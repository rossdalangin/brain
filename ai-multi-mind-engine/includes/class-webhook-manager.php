<?php
/**
 * External Webhook Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Webhook_Manager {

	/**
	 * Send generated intelligence to an external webhook
	 */
	public function push_to_webhook( $user_id, $data ) {
		$webhook_url = get_user_meta( $user_id, 'amm_external_webhook_url', true );
		if ( ! $webhook_url ) return;

		wp_remote_post( $webhook_url, array(
			'method'  => 'POST',
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => json_encode( array(
				'source'    => 'AI Multi-Mind SaaS Engine',
				'user_id'   => $user_id,
				'timestamp' => current_time( 'mysql' ),
				'payload'   => $data,
			)),
		));
	}
}
