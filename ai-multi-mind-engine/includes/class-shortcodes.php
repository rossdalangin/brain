<?php
/**
 * Shortcodes for the SaaS Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Shortcodes {

	public function __construct() {
		add_shortcode( 'amm_dashboard', array( $this, 'render_dashboard' ) );
	}

	public function render_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>Please <a href="' . wp_login_url() . '">login</a> to access the AI Multi-Mind Engine.</p>';
		}

		ob_start();
		?>
		<div id="amm-dashboard-root" class="amm-dashboard">
			<header class="amm-header">
				<h2>AI Multi-Mind SaaS Engine</h2>
				<div id="amm-user-stats">Loading stats...</div>
			</header>

			<div class="amm-grid">
				<aside class="amm-sidebar">
					<h3>Select Mind</h3>
					<select id="amm-mind-select">
						<option value="">Loading minds...</option>
					</select>

					<h3>Output Type</h3>
					<select id="amm-type-select">
						<option value="business_plan">Business Plan</option>
						<option value="marketing_plan">Marketing Plan</option>
						<option value="sales_script">Sales Script</option>
						<option value="sop">SOP</option>
					</select>
				</aside>

				<main class="amm-main">
					<textarea id="amm-input" placeholder="Enter your request or context here..."></textarea>
					<button id="amm-generate-btn" class="button button-primary">IGNITE MIND</button>

					<div id="amm-output-container">
						<h3>Output</h3>
						<div id="amm-output" class="amm-output-box">
							Your AI-generated business output will appear here...
						</div>
					</div>
				</main>
			</div>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const btn = document.getElementById('amm-generate-btn');
			const output = document.getElementById('amm-output');
			const stats = document.getElementById('amm-user-stats');
			const apiRoot = '<?php echo esc_url_raw( rest_url( 'amm/v1' ) ); ?>';

			// Fetch User Stats
			fetch(apiRoot + '/user', {
				headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
			})
			.then(res => res.json())
			.then(data => {
				stats.innerHTML = `Plan: ${data.plan} | Usage: ${data.usage.used}/${data.usage.limit} credits`;
			});

			// Fetch Minds Library
			const mindSelect = document.getElementById('amm-mind-select');
			fetch(apiRoot + '/minds', {
				headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
			})
			.then(res => res.json())
			.then(minds => {
				mindSelect.innerHTML = minds.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
			});

			// Handle Generation
			btn.addEventListener('click', function() {
				const mindId = document.getElementById('amm-mind-select').value;
				const outputType = document.getElementById('amm-type-select').value;
				const userInput = document.getElementById('amm-input').value;

				btn.disabled = true;
				output.innerHTML = 'Thinking...';

				fetch(apiRoot + '/generate', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
					},
					body: JSON.stringify({
						mind_id: mindId,
						output_type: outputType,
						user_input: userInput
					})
				})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						output.innerHTML = data.content;
						stats.innerHTML = `Plan: ${data.plan || '...'} | Usage: ${data.usage}/${data.limit || '...'} credits`;
					} else {
						output.innerHTML = 'Error: ' + data.message;
					}
				})
				.finally(() => {
					btn.disabled = false;
				});
			});
		});
		</script>

		<style>
		.amm-dashboard { font-family: sans-serif; max-width: 1000px; margin: 20px auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
		.amm-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 20px; }
		.amm-grid { display: grid; grid-template-columns: 250px 1fr; gap: 20px; }
		.amm-sidebar select { width: 100%; margin-bottom: 20px; }
		.amm-main textarea { width: 100%; height: 150px; margin-bottom: 10px; padding: 10px; }
		.amm-output-box { background: #fff; border: 1px solid #ddd; padding: 15px; min-height: 200px; white-space: pre-wrap; }
		.amm-generate-btn { background: #007cba; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
		</style>
		<?php
		return ob_get_clean();
	}
}
