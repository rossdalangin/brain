<?php
/**
 * Usage Tracker & Limits Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Usage_Tracker {

	/**
	 * Check if a user has enough credits for a generation
	 */
	public function can_user_generate( $user_id ) {
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';
		$limit = $this->get_plan_limit( $plan_id );

		$used = $this->get_current_month_usage( $user_id );

		return $used < $limit;
	}

	/**
	 * Increment usage for a user
	 */
	public function track_generation( $user_id ) {
		global $wpdb;
		$month = date( 'Y-m' );
		$this->check_usage_alerts( $user_id );
		$table = $wpdb->prefix . 'amm_usage';

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE user_id = %d AND month = %s",
			$user_id, $month
		));

		if ( $exists ) {
			$wpdb->query( $wpdb->prepare(
				"UPDATE $table SET credits_used = credits_used + 1 WHERE id = %d",
				$exists
			));
		} else {
			$wpdb->insert( $table, array(
				'user_id'      => $user_id,
				'month'        => $month,
				'credits_used' => 1,
			));
		}
	}

	/**
	 * Get plan limits
	 */
	public function get_plan_limit( $plan_id ) {
		$limits = array(
			'free'    => 5,
			'starter' => 50,
			'pro'     => 200,
			'agency'  => 1000,
		);

		return $limits[$plan_id] ?? 5;
	}

	/**
	 * Check and send usage alerts
	 */
	private function check_usage_alerts( $user_id ) {
		$alerts_enabled = get_user_meta( $user_id, 'amm_usage_alerts', true ) === 'yes';
		if ( ! $alerts_enabled ) return;

		$used = $this->get_current_month_usage( $user_id );
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';
		$limit = $this->get_plan_limit( $plan_id );

		if ( $used >= ( $limit * 0.9 ) && $used < $limit ) {
			// In production: wp_mail( get_userdata($user_id)->user_email, 'AI Multi-Mind: Usage Alert', 'You have used 90% of your credits.' );
		}
	}

	/**
	 * Get current month usage for a user
	 */
	public function get_current_month_usage( $user_id ) {
		global $wpdb;
		$month = date( 'Y-m' );
		$table = $wpdb->prefix . 'amm_usage';

		$usage = $wpdb->get_var( $wpdb->prepare(
			"SELECT credits_used FROM $table WHERE user_id = %d AND month = %s",
			$user_id, $month
		));

		return $usage ?: 0;
	}
}
