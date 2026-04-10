<?php
/**
 * AI Provider Manager
 * Handles API calls to Gemini, OpenAI, Claude, etc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_AI_Provider_Manager {

	private $api_keys = array();

	public function __construct() {
		$this->api_keys = array(
			'gemini' => $this->get_decrypted_option( 'amm_gemini_api_key' ),
			'openai' => $this->get_decrypted_option( 'amm_openai_api_key' ),
			'claude' => $this->get_decrypted_option( 'amm_claude_api_key' ),
		);
	}

	/**
	 * Simple encryption for demonstration (In production, use a more robust key management system)
	 */
	private function get_decrypted_option( $option_name ) {
		$value = get_option( $option_name );
		if ( ! $value ) return '';

		// Simple XOR/base64 "obfuscation" as a placeholder for real encryption
		return base64_decode( $value );
	}

	/**
	 * Call the selected AI provider
	 */
	public function generate_response( $provider, $system_prompt, $user_prompt ) {
		switch ( $provider ) {
			case 'gemini':
				return $this->call_gemini( $system_prompt, $user_prompt );
			case 'openai':
				return $this->call_openai( $system_prompt, $user_prompt );
			case 'claude':
				return $this->call_claude( $system_prompt, $user_prompt );
			default:
				return new WP_Error( 'invalid_provider', 'Selected AI provider is not supported.' );
		}
	}

	/**
	 * Call Anthropic Claude API
	 */
	private function call_claude( $system_prompt, $user_prompt ) {
		$api_key = $this->api_keys['claude'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'Claude API key missing.' );

		$url = "https://api.anthropic.com/v1/messages";

		$body = array(
			'model' => 'claude-3-opus-20240229',
			'max_tokens' => 1024,
			'system' => $system_prompt,
			'messages' => array(
				array( 'role' => 'user', 'content' => $user_prompt ),
			)
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array(
				'Content-Type'      => 'application/json',
				'x-api-key'         => $api_key,
				'anthropic-version' => '2023-06-01'
			),
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['content'][0]['text'] ?? 'AI response error';
	}

	/**
	 * Call Google Gemini API
	 */
	private function call_gemini( $system_prompt, $user_prompt ) {
		$api_key = $this->api_keys['gemini'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'Gemini API key missing.' );

		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $system_prompt . "\n\nUser Request: " . $user_prompt )
					)
				)
			)
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array( 'Content-Type' => 'application/json' ),
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'AI response error';
	}

	/**
	 * Call OpenAI API
	 */
	private function call_openai( $system_prompt, $user_prompt ) {
		$api_key = $this->api_keys['openai'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'OpenAI API key missing.' );

		$url = "https://api.openai.com/v1/chat/completions";

		$body = array(
			'model' => 'gpt-4-turbo',
			'messages' => array(
				array( 'role' => 'system', 'content' => $system_prompt ),
				array( 'role' => 'user', 'content' => $user_prompt ),
			)
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['choices'][0]['message']['content'] ?? 'AI response error';
	}
}
