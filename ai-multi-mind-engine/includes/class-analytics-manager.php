<?php
/**
 * Analytics Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Analytics_Manager {

	/**
	 * Track a mind being used
	 */
	public function log_mind_usage( $mind_id ) {
		$stats = get_option( 'amm_mind_stats', array() );
		$stats[$mind_id] = ( $stats[$mind_id] ?? 0 ) + 1;
		update_option( 'amm_mind_stats', $stats );
	}

	/**
	 * Get overall usage stats
	 */
	public function get_stats() {
		global $wpdb;
		$total_credits = $wpdb->get_var( "SELECT SUM(credits_used) FROM {$wpdb->prefix}amm_usage" );
		$total_subs = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}amm_subscriptions WHERE status = 'active'" );
		$mind_popularity = get_option( 'amm_mind_stats', array() );

		return array(
			'total_credits_used' => (int)$total_credits,
			'active_subscriptions' => (int)$total_subs,
			'mind_popularity' => $mind_popularity,
			'revenue_metrics'      => $this->get_revenue_metrics(),
		);
	}

	/**
	 * Get Revenue Metrics (MRR, LTV, Churn)
	 */
	private function get_revenue_metrics() {
		global $wpdb;
		$month = date( 'Y-m' );

		// Map plan IDs to prices
		$prices = array(
			'starter' => (int)get_option('amm_plan_starter_price', 19),
			'pro' => (int)get_option('amm_plan_pro_price', 49),
			'agency' => (int)get_option('amm_plan_agency_price', 199)
		);

		$subs = $wpdb->get_results( "SELECT plan_id, status FROM {$wpdb->prefix}amm_subscriptions WHERE status = 'active'" );
		$mrr = 0;
		foreach ( $subs as $s ) {
			$mrr += $prices[$s->plan_id] ?? 0;
		}

		$total_customers = $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}amm_subscriptions" );
		$arpu = $total_customers > 0 ? $mrr / $total_customers : 0;

		$churned = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}amm_subscriptions WHERE status = 'cancelled' AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" );
		$churn_rate = $total_customers > 0 ? ($churned / $total_customers) * 100 : 0;

		$ltv = $churn_rate > 0 ? $arpu / ($churn_rate / 100) : $arpu * 12; // Simple LTV estimation

		return array(
			'mrr' => $mrr,
			'arr' => $mrr * 12,
			'arpu' => round($arpu, 2),
			'churn_rate' => round($churn_rate, 2),
			'ltv' => round($ltv, 2),
			'customer_count' => $total_customers
		);
	}
}
