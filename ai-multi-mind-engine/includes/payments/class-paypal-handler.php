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
	 * Get Access Token
	 */
	private function get_access_token() {
		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) return false;

		$response = wp_remote_post( 'https://api-m.paypal.com/v1/oauth2/token', array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
			),
			'body' => array( 'grant_type' => 'client_credentials' )
		));

		if ( is_wp_error( $response ) ) return false;
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['access_token'] ?? false;
	}

	/**
	 * Create PayPal Subscription initiation
	 */
	public function create_subscription( $user_id, $plan_id ) {
		$token = $this->get_access_token();
		if ( ! $token ) return new WP_Error( 'paypal_error', 'Could not authenticate with PayPal.' );

		$paypal_plan_id = get_option( 'amm_paypal_plan_' . $plan_id );

		$body = array(
			'plan_id' => $paypal_plan_id,
			'custom_id' => $user_id,
			'application_context' => array(
				'return_url' => home_url( '/dashboard/?success=paypal' ),
				'cancel_url'  => home_url( '/billing/' ),
			)
		);

		$response = wp_remote_post( 'https://api-m.paypal.com/v1/billing/subscriptions', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body' => json_encode( $body )
		));

		if ( is_wp_error( $response ) ) return $response;
		$sub = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $sub['links'] ) ) {
			foreach ( $sub['links'] as $link ) {
				if ( $link['rel'] === 'approve' ) return $link['href'];
			}
		}

		return new WP_Error( 'paypal_error', 'Unexpected PayPal response.' );
	}

	/**
	 * Handle PayPal Webhook
	 */
	public function handle_webhook() {
		$payload = @file_get_contents( 'php://input' );
		$headers = array_change_key_case( function_exists('getallheaders') ? getallheaders() : array(), CASE_UPPER );

		// Fallback for some environments
		if ( empty($headers) && function_exists('apache_request_headers') ) {
			$headers = array_change_key_case( apache_request_headers(), CASE_UPPER );
		}

		// Production-ready: Verify Webhook Signature
		if ( ! $this->verify_webhook_signature( $payload, $headers ) ) {
			return new WP_Error( 'invalid_signature', 'PayPal webhook verification failed.' );
		}

		$data = json_decode( $payload, true );
		if ( ! $data ) return;

		switch ( $data['event_type'] ) {
			case 'BILLING.SUBSCRIPTION.ACTIVATED':
				$this->process_subscription( $data['resource'], 'active' );
				break;
			case 'BILLING.SUBSCRIPTION.CANCELLED':
			case 'BILLING.SUBSCRIPTION.EXPIRED':
				$this->process_subscription( $data['resource'], 'cancelled' );
				break;
		}
	}

	/**
	 * Verify PayPal Webhook Signature
	 */
	private function verify_webhook_signature( $payload, $headers ) {
		$token = $this->get_access_token();
		if ( ! $token ) return false;

		$webhook_id = get_option( 'amm_paypal_webhook_id' );

		$body = array(
			'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
			'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
			'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
			'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
			'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
			'webhook_id' => $webhook_id,
			'webhook_event' => json_decode( $payload )
		);

		$response = wp_remote_post( 'https://api-m.paypal.com/v1/notifications/verify-webhook-signature', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body' => json_encode( $body )
		));

		if ( is_wp_error( $response ) ) return false;
		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		return ( $result['verification_status'] ?? '' ) === 'SUCCESS';
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
