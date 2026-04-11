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
		register_setting( 'amm_settings_group', 'amm_stripe_price_mind_unlock' );
		register_setting( 'amm_settings_group', 'amm_stripe_price_agency' );
	}

	public function encrypt_key( $value ) {
		if ( empty( $value ) ) return '';

		$encryption_key = defined('AUTH_SALT') ? AUTH_SALT : 'default_fallback_salt';
		$iv_length = openssl_cipher_iv_length('aes-256-cbc');
		$iv = openssl_random_pseudo_bytes($iv_length);

		$encrypted = openssl_encrypt($value, 'aes-256-cbc', $encryption_key, 0, $iv);
		return base64_encode($iv . $encrypted);
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
								<option value="free_jules" <?php selected( get_option('amm_default_ai_provider'), 'free_jules' ); ?>>Free Jules (Fallback)</option>
								<option value="gemini" <?php selected( get_option('amm_default_ai_provider'), 'gemini' ); ?>>Gemini (Free)</option>
								<option value="openai" <?php selected( get_option('amm_default_ai_provider'), 'openai' ); ?>>OpenAI (GPT-4)</option>
								<option value="claude" <?php selected( get_option('amm_default_ai_provider'), 'claude' ); ?>>Anthropic Claude</option>
							</select>
						</td>
					</tr>
					<?php
					$ai_manager = new AMM_AI_Provider_Manager();
					$ref = new ReflectionClass('AMM_AI_Provider_Manager');
					$method = $ref->getMethod('get_decrypted_option');
					$method->setAccessible(true);
					?>
					<tr valign="top">
						<th scope="row">Gemini API Key</th>
						<td><input type="password" name="amm_gemini_api_key" value="<?php echo esc_attr( $method->invoke($ai_manager, 'amm_gemini_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">OpenAI API Key</th>
						<td><input type="password" name="amm_openai_api_key" value="<?php echo esc_attr( $method->invoke($ai_manager, 'amm_openai_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Claude API Key</th>
						<td><input type="password" name="amm_claude_api_key" value="<?php echo esc_attr( $method->invoke($ai_manager, 'amm_claude_api_key') ); ?>" class="regular-text" /></td>
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
					<tr valign="top">
						<th scope="row">Stripe Mind Unlock Price ID</th>
						<td><input type="text" name="amm_stripe_price_mind_unlock" value="<?php echo esc_attr( get_option('amm_stripe_price_mind_unlock') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Global System Context</th>
						<td><textarea name="amm_global_system_context" class="large-text" rows="5"><?php echo esc_textarea( get_option('amm_global_system_context') ); ?></textarea>
						<p class="description">This ruleset will be applied to ALL AI Minds site-wide.</p></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<h2>System Health Monitor</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Service</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Gemini API</td>
						<td id="status-gemini"><?php echo get_option('amm_gemini_api_key') ? '✅ Configured' : '❌ Missing'; ?></td>
						<td><button type="button" class="button amm-test-btn" data-service="gemini">Test</button></td>
					</tr>
					<tr>
						<td>OpenAI API</td>
						<td id="status-openai"><?php echo get_option('amm_openai_api_key') ? '✅ Configured' : '❌ Missing'; ?></td>
						<td><button type="button" class="button amm-test-btn" data-service="openai">Test</button></td>
					</tr>
					<tr>
						<td>Claude API</td>
						<td id="status-claude"><?php echo get_option('amm_claude_api_key') ? '✅ Configured' : '❌ Missing'; ?></td>
						<td><button type="button" class="button amm-test-btn" data-service="claude">Test</button></td>
					</tr>
					<tr>
						<td>Stripe Integration</td>
						<td id="status-stripe"><?php echo get_option('amm_stripe_secret_key') ? '✅ Configured' : '❌ Missing'; ?></td>
						<td><button type="button" class="button amm-test-btn" data-service="stripe">Verify</button></td>
					</tr>
				</tbody>
			</table>

			<hr>
			<h2>SaaS Analytics & Insights</h2>
			<?php
			$analytics = new AMM_Analytics_Manager();
			$stats = $analytics->get_stats();
			?>
			<div style="display:flex; gap:20px; margin-top:20px;">
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1;">
					<strong>Total Credits Used</strong><br>
					<span style="font-size:24px; color:#007cba;"><?php echo number_format($stats['total_credits_used']); ?></span>
				</div>
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1;">
					<strong>Active Subscriptions</strong><br>
					<span style="font-size:24px; color:#007cba;"><?php echo number_format($stats['active_subscriptions']); ?></span>
				</div>
			</div>

			<h3>User Management & Credit Control</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>User</th><th>Plan</th><th>Credits Used</th><th>Actions</th></tr></thead>
				<tbody>
					<?php
					$users = get_users();
					$tracker = new AMM_Usage_Tracker();
					foreach($users as $u):
						$used = $tracker->get_current_month_usage($u->ID);
						$plan = get_user_meta($u->ID, 'amm_plan_id', true) ?: 'free';
					?>
						<tr>
							<td><?php echo esc_html($u->display_name); ?></td>
							<td><?php echo esc_html(strtoupper($plan)); ?></td>
							<td><?php echo number_format($used); ?> / <?php echo $tracker->get_plan_limit($plan); ?></td>
							<td><button class="button">Reset Credits</button> <button class="button">Change Plan</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h3>Affiliate Network Management</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Affiliate User</th><th>Code</th><th>Total Commissions</th><th>Referral Count</th></tr></thead>
				<tbody>
					<?php
					global $wpdb;
					$affiliates = $wpdb->get_results( "SELECT a.*, u.display_name FROM {$wpdb->prefix}amm_affiliates a JOIN wp_users u ON a.user_id = u.ID" );
					foreach($affiliates as $a):
						$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}amm_referrals WHERE affiliate_id = %d", $a->id ) );
					?>
						<tr>
							<td><?php echo esc_html($a->display_name); ?></td>
							<td><code><?php echo esc_html($a->affiliate_code); ?></code></td>
							<td>$<?php echo number_format($a->total_commissions, 2); ?></td>
							<td><?php echo (int)$count; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h3>Mind Popularity (Total Generations)</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Mind ID</th><th>Usage Count</th></tr></thead>
				<tbody>
					<?php foreach($stats['mind_popularity'] as $mind => $count): ?>
						<tr><td><?php echo esc_html($mind); ?></td><td><?php echo number_format($count); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('.amm-test-btn').on('click', function() {
				var btn = $(this);
				var service = btn.data('service');
				var statusCell = $('#status-' + service);

				btn.prop('disabled', true).text('Testing...');

				$.ajax({
					url: '<?php echo esc_url_raw( rest_url( "amm/v1/admin/test-api" ) ); ?>',
					method: 'POST',
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce("wp_rest"); ?>');
					},
					contentType: 'application/json',
					data: JSON.stringify({ service: service }),
					success: function(response) {
						if (response.success) {
							statusCell.html('✅ ' + response.message).css('color', 'green');
						} else {
							statusCell.html('❌ Error').css('color', 'red');
						}
					},
					error: function(xhr) {
						var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Connection Failed';
						statusCell.html('❌ ' + msg).css('color', 'red');
					},
					complete: function() {
						btn.prop('disabled', false).text(service === 'stripe' ? 'Verify' : 'Test');
					}
				});
			});
		});
		</script>
		<?php
	}
}
