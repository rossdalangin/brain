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
				<div style="display:flex; align-items:center; gap:15px;">
					<button id="amm-theme-toggle" class="button">🌓 Toggle Mode</button>
					<div id="amm-user-stats">Loading stats...</div>
				</div>
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
						<option value="report">Business Report</option>
						<option value="blog_post">Blog Post</option>
						<option value="ad_copy">Ad Copy</option>
						<option value="video_script">Video Script</option>
					</select>
				</aside>

				<main class="amm-main">
					<textarea id="amm-input" placeholder="Enter your request or context here..."></textarea>
					<button id="amm-generate-btn" class="button button-primary">IGNITE MIND</button>

					<div id="amm-output-container">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<h3>Output</h3>
							<button id="amm-export-btn" class="button" style="display:none;">📄 Export to PDF</button>
						</div>
						<div id="amm-output" class="amm-output-box">
							Your AI-generated business output will appear here...
						</div>
					</div>

					<div id="amm-workspace-container" style="margin-top: 40px;">
						<h3>My Workspace (Saved Outputs)</h3>
						<div id="amm-workspace-list" class="amm-workspace-box">
							Loading saved outputs...
						</div>
					</div>

					<div id="amm-team-container" style="margin-top: 40px; display:none;">
						<h3>My Team</h3>
						<div id="amm-team-list" class="amm-team-box">
							Loading team members...
						</div>
					</div>

					<div id="amm-affiliate-container" style="margin-top: 40px;">
						<h3>Affiliate Program</h3>
						<div id="amm-affiliate-info" class="amm-workspace-box">
							Loading affiliate info...
						</div>
					</div>

					<div id="amm-builder-container" style="margin-top: 40px; display:none;">
						<h3>Custom AI Mind Builder (PRO)</h3>
						<div class="amm-workspace-box">
							<input type="text" id="amm-new-mind-name" placeholder="Mind Name" style="width:100%; margin-bottom:10px;">
							<input type="text" id="amm-new-mind-role" placeholder="Role (e.g. Sales Expert)" style="width:100%; margin-bottom:10px;">
							<textarea id="amm-new-mind-prompt" placeholder="Hidden Prompt Engineering Layer..." style="width:100%; height:80px; margin-bottom:10px;"></textarea>
							<button id="amm-create-mind-btn" class="button button-primary">Create Elite Mind</button>
						</div>
					</div>

					<div id="amm-marketplace-container" style="margin-top: 40px;">
						<h3>Premium Mind Marketplace</h3>
						<div id="amm-marketplace-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:15px;">
							Loading marketplace...
						</div>
					</div>

					<div id="amm-billing-container" style="margin-top: 40px;">
						<h3>Upgrade Your Plan</h3>
						<div class="amm-billing-grid" style="display:flex; gap:10px;">
							<div class="amm-plan-card" style="border:1px solid #ddd; padding:15px; flex:1;">
								<h4>Starter</h4>
								<p>$19/mo</p>
								<button onclick="ammCheckout('starter')">Select</button>
							</div>
							<div class="amm-plan-card" style="border:1px solid #ddd; padding:15px; flex:1; border-color:#007cba;">
								<h4>Pro</h4>
								<p>$49/mo</p>
								<button onclick="ammCheckout('pro')">Select</button>
							</div>
							<div class="amm-plan-card" style="border:1px solid #ddd; padding:15px; flex:1;">
								<h4>Agency</h4>
								<p>$199/mo</p>
								<button onclick="ammCheckout('agency')">Select</button>
							</div>
						</div>
					</div>
				</main>
			</div>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const btn = document.getElementById('amm-generate-btn');
			const themeToggle = document.getElementById('amm-theme-toggle');
			const dashboard = document.getElementById('amm-dashboard-root');

			themeToggle.addEventListener('click', () => {
				dashboard.classList.toggle('dark-mode');
			});
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
				if (data.plan === 'pro' || data.plan === 'agency') {
					document.getElementById('amm-builder-container').style.display = 'block';
				}
			});

			// Fetch Minds Library & Marketplace
			const mindSelect = document.getElementById('amm-mind-select');
			const marketplaceGrid = document.getElementById('amm-marketplace-grid');
			fetch(apiRoot + '/minds', {
				headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
			})
			.then(res => res.json())
			.then(minds => {
				mindSelect.innerHTML = minds.map(m => `<option value="${m.id}">${m.name}${m.premium ? ' (Premium)' : ''}</option>`).join('');

				marketplaceGrid.innerHTML = minds.filter(m => m.premium).map(m => `
					<div class="amm-plan-card" style="border:1px solid gold; padding:10px; text-align:center;">
						<strong>${m.name}</strong>
						<button class="button" onclick="alert('Access this mind with a PRO plan!')">Unlock</button>
					</div>
				`).join('') || 'No premium minds currently listed.';
			});

			// Fetch Workspace
			const workspaceList = document.getElementById('amm-workspace-list');
			function refreshWorkspace() {
				fetch(apiRoot + '/outputs', {
					headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
				})
				.then(res => res.json())
				.then(outputs => {
					if (outputs.length === 0) {
						workspaceList.innerHTML = 'No saved outputs yet.';
						return;
					}
					workspaceList.innerHTML = '<table style="width:100%; text-align:left;">' +
						'<tr><th>Date</th><th>Title</th><th>Actions</th></tr>' +
						outputs.map(o => `<tr><td>${o.date}</td><td>${o.title}</td><td><button onclick="alert(\`Content: \\n\\n\` + ${JSON.stringify(o.content)})">View</button></td></tr>`).join('') +
						'</table>';
				});
			}
			refreshWorkspace();

			// Fetch Team
			const teamContainer = document.getElementById('amm-team-container');
			const teamList = document.getElementById('amm-team-list');
			fetch(apiRoot + '/teams', {
				headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
			})
			.then(res => res.json())
			.then(teams => {
				if (teams && teams.length > 0) {
					teamContainer.style.display = 'block';
					teamList.innerHTML = teams.map(t => `<div><strong>${t.team_name}</strong> (Role: ${t.role})</div>`).join('');
				}
			});

			// Fetch Affiliate Info
			const affInfo = document.getElementById('amm-affiliate-info');
			fetch(apiRoot + '/affiliate', {
				headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
			})
			.then(res => res.json())
			.then(data => {
				affInfo.innerHTML = `
					<p><strong>Your Affiliate Code:</strong> ${data.code}</p>
					<p><strong>Referral Link:</strong> <input type="text" value="${data.link}" readonly style="width:100%;"></p>
					<p><strong>Total Commissions:</strong> $${data.commissions}</p>
				`;
			});

			// Handle Checkout
			window.ammCheckout = function(planId) {
				fetch(apiRoot + '/checkout', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
					},
					body: JSON.stringify({ plan_id: planId, gateway: 'stripe' })
				})
				.then(res => res.json())
				.then(data => {
					if (data.url) {
						window.location.href = data.url;
					} else {
						alert('Checkout error: ' + (data.message || 'Unknown error'));
					}
				});
			};

			// Handle Create Mind
			document.getElementById('amm-create-mind-btn').addEventListener('click', function() {
				const name = document.getElementById('amm-new-mind-name').value;
				const role = document.getElementById('amm-new-mind-role').value;
				const prompt = document.getElementById('amm-new-mind-prompt').value;

				fetch(apiRoot + '/create-mind', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
					},
					body: JSON.stringify({ name, role, prompt, framework: 'Custom', style: 'Custom', structure: 'Custom' })
				})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						alert('Mind Created Successfully!');
						location.reload();
					} else {
						alert('Error: ' + data.message);
					}
				});
			});

			// Handle Export
			document.getElementById('amm-export-btn').addEventListener('click', function() {
				const content = output.innerText;
				const win = window.open('', '_blank');
				win.document.write(`<html><head><title>AI Output</title><style>body{font-family:serif; line-height:1.6; padding:40px; max-width:800px; margin:auto; white-space:pre-wrap;}</style></head><body><h1>AI Multi-Mind Export</h1><hr/>${content}</body></html>`);
				win.document.close();
				win.print();
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
						document.getElementById('amm-export-btn').style.display = 'block';
						stats.innerHTML = `Plan: ${data.plan || '...'} | Usage: ${data.usage}/${data.limit || '...'} credits`;
						refreshWorkspace();
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
		.amm-dashboard { font-family: sans-serif; max-width: 1000px; margin: 20px auto; background: #f9f9f9; color: #333; padding: 20px; border-radius: 8px; transition: all 0.3s; }
		.amm-dashboard.dark-mode { background: #1a1a1a; color: #f1f1f1; }
		.amm-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 20px; }
		.dark-mode .amm-header { border-bottom-color: #333; }
		.amm-grid { display: grid; grid-template-columns: 250px 1fr; gap: 20px; }
		.amm-sidebar select { width: 100%; margin-bottom: 20px; }
		.amm-main textarea { width: 100%; height: 150px; margin-bottom: 10px; padding: 10px; }
		.amm-output-box { background: #fff; border: 1px solid #ddd; padding: 15px; min-height: 200px; white-space: pre-wrap; }
		.dark-mode .amm-output-box { background: #2d2d2d; border-color: #444; color: #eee; }
		.amm-generate-btn { background: #007cba; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
		.amm-plan-card { background: #fff; }
		.dark-mode .amm-plan-card { background: #2d2d2d; color: #eee; }
		</style>
		<?php
		return ob_get_clean();
	}
}
