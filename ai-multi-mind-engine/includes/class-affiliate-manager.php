<?php
/**
 * Affiliate Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Affiliate_Manager {

	/**
	 * Register a user as an affiliate
	 */
	public function register_affiliate( $user_id ) {
		global $wpdb;
		$code = substr( md5( $user_id . time() ), 0, 8 );
		$wpdb->insert( $wpdb->prefix . 'amm_affiliates', array(
			'user_id' => $user_id,
			'affiliate_code' => $code,
		));
		return $code;
	}

	/**
	 * Get affiliate data
	 */
	public function get_affiliate_data( $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_affiliates WHERE user_id = %d",
			$user_id
		));
	}

	/**
	 * Record a referral
	 */
	public function record_referral( $affiliate_code, $referred_user_id, $commission ) {
		global $wpdb;
		$affiliate = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}amm_affiliates WHERE affiliate_code = %s",
			$affiliate_code
		));

		if ( $affiliate ) {
			$wpdb->insert( $wpdb->prefix . 'amm_referrals', array(
				'affiliate_id'     => $affiliate->id,
				'referred_user_id' => $referred_user_id,
				'status'           => 'pending',
				'commission_amount' => $commission,
			));
		}
	}
}
