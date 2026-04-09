<?php
/**
 * PayPal Payment Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_PayPal_Handler {

	private $client_id;
	private $client_secret;

	public function __construct() {
		$this->client_id = get_option( 'amm_paypal_client_id' );
		$this->client_secret = get_option( 'amm_paypal_client_secret' );
	}

	/**
	 * Create PayPal Subscription initiation
	 */
	public function create_subscription( $user_id, $plan_id ) {
		// In production, this would call the PayPal API /v1/billing/subscriptions
		// Returns the approval URL for the user
		return "https://www.paypal.com/checkoutnow?plan_id=" . $plan_id;
	}

	/**
	 * Handle PayPal IPN or Webhook
	 */
	public function handle_webhook() {
		$payload = @file_get_contents( 'php://input' );
		$data = json_decode( $payload, true );

		if ( ! $data ) return;

		switch ( $data['event_type'] ) {
			case 'BILLING.SUBSCRIPTION.CREATED':
				$this->process_subscription( $data['resource'], 'active' );
				break;
			case 'BILLING.SUBSCRIPTION.CANCELLED':
			case 'BILLING.SUBSCRIPTION.EXPIRED':
				$this->process_subscription( $data['resource'], 'cancelled' );
				break;
		}
	}

	/**
	 * Process subscription status change
	 */
	private function process_subscription( $resource, $status ) {
		global $wpdb;
		$sub_id = $resource['id'];
		$user_id = $resource['custom_id']; // Passed during checkout

		if ( $status === 'active' ) {
			$wpdb->insert( $wpdb->prefix . 'amm_subscriptions', array(
				'user_id' => $user_id,
				'plan_id' => $resource['plan_id'],
				'gateway' => 'paypal',
				'subscription_id' => $sub_id,
				'status'  => $status,
				'current_period_end' => date( 'Y-m-d H:i:s', strtotime( $resource['billing_info']['next_billing_time'] ) ),
			));
		} else {
			$wpdb->update(
				$wpdb->prefix . 'amm_subscriptions',
				array( 'status' => $status ),
				array( 'subscription_id' => $sub_id )
			);
		}

		update_user_meta( $user_id, 'amm_subscription_status', $status );
	}
}
