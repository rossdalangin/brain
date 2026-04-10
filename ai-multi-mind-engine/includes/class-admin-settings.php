<?php
/**
 * Admin Settings UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Admin_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu_page() {
		add_menu_page(
			'AI Multi-Mind Settings',
			'AI SaaS Settings',
			'manage_options',
			'amm-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-admin-generic'
		);
	}

	public function register_settings() {
		register_setting( 'amm_settings_group', 'amm_gemini_api_key', array( 'sanitize_callback' => array( $this, 'encrypt_key' ) ) );
		register_setting( 'amm_settings_group', 'amm_openai_api_key', array( 'sanitize_callback' => array( $this, 'encrypt_key' ) ) );
		register_setting( 'amm_settings_group', 'amm_claude_api_key', array( 'sanitize_callback' => array( $this, 'encrypt_key' ) ) );
		register_setting( 'amm_settings_group', 'amm_default_ai_provider' );
		register_setting( 'amm_settings_group', 'amm_stripe_secret_key' );
		register_setting( 'amm_settings_group', 'amm_stripe_webhook_secret' );
		register_setting( 'amm_settings_group', 'amm_stripe_price_starter' );
		register_setting( 'amm_settings_group', 'amm_stripe_price_pro' );
		register_setting( 'amm_settings_group', 'amm_stripe_price_agency' );
	}

	public function encrypt_key( $value ) {
		if ( empty( $value ) ) return '';
		return base64_encode( $value );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>AI Multi-Mind SaaS Engine Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'amm_settings_group' ); ?>
				<?php do_settings_sections( 'amm_settings_group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Default AI Provider</th>
						<td>
							<select name="amm_default_ai_provider">
								<option value="gemini" <?php selected( get_option('amm_default_ai_provider'), 'gemini' ); ?>>Gemini (Free)</option>
								<option value="openai" <?php selected( get_option('amm_default_ai_provider'), 'openai' ); ?>>OpenAI (GPT-4)</option>
								<option value="claude" <?php selected( get_option('amm_default_ai_provider'), 'claude' ); ?>>Anthropic Claude</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Gemini API Key</th>
						<td><input type="password" name="amm_gemini_api_key" value="<?php echo esc_attr( get_option('amm_gemini_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">OpenAI API Key</th>
						<td><input type="password" name="amm_openai_api_key" value="<?php echo esc_attr( get_option('amm_openai_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Claude API Key</th>
						<td><input type="password" name="amm_claude_api_key" value="<?php echo esc_attr( base64_decode(get_option('amm_claude_api_key')) ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Stripe Secret Key</th>
						<td><input type="password" name="amm_stripe_secret_key" value="<?php echo esc_attr( get_option('amm_stripe_secret_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Stripe Webhook Secret</th>
						<td><input type="password" name="amm_stripe_webhook_secret" value="<?php echo esc_attr( get_option('amm_stripe_webhook_secret') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Stripe Starter Price ID</th>
						<td><input type="text" name="amm_stripe_price_starter" value="<?php echo esc_attr( get_option('amm_stripe_price_starter') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Stripe Pro Price ID</th>
						<td><input type="text" name="amm_stripe_price_pro" value="<?php echo esc_attr( get_option('amm_stripe_price_pro') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Stripe Agency Price ID</th>
						<td><input type="text" name="amm_stripe_price_agency" value="<?php echo esc_attr( get_option('amm_stripe_price_agency') ); ?>" class="regular-text" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
