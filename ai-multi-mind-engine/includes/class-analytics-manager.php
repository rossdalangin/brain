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
		);
	}
}
