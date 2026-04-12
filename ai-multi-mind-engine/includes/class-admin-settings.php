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
		register_setting( 'amm_settings_group', 'amm_stripe_price_topup' );

		// Plan Limits & Pricing
		register_setting( 'amm_settings_group', 'amm_plan_free_credits' );
		register_setting( 'amm_settings_group', 'amm_plan_starter_credits' );
		register_setting( 'amm_settings_group', 'amm_plan_pro_credits' );
		register_setting( 'amm_settings_group', 'amm_plan_agency_credits' );

		register_setting( 'amm_settings_group', 'amm_plan_starter_price' );
		register_setting( 'amm_settings_group', 'amm_plan_pro_price' );
		register_setting( 'amm_settings_group', 'amm_plan_agency_price' );
		register_setting( 'amm_settings_group', 'amm_viral_automation_webhook' );
		register_setting( 'amm_settings_group', 'amm_paypal_client_id' );
		register_setting( 'amm_settings_group', 'amm_paypal_client_secret' );
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
					<?php $ai_manager = new AMM_AI_Provider_Manager(); ?>
					<tr valign="top">
						<th scope="row">Gemini API Key</th>
						<td><input type="password" name="amm_gemini_api_key" value="<?php echo esc_attr( $ai_manager->get_decrypted_option('amm_gemini_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">OpenAI API Key</th>
						<td><input type="password" name="amm_openai_api_key" value="<?php echo esc_attr( $ai_manager->get_decrypted_option('amm_openai_api_key') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Claude API Key</th>
						<td><input type="password" name="amm_claude_api_key" value="<?php echo esc_attr( $ai_manager->get_decrypted_option('amm_claude_api_key') ); ?>" class="regular-text" /></td>
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
						<th scope="row">Stripe Top-up (50) Price ID</th>
						<td><input type="text" name="amm_stripe_price_topup" value="<?php echo esc_attr( get_option('amm_stripe_price_topup') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">PayPal Client ID</th>
						<td><input type="password" name="amm_paypal_client_id" value="<?php echo esc_attr( get_option('amm_paypal_client_id') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">PayPal Client Secret</th>
						<td><input type="password" name="amm_paypal_client_secret" value="<?php echo esc_attr( get_option('amm_paypal_client_secret') ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Viral Automation Webhook (Slack/Discord)</th>
						<td><input type="text" name="amm_viral_automation_webhook" value="<?php echo esc_attr( get_option('amm_viral_automation_webhook') ); ?>" class="regular-text" />
						<p class="description">Global webhook for team activity notifications.</p></td>
					</tr>
					<tr valign="top">
						<th scope="row">Plan Configurations</th>
						<td>
							<table class="widefat fixed striped">
								<thead><tr><th>Plan</th><th>Monthly Credits</th><th>Display Price ($)</th></tr></thead>
								<tbody>
									<tr>
										<td>Free</td>
										<td><input type="number" name="amm_plan_free_credits" value="<?php echo (int)get_option('amm_plan_free_credits', 5); ?>" style="width:100px;"></td>
										<td>-</td>
									</tr>
									<tr>
										<td>Starter</td>
										<td><input type="number" name="amm_plan_starter_credits" value="<?php echo (int)get_option('amm_plan_starter_credits', 50); ?>" style="width:100px;"></td>
										<td><input type="number" name="amm_plan_starter_price" value="<?php echo (int)get_option('amm_plan_starter_price', 19); ?>" style="width:100px;"></td>
									</tr>
									<tr>
										<td>Pro</td>
										<td><input type="number" name="amm_plan_pro_credits" value="<?php echo (int)get_option('amm_plan_pro_credits', 200); ?>" style="width:100px;"></td>
										<td><input type="number" name="amm_plan_pro_price" value="<?php echo (int)get_option('amm_plan_pro_price', 49); ?>" style="width:100px;"></td>
									</tr>
									<tr>
										<td>Agency</td>
										<td><input type="number" name="amm_plan_agency_credits" value="<?php echo (int)get_option('amm_plan_agency_credits', 1000); ?>" style="width:100px;"></td>
										<td><input type="number" name="amm_plan_agency_price" value="<?php echo (int)get_option('amm_plan_agency_price', 199); ?>" style="width:100px;"></td>
									</tr>
								</tbody>
							</table>
						</td>
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

			<div style="display:flex; gap:20px; margin-top:20px;">
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1; border-left: 5px solid #28a745;">
					<strong>Monthly Recurring Revenue (MRR)</strong><br>
					<span style="font-size:24px; color:#28a745;">$<?php echo number_format($stats['revenue_metrics']['mrr']); ?></span>
				</div>
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1;">
					<strong>Average Revenue Per User (ARPU)</strong><br>
					<span style="font-size:24px; color:#007cba;">$<?php echo number_format($stats['revenue_metrics']['arpu'], 2); ?></span>
				</div>
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1; border-left: 5px solid #dc3545;">
					<strong>Churn Rate (30d)</strong><br>
					<span style="font-size:24px; color:#dc3545;"><?php echo $stats['revenue_metrics']['churn_rate']; ?>%</span>
				</div>
				<div style="background:#fff; padding:20px; border:1px solid #ddd; flex:1;">
					<strong>Estimated Customer LTV</strong><br>
					<span style="font-size:24px; color:#007cba;">$<?php echo number_format($stats['revenue_metrics']['ltv'], 2); ?></span>
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
							<td id="usage-<?php echo $u->ID; ?>"><?php echo number_format($used); ?> / <?php echo $tracker->get_plan_limit($plan); ?></td>
							<td>
								<button type="button" class="button amm-reset-credits" data-user-id="<?php echo $u->ID; ?>">Reset Credits</button>
								<button type="button" class="button amm-adjust-credits" data-user-id="<?php echo $u->ID; ?>">+ Adjust</button>
								<select class="amm-change-plan" data-user-id="<?php echo $u->ID; ?>">
									<option value="free" <?php selected($plan, 'free'); ?>>Free</option>
									<option value="starter" <?php selected($plan, 'starter'); ?>>Starter</option>
									<option value="pro" <?php selected($plan, 'pro'); ?>>Pro</option>
									<option value="agency" <?php selected($plan, 'agency'); ?>>Agency</option>
								</select>
							</td>
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

			<h3>SaaS Audit Trail (Last 50 Events)</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Time</th><th>User ID</th><th>Event</th><th>Details</th></tr></thead>
				<tbody>
					<?php
					global $wpdb;
					$logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}amm_audit_trail ORDER BY created_at DESC LIMIT 50" );
					foreach($logs as $l):
					?>
						<tr>
							<td><?php echo esc_html($l->created_at); ?></td>
							<td><?php echo (int)$l->user_id; ?></td>
							<td><code><?php echo esc_html($l->event_type); ?></code></td>
							<td><?php echo esc_html($l->description); ?></td>
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
			$('.amm-adjust-credits').on('click', function() {
				var userId = $(this).data('user-id');
				var amount = prompt('Amount to add (use negative to subtract):', '10');
				if(!amount) return;

				$.ajax({
					url: '<?php echo esc_url_raw( rest_url( "amm/v1/admin/adjust-credits" ) ); ?>',
					method: 'POST',
					beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce("wp_rest"); ?>'); },
					contentType: 'application/json',
					data: JSON.stringify({ user_id: userId, amount: parseInt(amount) }),
					success: function() { alert('Credits Adjusted!'); location.reload(); }
				});
			});

			$('.amm-reset-credits').on('click', function() {
				var btn = $(this);
				var userId = btn.data('user-id');
				if(!confirm('Reset credits for this user?')) return;

				$.ajax({
					url: '<?php echo esc_url_raw( rest_url( "amm/v1/admin/reset-credits" ) ); ?>',
					method: 'POST',
					beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce("wp_rest"); ?>'); },
					contentType: 'application/json',
					data: JSON.stringify({ user_id: userId }),
					success: function() {
						alert('Credits Reset!');
						$('#usage-' + userId).text('0 / ' + $('#usage-' + userId).text().split('/')[1].trim());
					}
				});
			});

			$('.amm-change-plan').on('change', function() {
				var select = $(this);
				var userId = select.data('user-id');
				var planId = select.val();

				$.ajax({
					url: '<?php echo esc_url_raw( rest_url( "amm/v1/admin/change-plan" ) ); ?>',
					method: 'POST',
					beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce("wp_rest"); ?>'); },
					contentType: 'application/json',
					data: JSON.stringify({ user_id: userId, plan_id: planId }),
					success: function() { alert('Plan Updated!'); location.reload(); }
				});
			});

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
