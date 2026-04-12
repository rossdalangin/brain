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
	 * AES-256 Decryption with site-specific salt
	 */
	public function get_decrypted_option( $option_name ) {
		$value = get_option( $option_name );
		if ( ! $value ) return '';

		$encryption_key = defined('AUTH_SALT') ? AUTH_SALT : 'default_fallback_salt';
		$data = base64_decode($value);
		$iv_length = openssl_cipher_iv_length('aes-256-cbc');
		$iv = substr($data, 0, $iv_length);
		$encrypted = substr($data, $iv_length);

		return openssl_decrypt($encrypted, 'aes-256-cbc', $encryption_key, 0, $iv);
	}

	/**
	 * Call the selected AI provider
	 */
	public function generate_response( $provider, $system_prompt, $user_prompt, $history = array() ) {
		switch ( $provider ) {
			case 'free_jules':
				$tips = array(
					"Focus on high-leverage activities ($10,000/hr work) rather than busywork.",
					"Ensure your unit economics are sound: LTV must be at least 3x CAC.",
					"Speed is a feature. Build, test, and pivot faster than your competition.",
					"Niche down until it hurts. Then dominate that niche.",
					"Solve a bleeding-neck problem, not a nice-to-have one.",
					"Your network is your net worth. Build elite relationships.",
					"Automate or delegate anything that isn't your core genius.",
					"The best marketing is a product that actually works."
				);
				$tip = $tips[array_rand($tips)];
				return "Expert Insight from Jules: \"{$tip}\"\n\nStrategic Recommendation: Based on your request for '{$user_prompt}', I recommend auditing your current workflow to identify the single biggest constraint and applying a 'Blue Ocean' approach to differentiate your offer.";
			case 'gemini':
				return $this->call_gemini( $system_prompt, $user_prompt, $history );
			case 'openai':
				return $this->call_openai( $system_prompt, $user_prompt, $history );
			case 'claude':
				return $this->call_claude( $system_prompt, $user_prompt, $history );
			default:
				return new WP_Error( 'invalid_provider', 'Selected AI provider is not supported.' );
		}
	}

	/**
	 * Generate an image using DALL-E 3
	 */
	public function generate_image( $prompt ) {
		$api_key = $this->api_keys['openai'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'OpenAI API key missing for image generation.' );

		$url = "https://api.openai.com/v1/images/generations";
		$body = array(
			'model'  => 'dall-e-3',
			'prompt' => $prompt,
			'n'      => 1,
			'size'   => '1024x1024'
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 60
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $data['error'] ) ) {
			return new WP_Error( 'ai_error', $data['error']['message'] );
		}

		return $data['data'][0]['url'] ?? 'Image generation error';
	}

	/**
	 * Call Anthropic Claude API
	 */
	private function call_claude( $system_prompt, $user_prompt, $history = array() ) {
		$api_key = $this->api_keys['claude'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'Claude API key missing.' );

		$url = "https://api.anthropic.com/v1/messages";

		$messages = array();
		foreach ( $history as $h ) {
			$messages[] = array( 'role' => $h['role'], 'content' => $h['content'] );
		}
		$messages[] = array( 'role' => 'user', 'content' => $user_prompt );

		$body = array(
			'model' => 'claude-3-opus-20240229',
			'max_tokens' => 1024,
			'system' => $system_prompt,
			'messages' => $messages
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array(
				'Content-Type'      => 'application/json',
				'x-api-key'         => $api_key,
				'anthropic-version' => '2023-06-01'
			),
			'timeout' => 60,
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['content'][0]['text'] ?? 'AI response error';
	}

	/**
	 * Call Google Gemini API
	 */
	private function call_gemini( $system_prompt, $user_prompt, $history = array() ) {
		$api_key = $this->api_keys['gemini'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'Gemini API key missing.' );

		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;

		$contents = array();
		foreach ( $history as $h ) {
			$contents[] = array(
				'role'  => ( $h['role'] === 'user' ) ? 'user' : 'model',
				'parts' => array( array( 'text' => $h['content'] ) )
			);
		}
		$contents[] = array(
			'role'  => 'user',
			'parts' => array( array( 'text' => $system_prompt . "\n\nUser Request: " . $user_prompt ) )
		);

		$body = array( 'contents' => $contents );

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'timeout' => 60,
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'AI response error';
	}

	/**
	 * Call OpenAI API
	 */
	private function call_openai( $system_prompt, $user_prompt, $history = array() ) {
		$api_key = $this->api_keys['openai'];
		if ( ! $api_key ) return new WP_Error( 'missing_key', 'OpenAI API key missing.' );

		$url = "https://api.openai.com/v1/chat/completions";

		$messages = array( array( 'role' => 'system', 'content' => $system_prompt ) );
		foreach ( $history as $h ) {
			$messages[] = array( 'role' => $h['role'], 'content' => $h['content'] );
		}
		$messages[] = array( 'role' => 'user', 'content' => $user_prompt );

		$body = array(
			'model' => 'gpt-4-turbo',
			'messages' => $messages
		);

		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $body ),
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 60,
		));

		if ( is_wp_error( $response ) ) return $response;

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$result = $data['choices'][0]['message']['content'] ?? 'AI response error';

		AMM_Logger::log( 'openai', $body, $result );
		return $result;
	}
}
