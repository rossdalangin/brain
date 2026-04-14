<?php
/**
 * PayPal Payment Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_PayPal_Handler {

	private function get_decrypted_option( $option_name ) {
		$ai_manager = new AMM_AI_Provider_Manager();
		return $ai_manager->get_decrypted_option( $option_name );
	}

	private $client_id;
	private $client_secret;

	public function __construct() {
		$this->client_id = $this->get_decrypted_option( 'amm_paypal_client_id' );
		$this->client_secret = $this->get_decrypted_option( 'amm_paypal_client_secret' );
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
	 * Create PayPal Order (One-time)
	 */
	public function create_order( $user_id, $item_id ) {
		$token = $this->get_access_token();
		if ( ! $token ) return new WP_Error( 'paypal_error', 'Could not authenticate.' );

		$price = 49; // Default for Mind
		$desc = "Premium Mind Unlock";
		if ( strpos($item_id, 'template_') === 0 ) {
			$id = str_replace('template_', '', $item_id);
			$price = (int)get_post_meta($id, 'amm_template_price', true) ?: 19;
			$desc = "Premium Template Unlock";
		}

		$body = array(
			'intent' => 'CAPTURE',
			'purchase_units' => array(
				array(
					'amount' => array( 'currency_code' => 'USD', 'value' => $price ),
					'description' => $desc,
					'custom_id' => $user_id . '|' . $item_id
				)
			),
			'application_context' => array(
				'return_url' => home_url( '/dashboard/?success=paypal' ),
				'cancel_url' => home_url( '/dashboard/' )
			)
		);

		$response = wp_remote_post( 'https://api-m.paypal.com/v2/checkout/orders', array(
			'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
			'body' => json_encode( $body )
		));

		$order = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $order['links'] ) ) {
			foreach ( $order['links'] as $link ) {
				if ( $link['rel'] === 'approve' ) return $link['href'];
			}
		}
		return new WP_Error( 'paypal_error', 'Checkout failed.' );
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
			case 'CHECKOUT.ORDER.APPROVED':
				$this->process_one_time( $data['resource'] );
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
	 * Process one-time payment
	 */
	private function process_one_time( $resource ) {
		global $wpdb;
		$custom = explode( '|', $resource['purchase_units'][0]['custom_id'] );
		$user_id = $custom[0];
		$item_id = $custom[1];

		$wpdb->insert( $wpdb->prefix . 'amm_purchases', array(
			'user_id' => $user_id,
			'mind_id' => $item_id,
		));
	}

	/**
	 * Process subscription status change
	 */
	private function process_subscription( $resource, $status ) {
		global $wpdb;
		$sub_id = $resource['id'];
		$user_id = $resource['custom_id']; // Passed during checkout

		if ( $status === 'active' ) {
			$paypal_plan_id = $resource['plan_id'];
			$plan_id = 'free';

			// Map PayPal Plan ID back to internal ID
			if ( $paypal_plan_id === get_option('amm_paypal_plan_starter') ) $plan_id = 'starter';
			elseif ( $paypal_plan_id === get_option('amm_paypal_plan_pro') ) $plan_id = 'pro';
			elseif ( $paypal_plan_id === get_option('amm_paypal_plan_agency') ) $plan_id = 'agency';

			$wpdb->insert( $wpdb->prefix . 'amm_subscriptions', array(
				'user_id' => $user_id,
				'plan_id' => $plan_id,
				'gateway' => 'paypal',
				'subscription_id' => $sub_id,
				'status'  => $status,
				'current_period_end' => date( 'Y-m-d H:i:s', strtotime( $resource['billing_info']['next_billing_time'] ) ),
			));

			update_user_meta( $user_id, 'amm_plan_id', $plan_id );
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
