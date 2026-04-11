<?php
/**
 * Stripe Payment Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Stripe_Handler {

	private $secret_key;
	private $webhook_secret;

	public function __construct() {
		$this->secret_key = get_option( 'amm_stripe_secret_key' );
		$this->webhook_secret = get_option( 'amm_stripe_webhook_secret' );
	}

	/**
	 * Create a Checkout Session for a plan or Top-up
	 */
	public function create_checkout_session( $user_id, $plan_id ) {
		if ( $plan_id === 'topup_50' ) {
			return $this->create_topup_session( $user_id, 50, 'price_topup_50' );
		}

		if ( strpos($plan_id, 'mind_') === 0 ) {
			return $this->create_mind_purchase_session( $user_id, $plan_id );
		}

		$prices = array(
			'starter' => get_option('amm_stripe_price_starter'),
			'pro'     => get_option('amm_stripe_price_pro'),
			'agency'  => get_option('amm_stripe_price_agency'),
		);

		$url = "https://api.stripe.com/v1/checkout/sessions";

		$body = array(
			'success_url' => home_url( '/dashboard/?session_id={CHECKOUT_SESSION_ID}' ),
			'cancel_url'  => home_url( '/billing/' ),
			'mode'        => 'subscription',
			'client_reference_id' => $user_id,
			'line_items'  => array(
				array(
					'price' => $prices[$plan_id],
					'quantity' => 1,
				),
			),
			'metadata' => array(
				'plan_id' => $plan_id
			)
		);

		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->secret_key,
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body' => http_build_query( $body )
		));

		if ( is_wp_error( $response ) ) return $response;

		$session = json_decode( wp_remote_retrieve_body( $response ), true );
		return $session['url'] ?? '';
	}

	/**
	 * Create billing portal session
	 */
	public function create_portal_session( $user_id ) {
		$customer_id = get_user_meta( $user_id, 'amm_stripe_customer_id', true );
		if ( ! $customer_id ) return '';

		$url = "https://api.stripe.com/v1/billing_portal/sessions";
		$body = array(
			'customer' => $customer_id,
			'return_url' => home_url( '/billing/' ),
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Authorization' => 'Bearer ' . $this->secret_key, 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body' => http_build_query( $body )
		));

		$session = json_decode( wp_remote_retrieve_body( $response ), true );
		return $session['url'] ?? '';
	}

	/**
	 * Create one-time mind purchase session
	 */
	private function create_mind_purchase_session( $user_id, $mind_id ) {
		$url = "https://api.stripe.com/v1/checkout/sessions";
		$body = array(
			'success_url' => home_url( '/dashboard/?success=purchase' ),
			'cancel_url'  => home_url( '/library/' ),
			'mode'        => 'payment',
			'client_reference_id' => $user_id,
			'line_items'  => array( array( 'price' => get_option('amm_stripe_price_mind_unlock'), 'quantity' => 1 ) ),
			'metadata' => array( 'type' => 'mind_unlock', 'mind_id' => $mind_id )
		);
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Authorization' => 'Bearer ' . $this->secret_key, 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body' => http_build_query( $body )
		));
		$session = json_decode( wp_remote_retrieve_body( $response ), true );
		return $session['url'] ?? '';
	}

	/**
	 * Create one-time topup session
	 */
	private function create_topup_session( $user_id, $credits, $price_id ) {
		$url = "https://api.stripe.com/v1/checkout/sessions";
		$body = array(
			'success_url' => home_url( '/dashboard/?success=topup' ),
			'cancel_url'  => home_url( '/billing/' ),
			'mode'        => 'payment',
			'client_reference_id' => $user_id,
			'line_items'  => array( array( 'price' => $price_id, 'quantity' => 1 ) ),
			'metadata' => array( 'type' => 'topup', 'credits' => $credits )
		);
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Authorization' => 'Bearer ' . $this->secret_key, 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body' => http_build_query( $body )
		));
		$session = json_decode( wp_remote_retrieve_body( $response ), true );
		return $session['url'] ?? '';
	}

	/**
	 * Handle incoming webhooks from Stripe
	 */
	public function handle_webhook() {
		$payload = @file_get_contents( 'php://input' );
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
		$event = null;

		// PRODUCTION-READY: Verify Signature
		if ( ! $this->verify_signature( $payload, $sig_header ) ) {
			return new WP_Error( 'invalid_signature', 'Stripe signature verification failed.' );
		}

		try {
			$event = json_decode( $payload, true );
		} catch ( Exception $e ) {
			return new WP_Error( 'webhook_error', $e->getMessage() );
		}

		if ( ! $event ) return false;

		switch ( $event['type'] ) {
			case 'checkout.session.completed':
				$session = $event['data']['object'];
				if ( isset( $session['metadata']['type'] ) && $session['metadata']['type'] === 'topup' ) {
					$this->process_topup_success( $session );
				} elseif ( isset( $session['metadata']['type'] ) && $session['metadata']['type'] === 'mind_unlock' ) {
					$this->process_mind_purchase_success( $session );
				} else {
					$this->process_subscription_success( $session );
				}
				break;
			case 'customer.subscription.deleted':
				$this->process_subscription_cancellation( $event['data']['object'] );
				break;
		}

		return true;
	}

	/**
	 * Signature Verification logic (Manual implementation for production security)
	 */
	private function verify_signature( $payload, $sig_header ) {
		if ( empty( $sig_header ) || empty( $this->webhook_secret ) ) return false;

		$parts = explode( ',', $sig_header );
		$timestamp = -1;
		$signatures = array();

		foreach ( $parts as $part ) {
			$kv = explode( '=', $part, 2 );
			if ( count( $kv ) < 2 ) continue;
			if ( trim( $kv[0] ) === 't' ) $timestamp = (int)$kv[1];
			if ( trim( $kv[0] ) === 'v1' ) $signatures[] = $kv[1];
		}

		if ( $timestamp === -1 || empty( $signatures ) ) return false;

		// Check timestamp tolerance (5 minutes)
		if ( abs( time() - $timestamp ) > 300 ) return false;

		$signed_payload = $timestamp . '.' . $payload;
		$expected_sig = hash_hmac( 'sha256', $signed_payload, $this->webhook_secret );

		foreach ( $signatures as $sig ) {
			if ( hash_equals( $expected_sig, $sig ) ) return true;
		}

		return false;
	}

	/**
	 * Process successful mind purchase
	 */
	private function process_mind_purchase_success( $session ) {
		global $wpdb;
		$user_id = $session['client_reference_id'];
		$mind_id = $session['metadata']['mind_id'];

		$wpdb->insert( $wpdb->prefix . 'amm_purchases', array(
			'user_id' => $user_id,
			'mind_id' => $mind_id,
		));
	}

	/**
	 * Process successful topup
	 */
	private function process_topup_success( $session ) {
		global $wpdb;
		$user_id = $session['client_reference_id'];
		$credits = (int)$session['metadata']['credits'];
		$month = date( 'Y-m' );

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}amm_usage SET credits_used = credits_used - %d WHERE user_id = %d AND month = %s",
			$credits, $user_id, $month
		));
	}

	/**
	 * Process successful subscription
	 */
	private function process_subscription_success( $session ) {
		global $wpdb;
		$user_id = $session['client_reference_id'];
		$plan_id = $session['metadata']['plan_id'];
		$sub_id = $session['subscription'];

		$wpdb->insert( $wpdb->prefix . 'amm_subscriptions', array(
			'user_id' => $user_id,
			'plan_id' => $plan_id,
			'gateway' => 'stripe',
			'subscription_id' => $sub_id,
			'status'  => 'active',
			'current_period_end' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
		));

		update_user_meta( $user_id, 'amm_subscription_status', 'active' );
		update_user_meta( $user_id, 'amm_plan_id', $plan_id );
		update_user_meta( $user_id, 'amm_stripe_customer_id', $session['customer'] );
	}

	/**
	 * Process subscription cancellation
	 */
	private function process_subscription_cancellation( $subscription ) {
		global $wpdb;
		$sub_id = $subscription['id'];

		$wpdb->update(
			$wpdb->prefix . 'amm_subscriptions',
			array( 'status' => 'cancelled' ),
			array( 'subscription_id' => $sub_id )
		);

		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}amm_subscriptions WHERE subscription_id = %s", $sub_id ) );
		if ( $user_id ) {
			update_user_meta( $user_id, 'amm_subscription_status', 'cancelled' );
		}
	}
}
