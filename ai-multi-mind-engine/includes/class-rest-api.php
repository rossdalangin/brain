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

		// Create Team Endpoint
		register_rest_route( $namespace, '/create-team', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_create_team' ),
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

		// Update Member Role Endpoint
		register_rest_route( $namespace, '/update-member-role', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_member_role_update' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Success Coach Endpoint
		register_rest_route( $namespace, '/chat-support', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_support_chat' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Verify Invite Endpoint
		register_rest_route( $namespace, '/verify-invite', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_verify_invite' ),
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

		// Image Generation Endpoint
		register_rest_route( $namespace, '/generate-image', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_image_generation' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Refine Prompt Endpoint
		register_rest_route( $namespace, '/refine-prompt', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_refine_prompt' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Duplicate Endpoint
		register_rest_route( $namespace, '/duplicate', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_duplicate' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Import Shared Intelligence Endpoint
		register_rest_route( $namespace, '/import-shared', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_import_shared' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Billing Portal Endpoint
		register_rest_route( $namespace, '/billing-portal', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_billing_portal' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Pricing Plans Endpoint
		register_rest_route( $namespace, '/plans', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_pricing_plans' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Usage History Endpoint
		register_rest_route( $namespace, '/usage-history', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_usage_history' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Invoices Endpoint
		register_rest_route( $namespace, '/invoices', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_user_invoices' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Get Folders Endpoint
		register_rest_route( $namespace, '/folders', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_folders' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Remove Member Endpoint
		register_rest_route( $namespace, '/remove-member', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_remove_member' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Mind Council Collaboration Endpoint
		register_rest_route( $namespace, '/collaborate', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_collaboration' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Team Activity Endpoint
		register_rest_route( $namespace, '/team-activity', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_team_activity' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Stripe Webhook (No Auth - Signature verified internally)
		register_rest_route( $namespace, '/stripe-webhook', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_stripe_webhook' ),
			'permission_callback' => '__return_true',
		));

		// PayPal Webhook (No Auth - Verified internally)
		register_rest_route( $namespace, '/paypal-webhook', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_paypal_webhook' ),
			'permission_callback' => '__return_true',
		));

		// Public Output Endpoint (No Auth Required)
		register_rest_route( $namespace, '/public-output', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_public_output' ),
			'permission_callback' => '__return_true',
		));

		// Admin Connectivity Test
		register_rest_route( $namespace, '/admin/test-api', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_admin_test' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		));

		// Admin Reset Credits
		register_rest_route( $namespace, '/admin/reset-credits', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_admin_reset_credits' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		));

		// Admin Change Plan
		register_rest_route( $namespace, '/admin/change-plan', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_admin_change_plan' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		));

		// Admin Adjust Credits
		register_rest_route( $namespace, '/admin/adjust-credits', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_admin_adjust_credits' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		));

		// Save History Endpoint
		register_rest_route( $namespace, '/save-history', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_save_history' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Get History Endpoint
		register_rest_route( $namespace, '/get-history', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'handle_get_history' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Update Output Endpoint
		register_rest_route( $namespace, '/update-output', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_update_output' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Export Workspace Endpoint
		register_rest_route( $namespace, '/export-workspace', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'handle_export_workspace' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Persona Endpoints
		register_rest_route( $namespace, '/persona', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_persona' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		register_rest_route( $namespace, '/save-persona', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_save_persona' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Save Preset Endpoint
		register_rest_route( $namespace, '/save-preset', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_save_preset' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));

		// Get Presets Endpoint
		register_rest_route( $namespace, '/presets', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'handle_get_presets' ),
			'permission_callback' => array( $this, 'check_auth' ),
		));
	}

	/**
	 * Handle Save Council Preset
	 */
	public function handle_save_preset( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$name = sanitize_text_field( $params['name'] );
		$mind_ids = (array)$params['mind_ids'];
		$mode = sanitize_text_field( $params['mode'] );

		$presets = get_user_meta( $user_id, 'amm_council_presets', true ) ?: array();
		$presets[] = array( 'name' => $name, 'mind_ids' => $mind_ids, 'mode' => $mode );
		update_user_meta( $user_id, 'amm_council_presets', $presets );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle Get Council Presets
	 */
	public function handle_get_presets() {
		$user_id = get_current_user_id();
		$presets = get_user_meta( $user_id, 'amm_council_presets', true ) ?: array();
		return rest_ensure_response( $presets );
	}

	/**
	 * Handle Save Conversation History
	 */
	public function handle_save_history( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$history = (array)$params['history'];

		// Limit history to last 10 messages for performance
		$history = array_slice( $history, -10 );
		update_user_meta( $user_id, 'amm_chat_history', $history );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle Get Conversation History
	 */
	public function handle_get_history() {
		$user_id = get_current_user_id();
		$history = get_user_meta( $user_id, 'amm_chat_history', true ) ?: array();
		return rest_ensure_response( $history );
	}

	/**
	 * Get User Target Persona
	 */
	public function get_persona() {
		$user_id = get_current_user_id();
		return rest_ensure_response( get_user_meta( $user_id, 'amm_target_persona', true ) ?: array() );
	}

	/**
	 * Save User Target Persona
	 */
	public function handle_save_persona( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		update_user_meta( $user_id, 'amm_target_persona', $params );
		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle Admin Connectivity Test
	 */
	public function handle_admin_reset_credits( $request ) {
		global $wpdb;
		$params = $request->get_json_params();
		$user_id = (int)$params['user_id'];
		$month = date( 'Y-m' );

		$wpdb->update(
			$wpdb->prefix . 'amm_usage',
			array( 'credits_used' => 0 ),
			array( 'user_id' => $user_id, 'month' => $month )
		);

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle Admin Manual Credit Adjustment
	 */
	public function handle_admin_adjust_credits( $request ) {
		global $wpdb;
		$params = $request->get_json_params();
		$user_id = (int)$params['user_id'];
		$amount = (int)$params['amount'];
		$month = date( 'Y-m' );

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}amm_usage SET credits_used = credits_used - %d WHERE user_id = %d AND month = %s",
			$amount, $user_id, $month
		));

		AMM()->log_audit( get_current_user_id(), 'admin_credit_adjust', "Adjusted $amount credits for user $user_id" );

		return rest_ensure_response( array( 'success' => true ) );
	}

	public function handle_admin_change_plan( $request ) {
		$params = $request->get_json_params();
		$user_id = (int)$params['user_id'];
		$plan_id = sanitize_text_field( $params['plan_id'] );

		update_user_meta( $user_id, 'amm_plan_id', $plan_id );

		return rest_ensure_response( array( 'success' => true ) );
	}

	public function handle_admin_test( $request ) {
		$params = $request->get_json_params();
		$service = $params['service'] ?? '';
		$provider_manager = new AMM_AI_Provider_Manager();

		switch ( $service ) {
			case 'gemini':
			case 'openai':
			case 'claude':
				$res = $provider_manager->generate_response( $service, "Verify connectivity.", "Ping." );
				if ( is_wp_error( $res ) ) return $res;
				return rest_ensure_response( array( 'success' => true, 'message' => 'API Connection Successful!' ) );
			case 'stripe':
				$key = get_option('amm_stripe_secret_key');
				if ( ! $key ) return new WP_Error( 'missing_key', 'Stripe secret key is not set.' );
				$response = wp_remote_get( 'https://api.stripe.com/v1/balance', array(
					'headers' => array( 'Authorization' => 'Bearer ' . $key )
				));
				if ( is_wp_error( $response ) ) return $response;
				return rest_ensure_response( array( 'success' => true, 'message' => 'Stripe Account Connected!' ) );
			default:
				return new WP_Error( 'invalid_service', 'Service not recognized.' );
		}
	}

	/**
	 * Get a publicly shared AI output
	 */
	public function get_public_output( $request ) {
		$post_id = (int)$request->get_param('id');
		$post = get_post( $post_id );

		if ( ! $post || $post->post_type !== 'ai_outputs' ) {
			return new WP_Error( 'not_found', 'Shared intelligence not found.', array( 'status' => 404 ) );
		}

		if ( get_post_meta( $post_id, 'amm_is_public', true ) !== 'yes' ) {
			return new WP_Error( 'forbidden', 'This intelligence is private.', array( 'status' => 403 ) );
		}

		return rest_ensure_response( array(
			'title'   => $post->post_title,
			'content' => $post->post_content,
			'date'    => get_the_date( 'Y-m-d', $post_id ),
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

		$default_mind = get_user_meta( $user_id, 'amm_default_mind', true ) ?: 'ceo';
		$mind_id     = $params['mind_id'] ?: $default_mind;
		$output_type = $params['output_type'] ?? 'business_plan';
		$user_input  = $params['user_input'] ?? '';
		$language    = $params['language'] ?? 'English';
		$history     = $params['history'] ?? array();
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

		// BFF Context Awareness (Persistent History)
		if ( $mind_id === 'magic_bff' && empty($history) ) {
			$history = get_user_meta( $user_id, 'amm_chat_history', true ) ?: array();
		}

		$kb_context = get_user_meta( $user_id, 'amm_knowledge_base', true );
		$persona = get_user_meta( $user_id, 'amm_target_persona', true );

		if ( $persona ) {
			$user_input = "TARGET AUDIENCE CONTEXT:\n- Name: {$persona['name']}\n- Pain: {$persona['pain']}\n- Desires: {$persona['desire']}\n- Triggers: {$persona['triggers']}\n\n{$user_input}";
		}

		if ( $kb_context ) {
			$user_input = "CONTEXT ABOUT MY BUSINESS:\n{$kb_context}\n\nUSER REQUEST:\n{$user_input}";
		}
		$prompts = $prompt_engine->prepare_prompts( $mind_id, $output_type, $user_input, $language, $user_id );

		if ( is_wp_error( $prompts ) ) return $prompts;

		// 3. Call AI Provider
		$ai_manager = new AMM_AI_Provider_Manager();
		$response = $ai_manager->generate_response( $provider, $prompts['system'], $prompts['user'], $history );

		if ( is_wp_error( $response ) ) {
			AMM()->log_audit( $user_id, 'generation_failed', $response->get_error_message() );
			return $response;
		}

		AMM()->log_audit( $user_id, 'generation_success', "Generated $output_type using $mind_id" );

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
			if ( strpos($plan_id, 'mind_') === 0 || strpos($plan_id, 'template_') === 0 ) {
				$url = $paypal->create_order( $user_id, $plan_id );
			} else {
				$url = $paypal->create_subscription( $user_id, $plan_id );
			}
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
		global $wpdb;
		$user_id = get_current_user_id();

		// Get Team Member IDs
		$team_manager = new AMM_Team_Manager();
		$teams = $team_manager->get_user_teams( $user_id );
		$author_ids = array( $user_id );

		foreach ( $teams as $team ) {
			$perms = $team_manager->get_member_permissions( $team->id, $user_id );
			if ( $team->role !== 'admin' && ! in_array( 'can_view_workspace', $perms ) ) continue;

			$author_ids[] = $team->owner_id;

			// Fetch all team member IDs
			$members = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}amm_team_members WHERE team_id = %d", $team->id ) );
			if ( $members ) $author_ids = array_merge( $author_ids, $members );
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
				'is_public' => get_post_meta( $output->ID, 'amm_is_public', true ) === 'yes',
				'folders' => wp_get_post_terms( $output->ID, 'amm_folder', array( 'fields' => 'names' ) ),
			);
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get all available Minds (Core + CPT)
	 */
	public function get_all_minds() {
		$user_id = get_current_user_id();
		$core_minds = array(
			array( 'id' => 'ceo', 'name' => 'CEO Mind', 'featured' => true, 'category' => 'Executive' ),
			array( 'id' => 'visionary', 'name' => 'Visionary Founder', 'category' => 'Executive' ),
			array( 'id' => 'board_advisor', 'name' => 'Board Advisor', 'category' => 'Executive' ),

			array( 'id' => 'strategist', 'name' => 'Elite Business Strategist', 'featured' => true, 'category' => 'Strategy' ),
			array( 'id' => 'growth_hacker', 'name' => 'Growth Hacker', 'category' => 'Strategy' ),
			array( 'id' => 'dominator', 'name' => 'Market Dominator', 'category' => 'Strategy' ),
			array( 'id' => 'blue_ocean', 'name' => 'Blue Ocean Expert', 'category' => 'Strategy' ),

			array( 'id' => 'funnel_builder', 'name' => 'Funnel Builder (Brunson-style)', 'category' => 'Marketing' ),
			array( 'id' => 'offer_creator', 'name' => 'Offer Creator (Hormozi-style)', 'featured' => true, 'category' => 'Marketing' ),
			array( 'id' => 'viral_creator', 'name' => 'Viral Content Creator', 'category' => 'Marketing' ),
			array( 'id' => 'brand_authority', 'name' => 'Brand Authority Builder', 'category' => 'Marketing' ),

			array( 'id' => 'sales_closer', 'name' => 'High-Ticket Closer', 'category' => 'Sales' ),
			array( 'id' => 'objection_killer', 'name' => 'Objection Killer', 'category' => 'Sales' ),
			array( 'id' => 'negotiation_master', 'name' => 'Negotiation Master', 'category' => 'Sales' ),

			array( 'id' => 'sop_architect', 'name' => 'SOP Architect', 'category' => 'Operations' ),
			array( 'id' => 'systems_builder', 'name' => 'Systems Builder', 'category' => 'Operations' ),
			array( 'id' => 'automation_expert', 'name' => 'Automation Expert', 'category' => 'Operations' ),

			array( 'id' => 'profit_maximizer', 'name' => 'Profit Maximizer', 'category' => 'Finance' ),
			array( 'id' => 'pricing_strategist', 'name' => 'Pricing Strategist', 'category' => 'Finance' ),
			array( 'id' => 'cost_cutter', 'name' => 'Cost Cutter', 'category' => 'Finance' ),

			array( 'id' => 'product_strategist', 'name' => 'Product Strategist', 'category' => 'Product' ),
			array( 'id' => 'ux_expert', 'name' => 'UX/UI Expert', 'category' => 'Product' ),
			array( 'id' => 'saas_architect', 'name' => 'SaaS Architect', 'category' => 'Product' ),

			array( 'id' => 'copywriter', 'name' => 'Copywriting Master', 'category' => 'Content' ),
			array( 'id' => 'storyteller', 'name' => 'Storytelling Expert', 'category' => 'Content' ),
			array( 'id' => 'email_specialist', 'name' => 'Email Conversion Specialist', 'category' => 'Content' ),

			array( 'id' => 'risk_analyst', 'name' => 'Risk Analyst', 'category' => 'Legal' ),
			array( 'id' => 'policy_generator', 'name' => 'Policy Generator', 'category' => 'Legal' ),

			array( 'id' => 'leadership_coach', 'name' => 'Leadership Coach', 'category' => 'Personal' ),
			array( 'id' => 'decision_expert', 'name' => 'Decision Expert', 'category' => 'Personal' ),

			array( 'id' => 'roadmap_builder', 'name' => 'Product Roadmap Builder', 'category' => 'Growth' ),
			array( 'id' => 'pitch_architect', 'name' => 'Investor Pitch Architect', 'category' => 'Growth' ),
			array( 'id' => 'vc_auditor', 'name' => 'VC Auditor', 'category' => 'Growth' ),
			array( 'id' => 'psych_copywriter', 'name' => 'Psychological Copywriter', 'category' => 'Growth' ),
			array( 'id' => 'seo_strategist', 'name' => 'SEO Strategist', 'category' => 'Growth' ),
			array( 'id' => 'viral_storyteller', 'name' => 'Viral Storyteller', 'category' => 'Growth' ),
			array( 'id' => 'support_architect', 'name' => 'Customer Support Architect', 'category' => 'Growth' ),
			array( 'id' => 'ecom_strategist', 'name' => 'E-commerce Strategist', 'category' => 'Growth' ),
			array( 'id' => 'real_estate_authority', 'name' => 'Real Estate Authority', 'category' => 'Growth' ),
			array( 'id' => 'podcast_strategist', 'name' => 'Podcast Guest Strategist', 'category' => 'Growth' ),
			array( 'id' => 'youtube_lead', 'name' => 'YouTube Growth Lead', 'category' => 'Growth' ),

			array( 'id' => 'magic_bff', 'name' => 'Magic Business Mentor (BFF)', 'featured' => true, 'category' => 'Special' ),
		);

		$cpt_minds = get_posts( array( 'post_type' => 'ai_minds', 'posts_per_page' => -1 ) );
		foreach ( $cpt_minds as $mind ) {
			$is_premium = get_post_meta( $mind->ID, 'amm_is_premium', true ) === 'yes';
			$has_access = $this->user_can_access_mind( $user_id, $mind->post_name );

			$core_minds[] = array(
				'id' => $mind->post_name,
				'name' => $mind->post_title . ' (Custom)',
				'premium' => $is_premium,
				'purchased' => $has_access,
				'featured' => get_post_meta( $mind->ID, 'amm_is_featured', true ) === 'yes',
				'category' => wp_get_post_terms( $mind->ID, 'amm_mind_category', array( 'fields' => 'names' ) )[0] ?? 'Custom'
			);
		}

		return rest_ensure_response( $core_minds );
	}

	/**
	 * Get Stripe billing portal URL
	 */
	public function get_billing_portal() {
		$user_id = get_current_user_id();
		$stripe = new AMM_Stripe_Handler();
		$url = $stripe->create_portal_session( $user_id );

		return rest_ensure_response( array( 'url' => $url ) );
	}

	/**
	 * Get pricing plans for the dashboard
	 */
	public function get_user_invoices() {
		global $wpdb;
		$user_id = get_current_user_id();
		$invoices = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_subscriptions WHERE user_id = %d ORDER BY created_at DESC",
			$user_id
		));
		return rest_ensure_response( $invoices );
	}

	/**
	 * Get detailed usage history for the user
	 */
	public function get_usage_history() {
		global $wpdb;
		$user_id = get_current_user_id();
		$logs = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_audit_trail WHERE user_id = %d AND event_type IN ('generation_success', 'generation_failed') ORDER BY created_at DESC LIMIT 50",
			$user_id
		));
		return rest_ensure_response( $logs );
	}

	public function get_pricing_plans() {
		$tracker = new AMM_Usage_Tracker();
		return rest_ensure_response( array(
			array( 'id' => 'starter', 'name' => 'Starter', 'price' => '$' . get_option('amm_plan_starter_price', 19), 'credits' => $tracker->get_plan_limit('starter') ),
			array( 'id' => 'pro', 'name' => 'Pro', 'price' => '$' . get_option('amm_plan_pro_price', 49), 'credits' => $tracker->get_plan_limit('pro'), 'featured' => true ),
			array( 'id' => 'agency', 'name' => 'Agency', 'price' => '$' . get_option('amm_plan_agency_price', 199), 'credits' => $tracker->get_plan_limit('agency') ),
		));
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

		global $wpdb;
		$referrals = $wpdb->get_results( $wpdb->prepare(
			"SELECT r.*, u.user_email FROM {$wpdb->prefix}amm_referrals r
			 JOIN wp_users u ON r.referred_user_id = u.ID
			 WHERE r.affiliate_id = %d",
			$data->id
		));

		return rest_ensure_response( array(
			'code' => $data->affiliate_code,
			'commissions' => $data->total_commissions,
			'link' => home_url( '/?ref=' . $data->affiliate_code ),
			'referrals' => $referrals
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
	 * Handle Invite Verification
	 */
	public function handle_verify_invite( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$token = sanitize_text_field( $params['token'] );

		$team_manager = new AMM_Team_Manager();
		$team_id = $team_manager->verify_and_consume_token( $token );

		if ( $team_id ) {
			$team_manager->add_member( $team_id, $user_id );
			return rest_ensure_response( array( 'success' => true ) );
		}

		return new WP_Error( 'invalid_token', 'Invalid or expired invitation token.', array( 'status' => 400 ) );
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
	 * Handle output duplication
	 */
	public function handle_export_workspace() {
		$outputs = $this->get_user_outputs();
		return $outputs; // For now JSON is fine via REST, download handled on frontend
	}

	public function handle_update_output( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$post_id = (int)$params['post_id'];
		$content = $params['content'];

		$post = get_post( $post_id );
		if ( ! $post || (int)$post->post_author !== $user_id ) {
			return new WP_Error( 'forbidden', 'Unauthorized.', array( 'status' => 403 ) );
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $content,
		));

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle importing a shared output into personal workspace
	 */
	public function handle_import_shared( $request ) {
		$params = $request->get_json_params();
		$post_id = (int)$params['post_id'];
		$user_id = get_current_user_id();

		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'ai_outputs' ) {
			return new WP_Error( 'not_found', 'Intelligence not found.', array( 'status' => 404 ) );
		}

		if ( get_post_meta( $post_id, 'amm_is_public', true ) !== 'yes' ) {
			return new WP_Error( 'forbidden', 'This intelligence is private.', array( 'status' => 403 ) );
		}

		$new_id = wp_insert_post( array(
			'post_title'   => $post->post_title . ' (Imported)',
			'post_content' => $post->post_content,
			'post_status'  => 'publish',
			'post_type'    => 'ai_outputs',
			'post_author'  => $user_id,
		));

		return rest_ensure_response( array( 'success' => true, 'new_id' => $new_id ) );
	}

	public function handle_duplicate( $request ) {
		$params = $request->get_json_params();
		$post_id = (int)$params['post_id'];
		$user_id = get_current_user_id();

		$post = get_post( $post_id );
		if ( ! $post || (int)$post->post_author !== $user_id ) {
			return new WP_Error( 'forbidden', 'Unauthorized.', array( 'status' => 403 ) );
		}

		$new_id = wp_insert_post( array(
			'post_title'   => $post->post_title . ' (Copy)',
			'post_content' => $post->post_content,
			'post_status'  => 'publish',
			'post_type'    => 'ai_outputs',
			'post_author'  => $user_id,
		));

		return rest_ensure_response( array( 'success' => true, 'new_id' => $new_id ) );
	}

	/**
	 * Handle bulk actions on outputs
	 */
	public function handle_bulk_action( $request ) {
		$params = $request->get_json_params();
		$ids = (array)$params['ids'];
		$action = $params['action']; // 'delete', 'move'
		$user_id = get_current_user_id();

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( $post && (int)$post->post_author === $user_id ) {
				if ( $action === 'delete' ) {
					wp_delete_post( $id, true );
				} elseif ( $action === 'move' ) {
					$folder_id = (int)$params['folder_id'];
					wp_set_post_terms( $id, array( $folder_id ), 'amm_folder' );
				}
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
	 * Handle Image Generation
	 */
	public function handle_image_generation( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$prompt = sanitize_text_field( $params['prompt'] );

		// Credit Check (Images cost 5 credits)
		$tracker = new AMM_Usage_Tracker();
		if ( $tracker->get_current_month_usage( $user_id ) + 5 > $tracker->get_plan_limit( get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free' ) ) {
			return new WP_Error( 'limit_reached', 'Insufficient credits for image generation.', array( 'status' => 403 ) );
		}

		$ai_manager = new AMM_AI_Provider_Manager();
		$image_url = $ai_manager->generate_image( $prompt );

		if ( is_wp_error( $image_url ) ) return $image_url;

		// Track usage (x5 for images)
		for($i=0; $i<5; $i++) $tracker->track_generation( $user_id );

		return rest_ensure_response( array( 'success' => true, 'url' => $image_url ) );
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
	 * Get all workspace folders
	 */
	public function get_folders() {
		$terms = get_terms( array( 'taxonomy' => 'amm_folder', 'hide_empty' => false ) );
		$data = array();
		foreach ( $terms as $t ) {
			$data[] = array( 'id' => $t->term_id, 'name' => $t->name );
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Handle Mind Council Collaboration
	 */
	public function handle_collaboration( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$mind_ids = (array)$params['mind_ids'];
		$user_input = $params['user_input'] ?? '';
		$mode = $params['mode'] ?? 'sequence';
		$provider = get_option( 'amm_default_ai_provider', 'gemini' );

		$current_output = $user_input;
		$results = array();
		$prompt_engine = new AMM_Prompt_Engine();
		$ai_manager = new AMM_AI_Provider_Manager();

		if ( $mode === 'critique' && count($mind_ids) >= 2 ) {
			// 1. Mind 1 creates
			$p1 = $prompt_engine->prepare_prompts( $mind_ids[0], 'report', $current_output );
			$r1 = $ai_manager->generate_response( $provider, $p1['system'], $p1['user'] );

			// 2. Mind 2 audits
			$p2 = $prompt_engine->prepare_prompts( $mind_ids[1], 'report', "AUDIT THIS STRATEGY FOR FLAWS AND IMPROVEMENTS:\n\n" . $r1 );
			$r2 = $ai_manager->generate_response( $provider, $p2['system'], $p2['user'] );

			// 3. Mind 1 finalizes
			$p3 = $prompt_engine->prepare_prompts( $mind_ids[0], 'report', "HERE IS AN AUDIT OF YOUR PREVIOUS WORK. IMPROVE AND FINALIZE THE STRATEGY BASED ON THIS FEEDBACK:\n\nAUDIT:\n" . $r2 . "\n\nORIGINAL:\n" . $r1 );
			$r3 = $ai_manager->generate_response( $provider, $p3['system'], $p3['user'] );

			$current_output = $r3;
			$results = array($r1, $r2, $r3);
		} elseif ( $mode === 'brainstorm' ) {
			foreach ( $mind_ids as $mind_id ) {
				$prompts = $prompt_engine->prepare_prompts( $mind_id, 'report', $user_input );
				$response = $ai_manager->generate_response( $provider, $prompts['system'], $prompts['user'] );
				if ( ! is_wp_error( $response ) ) {
					$results[] = array( 'mind_id' => $mind_id, 'content' => $response );
				}
			}
			$current_output = "The Council has provided multiple distinct perspectives. See reflections below.";
		} else {
			foreach ( $mind_ids as $mind_id ) {
				$prompts = $prompt_engine->prepare_prompts( $mind_id, 'report', $current_output );
				$response = $ai_manager->generate_response( $provider, $prompts['system'], $prompts['user'] );

				if ( ! is_wp_error( $response ) ) {
					$current_output = $response;
					$results[] = array( 'mind_id' => $mind_id, 'content' => $response );
				}
			}
		}

		// Save Council Output to CPT
		$output_id = wp_insert_post( array(
			'post_title'   => "Council Session: " . current_time( 'mysql' ),
			'post_content' => $current_output,
			'post_status'  => 'publish',
			'post_type'    => 'ai_outputs',
			'post_author'  => $user_id,
		));

		return rest_ensure_response( array(
			'success' => true,
			'output_id' => $output_id,
			'final_output' => $current_output,
			'sequence' => $results
		) );
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
	 * Handle team member removal
	 */
	public function handle_remove_member( $request ) {
		global $wpdb;
		$params = $request->get_json_params();
		$user_id_to_remove = (int)$params['user_id'];
		$team_id = (int)$params['team_id'];
		$owner_id = get_current_user_id();

		// Verify ownership
		$team = $wpdb->get_row( $wpdb->prepare( "SELECT owner_id FROM {$wpdb->prefix}amm_teams WHERE id = %d", $team_id ) );
		if ( ! $team || (int)$team->owner_id !== $owner_id ) {
			return new WP_Error( 'forbidden', 'Only team owners can remove members.', array( 'status' => 403 ) );
		}

		$wpdb->delete( $wpdb->prefix . 'amm_team_members', array( 'team_id' => $team_id, 'user_id' => $user_id_to_remove ) );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle Success Coach Chat
	 */
	public function handle_support_chat( $request ) {
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$message = $params['message'] ?? '';
		$provider = get_option( 'amm_default_ai_provider', 'gemini' );

		$system_prompt = "You are the AI Success Coach for the AI Multi-Mind Engine. Your goal is to help users get the most value out of our 50+ business minds. Be encouraging, strategic, and concise. If they ask about features, explain how to use the 'Council' or 'Magic BFF'.";

		$ai_manager = new AMM_AI_Provider_Manager();
		$response = $ai_manager->generate_response( $provider, $system_prompt, $message );

		return rest_ensure_response( array( 'success' => true, 'reply' => $response ) );
	}

	public function handle_member_role_update( $request ) {
		global $wpdb;
		$user_id = get_current_user_id();
		$params = $request->get_json_params();
		$team_id = (int)$params['team_id'];
		$target_user_id = (int)$params['user_id'];
		$permissions = (array)$params['permissions'];

		// Verify ownership
		$owner = $wpdb->get_var( $wpdb->prepare( "SELECT owner_id FROM {$wpdb->prefix}amm_teams WHERE id = %d", $team_id ) );
		if ( (int)$owner !== $user_id ) return new WP_Error( 'forbidden', 'Only owners can update roles.', array( 'status' => 403 ) );

		$wpdb->update(
			$wpdb->prefix . 'amm_team_members',
			array( 'permissions' => json_encode($permissions) ),
			array( 'team_id' => $team_id, 'user_id' => $target_user_id )
		);

		return rest_ensure_response( array( 'success' => true ) );
	}

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

		if ( isset( $params['knowledge_files'] ) ) {
			update_user_meta( $user_id, 'amm_knowledge_files', (array)$params['knowledge_files'] );
		}

		if ( isset( $params['company_name'] ) ) {
			update_user_meta( $user_id, 'amm_company_name', sanitize_text_field( $params['company_name'] ) );
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
			$is_premium = get_post_meta( $p->ID, 'amm_template_is_premium', true ) === 'yes';
			$price = (int)get_post_meta( $p->ID, 'amm_template_price', true ) ?: 19;

			$has_plan_access = ( $plans[$user_plan] ?? 0 ) >= ( $plans[$min_plan] ?? 0 );

			$purchased = false;
			if ( $is_premium ) {
				global $wpdb;
				$purchased = (bool)$wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}amm_purchases WHERE user_id = %d AND mind_id = %s",
					$user_id, 'template_' . $p->ID
				));
			}

			$is_locked = ! $has_plan_access && ( ! $is_premium || ! $purchased );

			$data[] = array(
				'id' => $p->ID,
				'title' => $p->post_title,
				'content' => $is_locked ? '' : $p->post_content,
				'locked' => $is_locked,
				'min_plan' => $min_plan,
				'premium' => $is_premium,
				'price' => $price,
				'purchased' => $purchased
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
	 * Get recent activity for the user's team
	 */
	public function handle_stripe_webhook() {
		$stripe = new AMM_Stripe_Handler();
		$res = $stripe->handle_webhook();
		if ( is_wp_error( $res ) ) return $res;
		return rest_ensure_response( array( 'success' => true ) );
	}

	public function handle_paypal_webhook() {
		$paypal = new AMM_PayPal_Handler();
		$res = $paypal->handle_webhook();
		if ( is_wp_error( $res ) ) return $res;
		return rest_ensure_response( array( 'success' => true ) );
	}

	public function get_team_activity( $request ) {
		global $wpdb;
		$user_id = get_current_user_id();
		$team_id = (int)$request->get_param('team_id');

		// Verify membership
		$is_member = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}amm_team_members WHERE team_id = %d AND user_id = %d", $team_id, $user_id ) );
		if ( ! $is_member ) return new WP_Error( 'forbidden', 'Unauthorized.', array( 'status' => 403 ) );

		$members = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}amm_team_members WHERE team_id = %d", $team_id ) );

		$activity = get_posts( array(
			'post_type'      => 'ai_outputs',
			'author__in'     => $members,
			'posts_per_page' => 10,
		));

		$data = array();
		foreach ( $activity as $a ) {
			$user = get_userdata( $a->post_author );
			$data[] = array(
				'title' => $a->post_title,
				'user'  => $user->display_name,
				'date'  => get_the_date( 'Y-m-d H:i', $a->ID )
			);
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get user teams
	 */
	public function handle_create_team( $request ) {
		$user_id = get_current_user_id();
		$plan_id = get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free';
		if ( $plan_id !== 'agency' ) return new WP_Error( 'forbidden', 'Only Agency users can create teams.', array( 'status' => 403 ) );

		$params = $request->get_json_params();
		$name = sanitize_text_field( $params['name'] );

		$team_manager = new AMM_Team_Manager();
		$team_id = $team_manager->create_team( $user_id, $name );
		$team_manager->add_member( $team_id, $user_id, 'admin' );

		return rest_ensure_response( array( 'success' => true, 'team_id' => $team_id ) );
	}

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
		global $wpdb;
		$user_id = get_current_user_id();
		$tracker = new AMM_Usage_Tracker();
		$team_manager = new AMM_Team_Manager();
		$teams = $team_manager->get_user_teams( $user_id );

		// Real Usage Trends (Daily generations for last 7 days)
		$trends = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$date = date( 'Y-m-d', strtotime( "-$i days" ) );
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'ai_outputs' AND post_date LIKE %s",
				$user_id, $date . '%'
			));
			$trends[] = (int)$count;
		}

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
				'knowledge_files' => get_user_meta( $user_id, 'amm_knowledge_files', true ) ?: array(),
				'company_name' => get_user_meta( $user_id, 'amm_company_name', true ),
				'usage_alerts' => get_user_meta( $user_id, 'amm_usage_alerts', true ) === 'yes',
			),
			'usage'   => array(
				'used'  => $tracker->get_current_month_usage( $user_id ),
				'limit' => $tracker->get_plan_limit( get_user_meta( $user_id, 'amm_plan_id', true ) ?: 'free' ),
				'trends' => $trends,
			),
			'insights' => array(
				'total_generations' => count( get_posts( array( 'post_type' => 'ai_outputs', 'author' => $user_id, 'posts_per_page' => -1 ) ) ),
				'referral_count' => $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->prefix}amm_referrals r JOIN {$wpdb->prefix}amm_affiliates a ON r.affiliate_id = a.id WHERE a.user_id = %d", $user_id ) ) ?: 0,
			),
			'team_branding' => !empty($teams) ? array(
				'logo' => $teams[0]->custom_logo,
				'color' => $teams[0]->primary_color,
			) : null
		));
	}
}
