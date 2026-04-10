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
			<aside class="amm-app-sidebar">
				<div class="amm-logo">AI Multi-Mind</div>
				<nav class="amm-nav">
					<a href="#" class="amm-nav-item active" data-tab="generate">🚀 Ignite Mind</a>
					<a href="#" class="amm-nav-item" data-tab="library">📚 Minds Library</a>
					<a href="#" class="amm-nav-item" data-tab="workspace">📁 My Workspace</a>
					<a href="#" class="amm-nav-item" data-tab="team">👥 Team Hub</a>
					<a href="#" class="amm-nav-item" data-tab="affiliate">💸 Affiliates</a>
					<a href="#" class="amm-nav-item" data-tab="billing">💳 Billing</a>
				</nav>
				<div class="amm-user-block">
					<div id="amm-user-stats-sidebar">Loading stats...</div>
					<button id="amm-theme-toggle" class="amm-theme-btn">🌓 Toggle Mode</button>
				</div>
			</aside>

			<main class="amm-app-content">
				<!-- Generate Tab -->
				<section id="tab-generate" class="amm-tab-content active">
					<header class="amm-tab-header">
						<h2>Generate Elite Strategy</h2>
						<p>Select a mind and an output type to begin the thinking process.</p>
					</header>
					<div class="amm-generate-layout">
						<div class="amm-controls">
							<label>Select Mind</label>
							<select id="amm-mind-select"><option value="">Loading minds...</option></select>
							<label>Output Type</label>
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
							<label>Task Template</label>
							<select id="amm-template-select">
								<option value="">Custom Request...</option>
								<option value="Create a $100M Grand Slam Offer for my service.">Grand Slam Offer ($100M)</option>
								<option value="Design a Blue Ocean strategy to dominate my local market.">Blue Ocean Strategy</option>
								<option value="Write an SOP for hiring and onboarding a new sales rep.">Sales Onboarding SOP</option>
								<option value="Draft a high-ticket sales script for a $5k coaching program.">High-Ticket Sales Script</option>
							</select>
							<textarea id="amm-input" placeholder="Enter your context, goals, and constraints here..."></textarea>
							<button id="amm-generate-btn" class="amm-primary-btn">IGNITE ENGINE</button>
						</div>
						<div class="amm-output-container">
							<div class="amm-output-header">
								<h3>Generated Intelligence</h3>
								<button id="amm-export-btn" class="amm-secondary-btn" style="display:none;">📄 Export PDF</button>
							</div>
							<div id="amm-output" class="amm-output-box">The engine is waiting for your request...</div>
						</div>
					</div>
				</section>

				<!-- Library Tab -->
				<section id="tab-library" class="amm-tab-content">
					<h2>AI Minds Library</h2>
					<div id="amm-marketplace-grid" class="amm-grid-layout">Loading library...</div>

					<div id="amm-builder-container" style="display:none; margin-top:40px;">
						<h3>Custom Mind Builder (PRO)</h3>
						<div class="amm-form-card">
							<input type="text" id="amm-new-mind-name" placeholder="Mind Name (e.g. Real Estate Guru)">
							<input type="text" id="amm-new-mind-role" placeholder="Role Description">
							<textarea id="amm-new-mind-prompt" placeholder="Hidden Prompt Engineering Layer (The 'Brain' of the mind)"></textarea>
							<button id="amm-create-mind-btn" class="amm-primary-btn">Create Custom Mind</button>
						</div>
					</div>
				</section>

				<!-- Workspace Tab -->
				<section id="tab-workspace" class="amm-tab-content">
					<h2>My Workspace</h2>
					<div id="amm-workspace-list" class="amm-form-card">Loading saved outputs...</div>
				</section>

				<!-- Team Tab -->
				<section id="tab-team" class="amm-tab-content">
					<h2>Team Collaboration Hub</h2>
					<div id="amm-team-list" class="amm-form-card">No team members found.</div>
				</section>

				<!-- Affiliate Tab -->
				<section id="tab-affiliate" class="amm-tab-content">
					<h2>Affiliate Program</h2>
					<div id="amm-affiliate-info" class="amm-form-card">Loading your data...</div>
				</section>

				<!-- Billing Tab -->
				<section id="tab-billing" class="amm-tab-content">
					<h2>Plans & Subscription</h2>
					<div class="amm-billing-grid">
						<div class="amm-plan-card"><h4>Starter</h4><p>$19/mo</p><button onclick="ammCheckout('starter')" class="amm-primary-btn">Select</button></div>
						<div class="amm-plan-card featured"><h4>Pro</h4><p>$49/mo</p><button onclick="ammCheckout('pro')" class="amm-primary-btn">Select</button></div>
						<div class="amm-plan-card"><h4>Agency</h4><p>$199/mo</p><button onclick="ammCheckout('agency')" class="amm-primary-btn">Select</button></div>
					</div>
				</section>
			</main>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const apiRoot = '<?php echo esc_url_raw( rest_url( 'amm/v1' ) ); ?>';
			const nonce = '<?php echo wp_create_nonce("wp_rest"); ?>';

			// Tab Logic
			document.querySelectorAll('.amm-nav-item').forEach(item => {
				item.addEventListener('click', (e) => {
					e.preventDefault();
					document.querySelectorAll('.amm-nav-item').forEach(i => i.classList.remove('active'));
					document.querySelectorAll('.amm-tab-content').forEach(c => c.classList.remove('active'));
					item.classList.add('active');
					document.getElementById('tab-' + item.dataset.tab).classList.add('active');
				});
			});

			// Theme Logic
			document.getElementById('amm-theme-toggle').addEventListener('click', () => {
				document.getElementById('amm-dashboard-root').classList.toggle('dark-mode');
			});

			// Template Logic
			document.getElementById('amm-template-select').addEventListener('change', (e) => {
				if(e.target.value) document.getElementById('amm-input').value = e.target.value;
			});

			// Fetch Data
			function initApp() {
				// User Stats
				fetch(apiRoot + '/user', { headers: { 'X-WP-Nonce': nonce } })
					.then(res => res.json()).then(data => {
						document.getElementById('amm-user-stats-sidebar').innerHTML = `<strong>${data.plan.toUpperCase()}</strong><br>${data.usage.used}/${data.usage.limit} credits`;
						if (data.plan === 'pro' || data.plan === 'agency') document.getElementById('amm-builder-container').style.display = 'block';
					});

				// Minds
				fetch(apiRoot + '/minds', { headers: { 'X-WP-Nonce': nonce } })
					.then(res => res.json()).then(minds => {
						document.getElementById('amm-mind-select').innerHTML = minds.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
						document.getElementById('amm-marketplace-grid').innerHTML = minds.map(m => `
							<div class="amm-plan-card ${m.premium ? 'premium' : ''}">
								<strong>${m.name}</strong>
								<p>${m.premium ? 'Premium Mind' : 'Core Mind'}</p>
								<button class="amm-secondary-btn" onclick="document.getElementById('amm-mind-select').value='${m.id}'; document.querySelector('[data-tab=generate]').click();">Use Mind</button>
							</div>
						`).join('');
					});

				// Workspace
				fetch(apiRoot + '/outputs', { headers: { 'X-WP-Nonce': nonce } })
					.then(res => res.json()).then(outputs => {
						document.getElementById('amm-workspace-list').innerHTML = outputs.length ? '<table style="width:100%">' + outputs.map(o => `<tr><td>${o.date}</td><td>${o.title}</td><td><button class="amm-secondary-btn" onclick="alert(${JSON.stringify(o.content)})">View</button></td></tr>`).join('') + '</table>' : 'No outputs saved.';
					});

				// Team
				fetch(apiRoot + '/teams', { headers: { 'X-WP-Nonce': nonce } })
					.then(res => res.json()).then(teams => {
						if(teams.length) document.getElementById('amm-team-list').innerHTML = teams.map(t => `<div><strong>${t.team_name}</strong> (${t.role})</div>`).join('');
					});

				// Affiliate
				fetch(apiRoot + '/affiliate', { headers: { 'X-WP-Nonce': nonce } })
					.then(res => res.json()).then(data => {
						document.getElementById('amm-affiliate-info').innerHTML = `<p>Referral Link: <code>${data.link}</code></p><p>Earnings: $${data.commissions}</p>`;
					});
			}
			initApp();

			// Generate Logic (Typewriter Effect)
			const btn = document.getElementById('amm-generate-btn');
			const outputBox = document.getElementById('amm-output');

			btn.addEventListener('click', () => {
				btn.disabled = true;
				outputBox.innerText = "The mind is thinking...";

				fetch(apiRoot + '/generate', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
					body: JSON.stringify({ mind_id: document.getElementById('amm-mind-select').value, output_type: document.getElementById('amm-type-select').value, user_input: document.getElementById('amm-input').value })
				})
				.then(res => res.json())
				.then(data => {
					if(data.success) {
						outputBox.innerText = "";
						let i = 0;
						const text = data.content;
						const interval = setInterval(() => {
							outputBox.innerText += text[i];
							i++;
							if(i >= text.length) {
								clearInterval(interval);
								btn.disabled = false;
								document.getElementById('amm-export-btn').style.display = 'block';
							}
						}, 5);
					} else {
						outputBox.innerText = "Error: " + data.message;
						btn.disabled = false;
					}
				});
			});

			// Export PDF
			document.getElementById('amm-export-btn').addEventListener('click', () => {
				const win = window.open('', '_blank');
				win.document.write(`<html><body style="font-family:sans-serif; padding:50px;"><h1>AI Multi-Mind Output</h1><hr>${outputBox.innerText}</body></html>`);
				win.print();
			});
		});

		window.ammCheckout = function(planId) {
			fetch('<?php echo esc_url_raw( rest_url( 'amm/v1' ) ); ?>/checkout', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
				body: JSON.stringify({ plan_id: planId, gateway: 'stripe' })
			}).then(res => res.json()).then(data => { if(data.url) window.location.href = data.url; });
		};
		</script>

		<style>
		.amm-dashboard { display: grid; grid-template-columns: 260px 1fr; min-height: 800px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; background: #fff; color: #333; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
		.amm-app-sidebar { background: #f8f9fa; border-right: 1px solid #eee; padding: 30px 20px; display: flex; flex-direction: column; }
		.amm-logo { font-size: 20px; font-weight: 800; color: #007cba; margin-bottom: 40px; text-transform: uppercase; letter-spacing: 1px; }
		.amm-nav { flex-grow: 1; }
		.amm-nav-item { display: block; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: 0.2s; }
		.amm-nav-item:hover, .amm-nav-item.active { background: #007cba; color: #fff; }
		.amm-app-content { padding: 40px; overflow-y: auto; background: #fff; }
		.amm-tab-content { display: none; }
		.amm-tab-content.active { display: block; }
		.amm-generate-layout { display: grid; grid-template-columns: 350px 1fr; gap: 30px; margin-top: 30px; }
		.amm-controls label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 13px; color: #888; }
		.amm-controls select, .amm-controls textarea { width: 100%; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; padding: 12px; }
		.amm-controls textarea { height: 180px; }
		.amm-primary-btn { width: 100%; background: #007cba; color: #fff; border: none; padding: 14px; border-radius: 8px; font-weight: 700; cursor: pointer; }
		.amm-secondary-btn { background: #eee; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; }
		.amm-output-box { background: #f9f9f9; border: 1px solid #eee; border-radius: 12px; padding: 25px; min-height: 400px; white-space: pre-wrap; line-height: 1.6; }
		.amm-grid-layout { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
		.amm-plan-card { border: 1px solid #eee; padding: 20px; border-radius: 12px; text-align: center; background: #fff; }
		.amm-plan-card.premium { border-color: gold; box-shadow: 0 5px 15px rgba(255,215,0,0.1); }
		.amm-plan-card.featured { border: 2px solid #007cba; transform: scale(1.05); }
		.amm-form-card { background: #f9f9f9; padding: 25px; border-radius: 12px; border: 1px solid #eee; }
		.amm-user-block { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 13px; }
		.amm-theme-btn { margin-top: 15px; width: 100%; background: none; border: 1px solid #ddd; border-radius: 6px; padding: 8px; cursor: pointer; }

		/* Dark Mode */
		.dark-mode { background: #111; color: #eee; }
		.dark-mode .amm-app-sidebar { background: #1a1a1a; border-right-color: #333; }
		.dark-mode .amm-app-content { background: #111; }
		.dark-mode .amm-nav-item { color: #aaa; }
		.dark-mode .amm-output-box, .dark-mode .amm-form-card, .dark-mode .amm-plan-card { background: #1a1a1a; border-color: #333; color: #eee; }
		.dark-mode .amm-controls select, .dark-mode .amm-controls textarea { background: #222; border-color: #444; color: #eee; }
		</style>
		<?php
		return ob_get_clean();
	}
}
