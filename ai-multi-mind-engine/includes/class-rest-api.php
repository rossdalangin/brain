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

		// All Minds Endpoint
		register_rest_route( $namespace, '/minds', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_all_minds' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Workspace Outputs Endpoint
		register_rest_route( $namespace, '/outputs', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_user_outputs' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Checkout Endpoint
		register_rest_route( $namespace, '/checkout', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_checkout' ),
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

		// Handle Folder assignment if provided
		$folder_id = $params['folder_id'] ?? 0;
		if ( $folder_id ) {
			wp_set_post_terms( $output_id, array( (int)$folder_id ), 'amm_folder' );
		}

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
	 * Handle checkout initiation
	 */
	public function handle_checkout( $request ) {
		$params = $request->get_json_params();
		$plan_id = $params['plan_id'] ?? '';
		$gateway = $params['gateway'] ?? 'stripe';
		$user_id = get_current_user_id();

		if ( $gateway === 'stripe' ) {
			$stripe = new AMM_Stripe_Handler();
			$url = $stripe->create_checkout_session( $user_id, $plan_id );
		} else {
			$paypal = new AMM_PayPal_Handler();
			$url = $paypal->create_subscription( $user_id, $plan_id );
		}

		if ( is_wp_error( $url ) ) return $url;

		return rest_ensure_response( array( 'success' => true, 'url' => $url ) );
	}

	/**
	 * Get user's saved outputs
	 */
	public function get_user_outputs() {
		$user_id = get_current_user_id();
		$outputs = get_posts( array(
			'post_type'      => 'ai_outputs',
			'post_author'    => $user_id,
			'posts_per_page' => 20,
		));

		$data = array();
		foreach ( $outputs as $output ) {
			$data[] = array(
				'id'      => $output->ID,
				'title'   => $output->post_title,
				'date'    => get_the_date( 'Y-m-d', $output->ID ),
				'content' => $output->post_content,
				'folders' => wp_get_post_terms( $output->ID, 'amm_folder', array( 'fields' => 'names' ) ),
			);
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get all available Minds (Core + CPT)
	 */
	public function get_all_minds() {
		$core_minds = array(
			array( 'id' => 'ceo', 'name' => 'Elite CEO' ),
			array( 'id' => 'strategist', 'name' => 'Blue Ocean Strategist' ),
			array( 'id' => 'funnel_builder', 'name' => 'Funnel Architect' ),
			array( 'id' => 'growth_hacker', 'name' => 'Growth Hacker' ),
			array( 'id' => 'copywriter', 'name' => 'Copywriting Master' ),
			array( 'id' => 'sales_closer', 'name' => 'Sales Closer' ),
			array( 'id' => 'profit_maximizer', 'name' => 'Profit Maximizer' ),
			array( 'id' => 'offer_creator', 'name' => 'Offer Creator (Hormozi)' ),
			array( 'id' => 'sop_architect', 'name' => 'SOP Architect' ),
			array( 'id' => 'viral_creator', 'name' => 'Viral Creator' ),
			array( 'id' => 'visionary', 'name' => 'Visionary Founder' ),
			array( 'id' => 'dominator', 'name' => 'Market Dominator' ),
			array( 'id' => 'automation_expert', 'name' => 'Automation Architect' ),
			array( 'id' => 'storyteller', 'name' => 'Master Storyteller' ),
			array( 'id' => 'board_advisor', 'name' => 'Board Advisor' ),
			array( 'id' => 'brand_authority', 'name' => 'Brand Authority Builder' ),
			array( 'id' => 'objection_killer', 'name' => 'Objection Killer' ),
			array( 'id' => 'negotiation_master', 'name' => 'Negotiation Master' ),
			array( 'id' => 'systems_builder', 'name' => 'Systems Builder' ),
			array( 'id' => 'pricing_strategist', 'name' => 'Pricing Strategist' ),
			array( 'id' => 'cost_cutter', 'name' => 'Cost Cutter' ),
			array( 'id' => 'product_strategist', 'name' => 'Product Strategist' ),
			array( 'id' => 'ux_expert', 'name' => 'UX/UI Expert' ),
			array( 'id' => 'saas_architect', 'name' => 'SaaS Architect' ),
			array( 'id' => 'email_specialist', 'name' => 'Email Conversion Specialist' ),
			array( 'id' => 'risk_analyst', 'name' => 'Risk Analyst' ),
			array( 'id' => 'policy_generator', 'name' => 'Policy Generator' ),
			array( 'id' => 'leadership_coach', 'name' => 'Leadership Coach' ),
			array( 'id' => 'decision_expert', 'name' => 'Decision Expert' ),
		);

		$cpt_minds = get_posts( array( 'post_type' => 'ai_minds', 'posts_per_page' => -1 ) );
		foreach ( $cpt_minds as $mind ) {
			$core_minds[] = array( 'id' => $mind->post_name, 'name' => $mind->post_title . ' (Custom)' );
		}

		return rest_ensure_response( $core_minds );
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
