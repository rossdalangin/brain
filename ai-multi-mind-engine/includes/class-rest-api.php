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

		// Public Share Endpoint
		register_rest_route( $namespace, '/share', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_share' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Checkout Endpoint
		register_rest_route( $namespace, '/checkout', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_checkout' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Affiliate Data Endpoint
		register_rest_route( $namespace, '/affiliate', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_affiliate_info' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Create Mind Endpoint (Pro/Agency only)
		register_rest_route( $namespace, '/create-mind', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_create_mind' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Update Branding Endpoint (Agency White-Label)
		register_rest_route( $namespace, '/update-branding', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_branding_update' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Update User Settings Endpoint
		register_rest_route( $namespace, '/update-settings', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_settings_update' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Create Invite Endpoint
		register_rest_route( $namespace, '/invite', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_invite' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Templates Endpoint
		register_rest_route( $namespace, '/templates', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_templates' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Create Folder Endpoint
		register_rest_route( $namespace, '/create-folder', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_create_folder' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Billing History Endpoint
		register_rest_route( $namespace, '/billing-history', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_billing_history' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Feedback Endpoint
		register_rest_route( $namespace, '/feedback', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_feedback' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Bulk Action Endpoint
		register_rest_route( $namespace, '/bulk-action', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_bulk_action' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Create Template Endpoint
		register_rest_route( $namespace, '/create-template', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_create_template' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Revoke Invite Endpoint
		register_rest_route( $namespace, '/revoke-invite', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_revoke_invite' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Pending Invites Endpoint
		register_rest_route( $namespace, '/pending-invites', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_pending_invites' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Refine Prompt Endpoint
		register_rest_route( $namespace, '/refine-prompt', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_refine_prompt' ),
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
		$language    = $params['language'] ?? 'English';
		$provider    = get_option( 'amm_default_ai_provider', 'gemini' );

		// 0. Plan Access Check for Mind
		if ( ! $this->user_can_access_mind( $user_id, $mind_id ) ) {
			return new WP_Error( 'forbidden', 'Upgrade your plan to access this AI Mind.', array( 'status' => 403 ) );
		}

		// 0a. Team Permission Check
		$team_manager = new AMM_Team_Manager();
		$teams = $team_manager->get_user_teams( $user_id );
		if ( ! empty( $teams ) ) {
			$perms = $team_manager->get_member_permissions( $teams[0]->id, $user_id );
			if ( $teams[0]->role === 'member' && ! in_array( 'can_generate', $perms ) ) {
				return new WP_Error( 'forbidden', 'You do not have permission to generate in this team.', array( 'status' => 403 ) );
			}
		}

		// 1. Check Limits
		$tracker = new AMM_Usage_Tracker();
		if ( ! $tracker->can_user_generate( $user_id ) ) {
			return new WP_Error( 'limit_reached', 'Monthly credit limit reached. Please upgrade.', array( 'status' => 403 ) );
		}

		// 2. Prepare Prompts
		$prompt_engine = new AMM_Prompt_Engine();
		$kb_context = get_user_meta( $user_id, 'amm_knowledge_base', true );
		if ( $kb_context ) {
			$user_input = "CONTEXT ABOUT MY BUSINESS:\n{$kb_context}\n\nUSER REQUEST:\n{$user_input}";
		}
		$prompts = $prompt_engine->prepare_prompts( $mind_id, $output_type, $user_input, $language );

		if ( is_wp_error( $prompts ) ) return $prompts;

		// 3. Call AI Provider
		$ai_manager = new AMM_AI_Provider_Manager();
		$response = $ai_manager->generate_response( $provider, $prompts['system'], $prompts['user'] );

		if ( is_wp_error( $response ) ) return $response;

		// 4. Track Usage
		$tracker->track_generation( $user_id );
		$analytics = new AMM_Analytics_Manager();
		$analytics->log_mind_usage( $mind_id );

		// 5. Save Output (optional, but good for SaaS)
		$webhook_manager = new AMM_Webhook_Manager();
		$webhook_manager->push_to_webhook( $user_id, array(
			'mind_id'     => $mind_id,
			'output_type' => $output_type,
			'content'     => $response
		));

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
	 * Handle output sharing
	 */
	public function handle_share( $request ) {
		$params = $request->get_json_params();
		$post_id = (int)$params['post_id'];
		$user_id = get_current_user_id();

		$post = get_post( $post_id );
		if ( ! $post || (int)$post->post_author !== $user_id ) {
			return new WP_Error( 'forbidden', 'You do not own this output.', array( 'status' => 403 ) );
		}

		$is_public = get_post_meta( $post_id, 'amm_is_public', true ) === 'yes';
		update_post_meta( $post_id, 'amm_is_public', $is_public ? 'no' : 'yes' );

		return rest_ensure_response( array(
			'success' => true,
			'is_public' => ! $is_public,
			'share_url' => home_url( '/shared-intel/?id=' . $post_id )
		));
	}

	/**
	 * Get user's saved outputs (including team shared ones)
	 */
	public function get_user_outputs() {
		$user_id = get_current_user_id();

		// Get Team Member IDs
		$team_manager = new AMM_Team_Manager();
		$teams = $team_manager->get_user_teams( $user_id );
		$author_ids = array( $user_id );

		foreach ( $teams as $team ) {
			$author_ids[] = $team->owner_id;
			// Ideally, fetch all team member IDs here
		}

		$outputs = get_posts( array(
			'post_type'      => 'ai_outputs',
			'author__in'     => array_unique( $author_ids ),
			'posts_per_page' => 50,
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
			array( 'id' => 'magic_bff', 'name' => 'Magic Business Mentor (BFF)' ),
		);

		$cpt_minds = get_posts( array( 'post_type' => 'ai_minds', 'posts_per_page' => -1 ) );
		foreach ( $cpt_minds as $mind ) {
			$core_minds[] = array(
				'id' => $mind->post_name,
				'name' => $mind->post_title . ' (Custom)',
				'premium' => get_post_meta( $mind->ID, 'amm_is_premium', true ) === 'yes',
				'category' => wp_get_post_terms( $mind->ID, 'amm_mind_category', array( 'fields' => 'names' ) )[0] ?? 'Custom'
			);
		}

		return rest_ensure_response( $core_minds );
	}

	/**
	 * Get billing history for the user
	 */
	public function get_billing_history() {
		global $wpdb;
		$user_id = get_current_user_id();
		$history = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_subscriptions WHERE user_id = %d ORDER BY created_at DESC",
			$user_id
		));

		return rest_ensure_response( $history );
	}

	/**
	 * Handle AI Output feedback
	 */
	public function handle_feedback( $request ) {
		$params = $request->get_json_params();
		$post_id = (int)$params['post_id'];
		$rating = sanitize_text_field( $params['rating'] ); // 'up' or 'down'

		update_post_meta( $post_id, 'amm_user_rating', $rating );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle custom mind creation
	 */
	public function handle_create_mind( $request ) {
		$user_id = get_current_user_id();
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';

		if ( ! in_array( $plan_id, array( 'pro', 'agency' ) ) ) {
			return new WP_Error( 'rest_forbidden', 'Mind creation is a PRO feature.', array( 'status' => 403 ) );
		}

		$params = $request->get_json_params();
		$name      = sanitize_text_field( $params['name'] );
		$role      = sanitize_text_field( $params['role'] );
		$framework = sanitize_text_field( $params['framework'] );
		$style     = sanitize_text_field( $params['style'] );
		$structure = sanitize_text_field( $params['structure'] );
		$prompt    = sanitize_textarea_field( $params['prompt'] );

		$post_id = wp_insert_post( array(
			'post_title'   => $name,
			'post_content' => $prompt,
			'post_status'  => 'publish',
			'post_type'    => 'ai_minds',
			'post_author'  => $user_id,
		));

		if ( is_wp_error( $post_id ) ) return $post_id;

		update_post_meta( $post_id, 'amm_mind_role', $role );
		update_post_meta( $post_id, 'amm_mind_framework', $framework );
		update_post_meta( $post_id, 'amm_mind_style', $style );
		update_post_meta( $post_id, 'amm_mind_structure', $structure );

		return rest_ensure_response( array( 'success' => true, 'mind_id' => $post_id ) );
	}

	/**
	 * Get affiliate info for the user
	 */
	public function get_affiliate_info() {
		$user_id = get_current_user_id();
		$aff_manager = new AMM_Affiliate_Manager();
		$data = $aff_manager->get_affiliate_data( $user_id );

		if ( ! $data ) {
			$code = $aff_manager->register_affiliate( $user_id );
			$data = $aff_manager->get_affiliate_data( $user_id );
		}

		return rest_ensure_response( array(
			'code' => $data->affiliate_code,
			'commissions' => $data->total_commissions,
			'link' => home_url( '/?ref=' . $data->affiliate_code ),
		));
	}

	/**
	 * Handle invite revocation
	 */
	public function handle_revoke_invite( $request ) {
		global $wpdb;
		$params = $request->get_json_params();
		$invite_id = (int)$params['id'];

		$wpdb->update(
			$wpdb->prefix . 'amm_invites',
			array( 'status' => 'revoked' ),
			array( 'id' => $invite_id )
		);

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle team invite
	 */
	public function handle_invite( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$team_id = (int)$params['team_id'];
		$email   = sanitize_email( $params['email'] );

		$team_manager = new AMM_Team_Manager();
		$token = $team_manager->create_invite( $team_id, $email );

		return rest_ensure_response( array(
			'success' => true,
			'invite_url' => home_url( '/join-team/?token=' . $token )
		));
	}

	/**
	 * Handle bulk actions on outputs
	 */
	public function handle_bulk_action( $request ) {
		$params = $request->get_json_params();
		$ids = (array)$params['ids'];
		$action = $params['action']; // 'delete'
		$user_id = get_current_user_id();

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( $post && (int)$post->post_author === $user_id ) {
				if ( $action === 'delete' ) wp_delete_post( $id, true );
			}
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle custom template creation
	 */
	public function handle_create_template( $request ) {
		$user_id = get_current_user_id();
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';

		if ( ! in_array( $plan_id, array( 'pro', 'agency' ) ) ) {
			return new WP_Error( 'forbidden', 'Template creation is a PRO feature.', array( 'status' => 403 ) );
		}

		$params = $request->get_json_params();
		$title   = sanitize_text_field( $params['title'] );
		$content = sanitize_textarea_field( $params['content'] );

		$post_id = wp_insert_post( array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'ai_templates',
			'post_author'  => $user_id,
		));

		return rest_ensure_response( array( 'success' => true, 'template_id' => $post_id ) );
	}

	/**
	 * Handle prompt refinement using AI
	 */
	public function handle_refine_prompt( $request ) {
		$params = $request->get_json_params();
		$user_input = $params['user_input'] ?? '';
		$provider = get_option( 'amm_default_ai_provider', 'gemini' );

		$system_prompt = "You are a Master Prompt Engineer. Take the user's short request and expand it into a high-quality, detailed prompt that will get the best possible result from an AI. Output ONLY the refined prompt.";

		$ai_manager = new AMM_AI_Provider_Manager();
		$response = $ai_manager->generate_response( $provider, $system_prompt, $user_input );

		if ( is_wp_error( $response ) ) return $response;

		return rest_ensure_response( array( 'success' => true, 'refined_prompt' => $response ) );
	}

	/**
	 * Handle folder creation
	 */
	public function handle_create_folder( $request ) {
		$params = $request->get_json_params();
		$name = sanitize_text_field( $params['name'] );

		$term = wp_insert_term( $name, 'amm_folder' );
		if ( is_wp_error( $term ) ) return $term;

		return rest_ensure_response( array( 'success' => true, 'term_id' => $term['term_id'] ) );
	}

	/**
	 * Handle user settings update
	 */
	public function handle_settings_update( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();

		if ( isset( $params['webhook_url'] ) ) {
			update_user_meta( $user_id, 'amm_external_webhook_url', esc_url_raw( $params['webhook_url'] ) );
		}

		if ( isset( $params['default_mind'] ) ) {
			update_user_meta( $user_id, 'amm_default_mind', sanitize_text_field( $params['default_mind'] ) );
		}

		if ( isset( $params['knowledge_base'] ) ) {
			update_user_meta( $user_id, 'amm_knowledge_base', sanitize_textarea_field( $params['knowledge_base'] ) );
		}

		if ( isset( $params['usage_alerts'] ) ) {
			update_user_meta( $user_id, 'amm_usage_alerts', $params['usage_alerts'] ? 'yes' : 'no' );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle branding update
	 */
	public function handle_branding_update( $request ) {
		$user_id = get_current_user_id();
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';

		if ( $plan_id !== 'agency' ) {
			return new WP_Error( 'rest_forbidden', 'White-labeling is an AGENCY feature.', array( 'status' => 403 ) );
		}

		$params = $request->get_json_params();
		$team_id = (int)$params['team_id'];
		$logo    = sanitize_text_field( $params['logo'] );
		$color   = sanitize_text_field( $params['color'] );

		$team_manager = new AMM_Team_Manager();
		$team_manager->update_branding( $team_id, $logo, $color );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Get all task templates
	 */
	public function get_templates() {
		$posts = get_posts( array( 'post_type' => 'ai_templates', 'posts_per_page' => -1 ) );
		$data = array();
		$user_id = get_current_user_id();
		$user_plan = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';
		$plans = array( 'free' => 0, 'starter' => 1, 'pro' => 2, 'agency' => 3 );

		foreach ( $posts as $p ) {
			$min_plan = get_post_meta( $p->ID, 'amm_min_plan', true ) ?: 'free';
			$is_locked = ( $plans[$user_plan] ?? 0 ) < ( $plans[$min_plan] ?? 0 );

			$data[] = array(
				'id' => $p->ID,
				'title' => $p->post_title,
				'content' => $is_locked ? '' : $p->post_content,
				'locked' => $is_locked,
				'min_plan' => $min_plan
			);
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Get pending invites for a team
	 */
	public function get_pending_invites( $request ) {
		$team_id = (int)$request->get_param('team_id');
		$team_manager = new AMM_Team_Manager();
		return rest_ensure_response( $team_manager->get_pending_invites( $team_id ) );
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
	 * Verify if user can access a specific mind
	 */
	private function user_can_access_mind( $user_id, $mind_id ) {
		// 1. Check if core mind
		$mind_post = get_page_by_path( $mind_id, OBJECT, 'ai_minds' );
		if ( ! $mind_post ) return true;

		// 2. Check if purchased one-time
		global $wpdb;
		$purchased = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}amm_purchases WHERE user_id = %d AND mind_id = %s",
			$user_id, $mind_id
		));
		if ( $purchased ) return true;

		// 3. Check plan level
		$min_plan = get_post_meta( $mind_post->ID, 'amm_min_plan', true ) ?: 'free';
		$user_plan = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';

		$plans = array( 'free' => 0, 'starter' => 1, 'pro' => 2, 'agency' => 3 );
		return ( $plans[$user_plan] ?? 0 ) >= ( $plans[$min_plan] ?? 0 );
	}

	/**
	 * Get user stats and subscription info
	 */
	public function get_user_data() {
		$user_id = get_current_user_id();
		$tracker = new AMM_Usage_Tracker();

		$user = get_userdata( $user_id );
		return rest_ensure_response( array(
			'user_id' => $user_id,
			'user_name' => $user->display_name,
			'plan'    => get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free',
			'status'  => get_user_meta( $user_id, 'amm_subscription_status', true ) ?: 'active',
			'settings' => array(
				'webhook_url' => get_user_meta( $user_id, 'amm_external_webhook_url', true ),
				'default_mind' => get_user_meta( $user_id, 'amm_default_mind', true ) ?: 'ceo',
				'knowledge_base' => get_user_meta( $user_id, 'amm_knowledge_base', true ),
				'usage_alerts' => get_user_meta( $user_id, 'amm_usage_alerts', true ) === 'yes',
			),
			'usage'   => array(
				'used'  => $tracker->get_current_month_usage( $user_id ),
				'limit' => $tracker->get_plan_limit( get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free' ),
			),
			'insights' => array(
				'total_generations' => count( get_posts( array( 'post_type' => 'ai_outputs', 'author' => $user_id, 'posts_per_page' => -1 ) ) ),
				'referral_count' => 0, // Placeholder for real referral count
			)
		));
	}
}
