<?php
/**
 * Plugin Name: AI Multi-Mind SaaS Engine
 * Plugin URI: https://example.com/ai-multi-mind
 * Description: A COMPLETE, production-ready SaaS platform built on WordPress for elite business AI Minds.
 * Version: 1.0.0
 * Author: Jules (World-Class SaaS Architect)
 * Author URI: https://example.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'AMM_VERSION', '1.0.0' );
define( 'AMM_PATH', plugin_dir_path( __FILE__ ) );
define( 'AMM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main AI Multi-Mind SaaS Engine Class
 */
class AI_Multi_Mind_Engine {

	/**
	 * Instance of this class
	 */
	protected static $instance = null;

	/**
	 * Get instance of this class
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files
	 */
	private function includes() {
		// Base Minds
		require_once AMM_PATH . 'includes/minds/class-mind-base.php';
		require_once AMM_PATH . 'includes/minds/class-mind-dynamic.php';
		require_once AMM_PATH . 'includes/minds/class-mind-ceo.php';
		require_once AMM_PATH . 'includes/minds/class-mind-strategist.php';
		require_once AMM_PATH . 'includes/minds/class-mind-funnel-builder.php';
		require_once AMM_PATH . 'includes/minds/class-mind-growth-hacker.php';
		require_once AMM_PATH . 'includes/minds/class-mind-copywriter.php';
		require_once AMM_PATH . 'includes/minds/class-mind-sales-closer.php';
		require_once AMM_PATH . 'includes/minds/class-mind-profit-maximizer.php';
		require_once AMM_PATH . 'includes/minds/class-mind-offer-creator.php';
		require_once AMM_PATH . 'includes/minds/class-mind-sop-architect.php';
		require_once AMM_PATH . 'includes/minds/class-mind-viral-creator.php';
		require_once AMM_PATH . 'includes/minds/class-mind-visionary.php';
		require_once AMM_PATH . 'includes/minds/class-mind-dominator.php';
		require_once AMM_PATH . 'includes/minds/class-mind-automation-expert.php';
		require_once AMM_PATH . 'includes/minds/class-mind-storytelling-expert.php';
		require_once AMM_PATH . 'includes/minds/class-mind-batch-1.php';
		require_once AMM_PATH . 'includes/minds/class-mind-batch-2.php';
		require_once AMM_PATH . 'includes/minds/class-mind-batch-3.php';

		// Managers
		require_once AMM_PATH . 'includes/class-ai-provider-manager.php';
		require_once AMM_PATH . 'includes/class-team-manager.php';
		require_once AMM_PATH . 'includes/class-affiliate-manager.php';
		require_once AMM_PATH . 'includes/class-analytics-manager.php';
		require_once AMM_PATH . 'includes/class-webhook-manager.php';
		require_once AMM_PATH . 'includes/class-prompt-engine.php';
		require_once AMM_PATH . 'includes/class-usage-tracker.php';

		// Payments
		require_once AMM_PATH . 'includes/payments/class-stripe-handler.php';
		require_once AMM_PATH . 'includes/payments/class-paypal-handler.php';

		// API
		require_once AMM_PATH . 'includes/class-rest-api.php';
		require_once AMM_PATH . 'includes/class-shortcodes.php';

		// Admin
		if ( is_admin() ) {
			require_once AMM_PATH . 'includes/class-admin-settings.php';
			require_once AMM_PATH . 'includes/class-admin-meta-boxes.php';
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'init_shortcodes' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		if ( is_admin() ) {
			add_action( 'plugins_loaded', array( $this, 'init_admin' ) );
		}
	}

	public function init_admin() {
		new AMM_Admin_Settings();
		new AMM_Admin_Meta_Boxes();
	}

	public function init_shortcodes() {
		new AMM_Shortcodes();
	}

	/**
	 * Register Custom Post Types
	 */
	public function register_post_types() {
		register_post_type( 'ai_minds', array(
			'labels' => array( 'name' => 'AI Minds', 'singular_name' => 'AI Mind' ),
			'public' => true,
			'show_in_rest' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'menu_icon' => 'dashicons-brain',
		));

		register_post_type( 'ai_outputs', array(
			'labels' => array( 'name' => 'AI Outputs', 'singular_name' => 'AI Output' ),
			'public' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'supports' => array( 'title', 'editor', 'author' ),
			'menu_icon' => 'dashicons-media-text',
		));

		register_taxonomy( 'amm_folder', 'ai_outputs', array(
			'labels' => array( 'name' => 'Folders', 'singular_name' => 'Folder' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		));

		register_post_type( 'ai_templates', array(
			'labels' => array( 'name' => 'Task Templates', 'singular_name' => 'Task Template' ),
			'public' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'supports' => array( 'title', 'editor' ),
			'menu_icon' => 'dashicons-forms',
		));
	}

	/**
	 * Activation hook: Create custom tables
	 */
	public function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Subscriptions Table
		$table_name = $wpdb->prefix . 'amm_subscriptions';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			plan_id varchar(50) NOT NULL,
			gateway varchar(20) NOT NULL,
			subscription_id varchar(100) NOT NULL,
			status varchar(20) NOT NULL,
			current_period_end datetime NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Usage Table
		$table_name = $wpdb->prefix . 'amm_usage';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			month varchar(7) NOT NULL,
			credits_used int(11) DEFAULT 0 NOT NULL,
			last_reset datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Teams Table
		$table_name = $wpdb->prefix . 'amm_teams';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			owner_id bigint(20) NOT NULL,
			team_name varchar(255) NOT NULL,
			custom_logo text,
			primary_color varchar(20) DEFAULT '#007cba',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Team Members Table
		$table_name = $wpdb->prefix . 'amm_team_members';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			team_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			role varchar(20) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Affiliates Table
		$table_name = $wpdb->prefix . 'amm_affiliates';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			affiliate_code varchar(50) NOT NULL,
			total_commissions decimal(10,2) DEFAULT 0.00 NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Referrals Table
		$table_name = $wpdb->prefix . 'amm_referrals';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			affiliate_id bigint(20) NOT NULL,
			referred_user_id bigint(20) NOT NULL,
			status varchar(20) NOT NULL,
			commission_amount decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Team Invites Table
		$table_name = $wpdb->prefix . 'amm_invites';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			team_id bigint(20) NOT NULL,
			email varchar(100) NOT NULL,
			token varchar(50) NOT NULL,
			status varchar(20) DEFAULT 'pending' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );
	}
}

// Initialize the plugin
function AMM() {
	return AI_Multi_Mind_Engine::get_instance();
}
AMM();
