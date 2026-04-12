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

		// Viral Slack/Discord Logic (If enabled via global settings)
		$this->push_viral_notification( $user_id, $data );
	}

	/**
	 * Viral Notifications for external teams
	 */
	private function push_viral_notification( $user_id, $data ) {
		$global_webhook = get_option( 'amm_viral_automation_webhook' );
		if ( ! $global_webhook ) return;

		$user = get_userdata( $user_id );
		$payload = array(
			'text' => "🚀 *New Intelligence Ignited!*\n" .
					  "User: *{$user->display_name}*\n" .
					  "Mind: *{$data['mind_id']}*\n" .
					  "Output: *{$data['output_type']}*"
		);

		wp_remote_post( $global_webhook, array(
			'method'  => 'POST',
			'body'    => json_encode( $payload ),
			'headers' => array( 'Content-Type' => 'application/json' ),
		));
	}
}
