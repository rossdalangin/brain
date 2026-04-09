<?php
/**
 * REST API Endpoints for the SaaS Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_REST_API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the REST API routes
	 */
	public function register_routes() {
		$namespace = 'amm/v1';

		// Generation Endpoint
		register_rest_route( $namespace, '/generate', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_generation' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// User Data Endpoint
		register_rest_route( $namespace, '/user', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_user_data' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Teams Endpoint
		register_rest_route( $namespace, '/teams', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_teams' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));
	}

	/**
	 * Check if the user is authorized to use the API
	 */
	public function check_auth() {
		return is_user_logged_in();
	}

	/**
	 * Handle AI Generation request
	 */
	public function handle_generation( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();

		$mind_id     = $params['mind_id'] ?? 'ceo';
		$output_type = $params['output_type'] ?? 'business_plan';
		$user_input  = $params['user_input'] ?? '';
		$provider    = get_option( 'amm_default_ai_provider', 'gemini' );

		// 1. Check Limits
		$tracker = new AMM_Usage_Tracker();
		if ( ! $tracker->can_user_generate( $user_id ) ) {
			return new WP_Error( 'limit_reached', 'Monthly credit limit reached. Please upgrade.', array( 'status' => 403 ) );
		}

		// 2. Prepare Prompts
		$prompt_engine = new AMM_Prompt_Engine();
		$prompts = $prompt_engine->prepare_prompts( $mind_id, $output_type, $user_input );

		if ( is_wp_error( $prompts ) ) return $prompts;

		// 3. Call AI Provider
		$ai_manager = new AMM_AI_Provider_Manager();
		$response = $ai_manager->generate_response( $provider, $prompts['system'], $prompts['user'] );

		if ( is_wp_error( $response ) ) return $response;

		// 4. Track Usage
		$tracker->track_generation( $user_id );

		// 5. Save Output (optional, but good for SaaS)
		$output_id = wp_insert_post( array(
			'post_title'   => "Output: " . ucfirst( str_replace( '_', ' ', $output_type ) ),
			'post_content' => $response,
			'post_status'  => 'publish',
			'post_type'    => 'ai_outputs',
			'post_author'  => $user_id,
		));

		return rest_ensure_response( array(
			'success'   => true,
			'output_id' => $output_id,
			'content'   => $response,
			'usage'     => $tracker->get_current_month_usage( $user_id ),
			'limit'     => $tracker->get_plan_limit( get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free' ),
			'plan'      => get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free',
		));
	}

	/**
	 * Get user teams
	 */
	public function get_teams() {
		$user_id = get_current_user_id();
		$team_manager = new AMM_Team_Manager();
		return rest_ensure_response( $team_manager->get_user_teams( $user_id ) );
	}

	/**
	 * Get user stats and subscription info
	 */
	public function get_user_data() {
		$user_id = get_current_user_id();
		$tracker = new AMM_Usage_Tracker();

		return rest_ensure_response( array(
			'user_id' => $user_id,
			'plan'    => get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free',
			'status'  => get_user_meta( $user_id, 'amm_subscription_status', true ) ?: 'active',
			'usage'   => array(
				'used'  => $tracker->get_current_month_usage( $user_id ),
				'limit' => $tracker->get_plan_limit( get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free' ),
			),
		));
	}
}
