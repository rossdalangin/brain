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
		add_shortcode( 'amm_shared_intel', array( $this, 'render_shared_intel' ) );
		add_shortcode( 'amm_landing_page', array( $this, 'render_landing_page' ) );
		add_shortcode( 'amm_join_team', array( $this, 'render_join_team' ) );
		add_shortcode( 'amm_auth', array( $this, 'render_auth' ) );
	}

	public function render_shared_intel() {
		$id = (int)($_GET['id'] ?? 0);
		$post = get_post( $id );
		if ( ! $post || get_post_meta( $id, 'amm_is_public', true ) !== 'yes' ) {
			return '<p>Shared intelligence not found or private.</p>';
		}

		// Apply Agency Branding
		$team_manager = new AMM_Team_Manager();
		$teams = $team_manager->get_user_teams( $post->post_author );
		$brand_logo = ! empty( $teams ) ? $teams[0]->custom_logo : '';
		$brand_color = ! empty( $teams ) ? $teams[0]->primary_color : '#007cba';

		ob_start();
		?>
		<div class="amm-shared-view" style="font-family:'Inter', sans-serif; line-height:1.6; padding:50px; max-width:800px; margin:auto; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.05); border-top: 10px solid <?php echo esc_attr($brand_color); ?>;">
			<?php if ( $brand_logo ): ?>
				<div style="text-align:center; margin-bottom:30px;"><img src="<?php echo esc_url($brand_logo); ?>" style="max-height:60px;"></div>
			<?php endif; ?>
			<h1><?php echo esc_html($post->post_title); ?></h1>
			<p style="color:#888; border-bottom:1px solid #eee; padding-bottom:10px;">Strategic Intelligence via AI Multi-Mind Engine</p>
			<div style="white-space:pre-wrap; margin-top:30px;"><?php echo esc_html($post->post_content); ?></div>
			<div style="margin-top:50px; text-align:center;">
				<?php if ( is_user_logged_in() ): ?>
					<button id="amm-import-shared-btn" class="amm-primary-btn" style="width:auto; padding:15px 30px;" data-id="<?php echo $id; ?>">Import to My Workspace</button>
					<script>
					document.getElementById('amm-import-shared-btn').addEventListener('click', function() {
						const btn = this;
						btn.disabled = true;
						btn.innerText = 'Importing...';
						fetch('<?php echo esc_url_raw( rest_url( "amm/v1/import-shared" ) ); ?>', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
							body: JSON.stringify({ post_id: btn.dataset.id })
						})
						.then(res => res.json())
						.then(data => {
							if(data.success) {
								alert('Intelligence successfully imported to your Workspace!');
								window.location.href = '<?php echo home_url("/dashboard/"); ?>';
							}
						});
					});
					</script>
				<?php else: ?>
					<a href="<?php echo wp_registration_url(); ?>" class="amm-primary-btn" style="width:auto; padding:15px 30px;">Get your own AI Minds</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_join_team() {
		if ( ! is_user_logged_in() ) {
			return '<p>Please <a href="' . wp_login_url( home_url( '/join-team/?' . $_SERVER['QUERY_STRING'] ) ) . '">login or register</a> to accept this invitation.</p>';
		}

		$token = sanitize_text_field( $_GET['token'] ?? '' );
		if ( ! $token ) return '<p>Invalid invitation link.</p>';

		ob_start();
		?>
		<div class="amm-join-view" style="max-width:500px; margin:100px auto; text-align:center; padding:40px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
			<h2>You've been invited! 👥</h2>
			<p>Join the team and start collaborating on elite business strategies.</p>
			<button id="amm-accept-invite-btn" class="amm-primary-btn" data-token="<?php echo esc_attr($token); ?>">Accept Invitation</button>
			<div id="amm-join-msg" style="margin-top:20px;"></div>
		</div>
		<script>
		document.getElementById('amm-accept-invite-btn').addEventListener('click', function() {
			const btn = this;
			const token = btn.dataset.token;
			btn.disabled = true;
			btn.innerText = 'Processing...';

			fetch('<?php echo esc_url_raw( rest_url( "amm/v1/verify-invite" ) ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
				},
				body: JSON.stringify({ token: token })
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					document.getElementById('amm-join-msg').innerHTML = '<p style="color:green;">Successfully joined! Redirecting to dashboard...</p>';
					setTimeout(() => window.location.href = '<?php echo home_url("/dashboard/"); ?>', 2000);
				} else {
					document.getElementById('amm-join-msg').innerHTML = '<p style="color:red;">Error: ' + data.message + '</p>';
					btn.disabled = false;
					btn.innerText = 'Accept Invitation';
				}
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	public function render_auth() {
		if ( is_user_logged_in() ) {
			return '<script>window.location.href="' . home_url('/dashboard/') . '";</script>';
		}

		$mode = $_GET['mode'] ?? 'login';

		ob_start();
		?>
		<div class="amm-auth-view" style="max-width:400px; margin:100px auto; padding:40px; background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.05); text-align:center; font-family:'Inter', sans-serif;">
			<div style="font-size:24px; font-weight:800; color:#007cba; margin-bottom:30px;">AI Multi-Mind</div>

			<?php if ( $mode === 'login' ): ?>
				<h2>Welcome Back</h2>
				<p style="color:#888; margin-bottom:30px;">Access your elite business minds.</p>
				<form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
					<input type="text" name="log" placeholder="Username or Email" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:15px;">
					<input type="password" name="pwd" placeholder="Password" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px;">
					<input type="hidden" name="redirect_to" value="<?php echo home_url('/dashboard/'); ?>">
					<button type="submit" class="amm-primary-btn">Sign In</button>
				</form>
				<p style="margin-top:20px; font-size:13px; color:#888;">Don't have an account? <a href="?mode=register" style="color:#007cba;">Start Free Journey</a></p>
			<?php else: ?>
				<h2>Get Started</h2>
				<p style="color:#888; margin-bottom:30px;">Build your billion-dollar strategy today.</p>
				<form name="registerform" id="registerform" action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post">
					<input type="text" name="user_login" placeholder="Choose Username" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:15px;">
					<input type="email" name="user_email" placeholder="Your Email" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px;">
					<button type="submit" class="amm-primary-btn">Create Account</button>
				</form>
				<p style="margin-top:20px; font-size:13px; color:#888;">Already have an account? <a href="?mode=login" style="color:#007cba;">Sign In</a></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_landing_page() {
		ob_start();
		?>
		<div class="amm-landing" style="font-family:'Inter', sans-serif; max-width:1200px; margin:auto; text-align:center; padding:100px 20px;">
			<span style="background:#e1f5fe; color:#039be5; padding:8px 20px; border-radius:50px; font-weight:bold; font-size:14px; text-transform:uppercase;">The Thinking Engine for Billion-Dollar Brands</span>
			<h1 style="font-size:64px; font-weight:800; margin:30px 0; line-height:1.1;">Access Elite Business Minds <span style="color:#007cba;">On Demand.</span></h1>
			<p style="font-size:20px; color:#666; max-width:800px; margin:0 auto 50px;">Stop using generic AI. Ignite a council of specialized personas trained in the frameworks of the world's most successful entrepreneurs.</p>

			<div style="display:flex; justify-content:center; gap:20px; margin-bottom:100px;">
				<a href="<?php echo wp_registration_url(); ?>" class="amm-primary-btn" style="width:auto; padding:20px 40px; font-size:18px;">Start Free Journey</a>
				<a href="#minds" class="amm-secondary-btn" style="padding:20px 40px; font-size:18px;">Browse the Council</a>
			</div>

			<div id="minds" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:30px; text-align:left;">
				<div class="amm-form-card">
					<div style="font-size:40px; margin-bottom:20px;">🧠</div>
					<h3>The Elite CEO</h3>
					<p>Strategic oversight and high-leverage decision making based on multi-variable constraints.</p>
				</div>
				<div class="amm-form-card">
					<div style="font-size:40px; margin-bottom:20px;">🚀</div>
					<h3>The Growth Hacker</h3>
					<p>Viral loops, acquisition funnels, and rapid-scale marketing experiments.</p>
				</div>
				<div class="amm-form-card">
					<div style="font-size:40px; margin-bottom:20px;">💰</div>
					<h3>$100M Offer Creator</h3>
					<p>Crafting offers so good people feel stupid saying no. Powered by the Hormozi framework.</p>
				</div>
			</div>

			<div style="margin-top:100px; padding:60px; background:#f9f9f9; border-radius:24px;">
				<h2>Built for Scalability. Designed for Revenue.</h2>
				<p>Whether you're a solo founder or a global agency, the AI Multi-Mind Engine scales with you.</p>
				<div style="display:flex; justify-content:center; gap:50px; margin-top:40px;">
					<div><strong>30+</strong><br>Specialized Minds</div>
					<div><strong>100%</strong><br>Secure & Private</div>
					<div><strong>24/7</strong><br>Elite Mentorship</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>Please <a href="' . wp_login_url() . '">login</a> to access the AI Multi-Mind Engine.</p>';
		}

		ob_start();
		?>
		<div id="amm-onboarding-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
			<div class="amm-form-card" style="max-width:600px;">
				<div id="amm-wizard-step-1" class="amm-wizard-step">
					<h2>Welcome to AI Multi-Mind! 🚀</h2>
					<p>Let's set up your Thinking Engine for success. Step 1: Your Brand Profile.</p>
					<input type="text" id="amm-wiz-company" placeholder="Your Business Name" style="width:100%; margin-bottom:10px;">
					<textarea id="amm-wiz-kb" placeholder="Describe what your business does..." style="width:100%; height:100px; margin-bottom:20px;"></textarea>
					<button onclick="wizardNext(2)" class="amm-primary-btn">Next: Define Audience</button>
				</div>
				<div id="amm-wizard-step-2" class="amm-wizard-step" style="display:none;">
					<h2>The Target Persona 🎯</h2>
					<p>Who are we building strategies for? This context improves AI outputs by 10x.</p>
					<input type="text" id="amm-wiz-persona" placeholder="Persona Name (e.g. Agency Owners)" style="width:100%; margin-bottom:10px;">
					<textarea id="amm-wiz-pain" placeholder="What is their #1 pain point?" style="width:100%; height:100px; margin-bottom:20px;"></textarea>
					<button onclick="wizardNext(3)" class="amm-primary-btn">Next: Ready to Ignite</button>
				</div>
				<div id="amm-wizard-step-3" class="amm-wizard-step" style="display:none; text-align:center;">
					<h2>You are Ready! 🧠</h2>
					<p>Your profile is saved. You now have access to the Council of 50+ Minds.</p>
					<div style="font-size:50px; margin:20px 0;">✨</div>
					<button onclick="finishWizard()" class="amm-primary-btn">Enter Dashboard</button>
				</div>
			</div>
		</div>

		<div id="amm-dashboard-root" class="amm-dashboard">
			<aside class="amm-app-sidebar">
				<div class="amm-logo">AI Multi-Mind</div>
				<div id="amm-system-status" style="font-size:10px; margin-bottom:10px; display:flex; gap:10px; color:#aaa;">
					<span title="AI Core">🤖 Core: <span id="amm-status-core">...</span></span>
					<span title="Payment Gateways">💳 Pay: <span id="amm-status-pay">...</span></span>
				</div>
				<div id="amm-user-profile" style="margin-bottom:20px; font-size:12px; color:#888;"></div>
				<nav class="amm-nav">
					<a href="#" class="amm-nav-item" data-tab="dashboard">📊 Dashboard</a>
					<a href="#" class="amm-nav-item active" data-tab="generate" title="Ignite a single elite AI mind for a specific task.">🚀 Ignite Mind</a>
					<a href="#" class="amm-nav-item" data-tab="council" title="Assemble multiple AI minds to collaborate or audit each other.">🏛️ Mind Council</a>
					<a href="#" class="amm-nav-item" data-tab="library" title="Browse and unlock 40+ specialized business personas.">📚 Minds Library</a>
					<a href="#" class="amm-nav-item" data-tab="templates" title="Proven frameworks for common business challenges.">📜 Templates</a>
					<a href="#" class="amm-nav-item" data-tab="workspace" title="Manage, search, and export your generated intelligence.">📁 My Workspace</a>
					<a href="#" class="amm-nav-item" data-tab="team" title="Invite your team and share intelligence outputs.">👥 Team Hub</a>
					<a href="#" class="amm-nav-item" data-tab="persona" title="Define who you are solving problems for.">🎯 Audience</a>
					<a href="#" class="amm-nav-item" data-tab="media" title="Generate high-end visual assets using DALL-E 3.">🎨 Media Engine</a>
					<a href="#" class="amm-nav-item" data-tab="affiliate">💸 Affiliates</a>
					<a href="#" class="amm-nav-item" data-tab="billing">💳 Billing</a>
					<a href="#" class="amm-nav-item" data-tab="settings">⚙️ Settings</a>
				</nav>
				<div class="amm-user-block">
					<div id="amm-user-stats-sidebar">Loading stats...</div>
					<div class="amm-usage-bar-container" style="margin-top:10px; background:#ddd; height:8px; border-radius:4px; overflow:hidden;">
						<div id="amm-usage-bar" style="background:#007cba; height:100%; width:0%; transition:width 0.5s;"></div>
					</div>
					<button id="amm-theme-toggle" class="amm-theme-btn">🌓 Toggle Mode</button>
				</div>
			</aside>

			<div id="amm-edit-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; padding:40px;">
				<div class="amm-form-card" style="width:100%; max-width:800px; height:80%;">
					<h3>Edit Strategy</h3>
					<textarea id="amm-edit-content" style="width:100%; height:calc(100% - 100px); margin-bottom:20px;"></textarea>
					<div style="display:flex; gap:10px;">
						<button id="amm-save-edit-btn" class="amm-primary-btn" style="flex:1;">Save Changes</button>
						<button onclick="document.getElementById('amm-edit-modal').style.display='none'" class="amm-secondary-btn">Cancel</button>
					</div>
				</div>
			</div>

			<main class="amm-app-content">
				<!-- Dashboard Tab -->
				<section id="tab-dashboard" class="amm-tab-content">
					<div style="display:flex; justify-content:space-between; align-items:center;">
						<h2>Elite Insights</h2>
						<button class="amm-primary-btn" style="width:200px;" onclick="document.querySelector('[data-tab=generate]').click();">🚀 Start New Session</button>
					</div>

					<div style="margin-top:30px;">
						<h3>Quick Ignite (Top Minds)</h3>
						<div class="amm-grid-layout" style="grid-template-columns: repeat(3, 1fr);">
							<div class="amm-plan-card" style="cursor:pointer;" onclick="quickIgnite('ceo')"><strong>The Elite CEO</strong></div>
							<div class="amm-plan-card" style="cursor:pointer;" onclick="quickIgnite('magic_bff')"><strong>Magic BFF</strong></div>
							<div class="amm-plan-card" style="cursor:pointer;" onclick="quickIgnite('funnel_builder')"><strong>Funnel Architect</strong></div>
						</div>
					</div>

					<div class="amm-grid-layout" style="margin-top:20px;">
						<div class="amm-form-card">
							<strong>Total Strategies Generated</strong><br>
							<span id="amm-insight-gens" style="font-size:32px; color:#007cba;">0</span>
							<div style="background:#eee; height:10px; margin-top:10px; border-radius:5px;">
								<div id="amm-insight-gens-bar" style="background:#007cba; height:100%; width:0%; border-radius:5px;"></div>
							</div>
						</div>
						<div class="amm-form-card">
							<strong>Referral Network</strong><br>
							<span id="amm-insight-refs" style="font-size:32px; color:#007cba;">0</span>
						</div>
						<div class="amm-form-card" style="grid-column: span 1;">
							<strong>Usage Velocity (7d)</strong>
							<div id="amm-usage-trends" style="display:flex; align-items:flex-end; gap:5px; height:100px; margin-top:10px;">
								<!-- Trends injected here -->
							</div>
						</div>
						<div class="amm-form-card" style="grid-column: span 1; text-align:center;">
							<strong>Credit Allocation</strong>
							<div id="amm-usage-svg" style="margin-top:10px;">
								<!-- SVG Chart -->
							</div>
						</div>
					</div>

					<div class="amm-form-card" style="margin-top:20px;">
						<h3>Recent Activity</h3>
						<div id="amm-recent-activity">Loading recent insights...</div>
					</div>
				</section>

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
							<div id="amm-provider-selection" style="display:none; margin-top:10px;">
								<label>AI Provider (PRO)</label>
								<select id="amm-provider-select">
									<option value="gemini">Google Gemini</option>
									<option value="openai">OpenAI GPT-4</option>
									<option value="claude">Anthropic Claude</option>
								</select>
							</div>
							<label>Output Type</label>
							<select id="amm-type-select">
								<option value="business_plan">Business Plan</option>
								<option value="marketing_plan">Marketing Plan</option>
								<option value="sales_script">Sales Script</option>
								<option value="sop">SOP</option>
								<option value="proposal">Project Proposal</option>
								<option value="report">Business Report</option>
								<option value="blog_post">Blog Post</option>
								<option value="ad_copy">Ad Copy</option>
								<option value="video_script">Video Script</option>
							</select>
							<label>Output Language</label>
							<select id="amm-language-select">
								<option value="English">English</option>
								<option value="Spanish">Spanish</option>
								<option value="French">French</option>
								<option value="German">German</option>
								<option value="Portuguese">Portuguese</option>
								<option value="Italian">Italian</option>
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
							<div style="display:flex; gap:10px; margin-bottom:10px;">
								<button id="amm-generate-btn" class="amm-primary-btn" style="flex:1;">IGNITE ENGINE</button>
								<button id="amm-clear-history-btn" class="amm-secondary-btn" title="Clear Magic BFF Memory">🗑️ Memory</button>
							</div>
					<button id="amm-refine-btn" class="amm-secondary-btn" style="background:#e1f5fe; color:#039be5;">✨ Refine with Magic BFF</button>
						</div>
						<div class="amm-output-container">
							<div class="amm-output-header">
								<h3>Generated Intelligence</h3>
								<div style="display:flex; gap:5px;">
									<button id="amm-export-btn" class="amm-secondary-btn" style="display:none;">📄 PDF</button>
									<button id="amm-export-html-btn" class="amm-secondary-btn" style="display:none;">🌐 HTML</button>
								</div>
							</div>
							<div id="amm-output" class="amm-output-box">The engine is waiting for your request...</div>
						</div>
					</div>
				</section>

				<!-- Library Tab -->
				<section id="tab-library" class="amm-tab-content">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
						<h2>AI Minds Marketplace</h2>
						<div style="display:flex; gap:10px; align-items:center;">
							<input type="text" id="amm-mind-search" placeholder="Search minds..." style="padding:8px; border-radius:8px; border:1px solid #ddd; width:200px;">
							<select id="amm-mind-filter" style="width:150px;">
								<option value="all">All Minds</option>
								<option value="purchased">My Minds</option>
								<option value="premium">Marketplace</option>
							</select>
							<select id="amm-category-filter" style="width:150px;">
								<option value="">All Categories</option>
							</select>
						</div>
					</div>
					<div id="amm-marketplace-grid" class="amm-grid-layout">Loading library...</div>

					<div id="amm-builder-container" style="display:none; margin-top:40px;">
						<h3>Elite Mind & Template Builder (PRO)</h3>
						<div class="amm-form-card" style="margin-bottom:20px;">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
								<strong>Elite Mind Builder 2.0</strong>
								<span id="amm-builder-status" style="font-size:10px; color:#007cba;">Drafting New Mind...</span>
							</div>
							<div style="display:grid; grid-template-columns:1fr 300px; gap:20px;">
								<div>
							<input type="text" id="amm-new-mind-name" placeholder="Mind Name (e.g. Real Estate Guru)" style="width:100%; margin-bottom:10px;">
							<input type="text" id="amm-new-mind-role" placeholder="Role Description (e.g. Expert in ROI and market trends)" style="width:100%; margin-bottom:10px;">
							<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
								<input type="text" id="amm-new-mind-framework" placeholder="Thinking Framework (e.g. SWOT)">
								<input type="text" id="amm-new-mind-style" placeholder="Decision Style (e.g. Cautious)">
							</div>
							<input type="text" id="amm-new-mind-structure" placeholder="Preferred Output Structure (e.g. List, PDF, Table)" style="width:100%; margin-bottom:10px;">
							<textarea id="amm-new-mind-prompt" placeholder="Hidden Prompt Engineering Layer (The 'Brain' of the mind) - Define how it thinks, what it ignores, and how it speaks." style="width:100%; height:150px; margin-bottom:10px;"></textarea>
								</div>
								<div style="background:#f0f8ff; padding:15px; border-radius:12px; font-size:12px; line-height:1.6;">
									<strong>Preview Architecture</strong><hr>
									<div id="amm-mind-preview">
										<em>Fill in the fields to see the mind's profile...</em>
									</div>
								</div>
							</div>
							<button id="amm-create-mind-btn" class="amm-primary-btn">Engrave Mind into Engine</button>
						</div>
						<div class="amm-form-card">
							<strong>Task Template</strong>
							<input type="text" id="amm-new-template-title" placeholder="Template Title (e.g. YouTube Script Pro)">
							<textarea id="amm-new-template-content" placeholder="The actual prompt instructions for this template..."></textarea>
							<button id="amm-create-template-btn" class="amm-secondary-btn">Create Template</button>
						</div>
					</div>
				</section>

				<!-- Templates Tab -->
				<section id="tab-templates" class="amm-tab-content">
					<h2>Elite Task Templates</h2>
					<p>Ready-to-use frameworks for common business challenges.</p>
					<div id="amm-templates-grid" class="amm-grid-layout">Loading templates...</div>
				</section>

				<!-- Workspace Tab -->
				<section id="tab-workspace" class="amm-tab-content">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
						<h2>My Workspace</h2>
						<div style="display:flex; gap:10px;">
							<select id="amm-workspace-filter" style="width:150px;">
								<option value="all">All Items</option>
								<option value="public">Shared Intel</option>
								<option value="personal">My Generations</option>
								<option value="team">Team Shared</option>
							</select>
							<select id="amm-workspace-folder-filter" style="width:150px; border-radius:8px;">
								<option value="">All Folders</option>
							</select>
							<select id="amm-workspace-sort" style="width:150px; border-radius:8px;">
								<option value="newest">Newest First</option>
								<option value="oldest">Oldest First</option>
								<option value="title">By Title</option>
							</select>
							<input type="text" id="amm-workspace-search" placeholder="Search strategy..." style="padding:8px; border-radius:8px; border:1px solid #ddd; width:200px;">
							<button id="amm-export-blueprint-btn" class="amm-secondary-btn" style="background:#007cba; color:#fff;">💎 Export Blueprint</button>
							<button id="amm-export-workspace-btn" class="amm-secondary-btn" style="background:#28a745; color:#fff;">📦 Export JSON</button>
							<button id="amm-bulk-delete-btn" class="amm-secondary-btn" style="background:#ff4444; color:#fff;">Delete Selected</button>
							<button id="amm-bulk-move-btn" class="amm-secondary-btn" style="background:#007cba; color:#fff;">Move to Folder</button>
							<button id="amm-new-folder-btn" class="amm-secondary-btn">+ New Folder</button>
						</div>
					</div>
					<div id="amm-workspace-list" class="amm-form-card">Loading saved outputs...</div>
				</section>

				<!-- Team Tab -->
				<section id="tab-team" class="amm-tab-content">
					<div style="display:flex; justify-content:space-between; align-items:center;">
						<h2>Team Collaboration Hub</h2>
						<button id="amm-create-team-btn" class="amm-secondary-btn" style="display:none;">+ Create New Team</button>
					</div>
					<div id="amm-team-controls" style="display:none;">
						<div class="amm-form-card" style="margin-bottom:20px;">
							<h3>Invite Team Member</h3>
							<input type="email" id="amm-invite-email" placeholder="email@example.com" style="width:70%;">
							<button id="amm-invite-btn" class="amm-primary-btn" style="width:25%;">Invite</button>
						</div>
					</div>

					<div id="amm-branding-container" class="amm-form-card" style="margin-bottom:20px; display:none;">
						<h3>Agency White-Labeling</h3>
						<label>Custom Logo URL</label>
						<input type="text" id="amm-branding-logo" style="width:100%; margin-bottom:10px;">
						<label>Primary Brand Color</label>
						<input type="color" id="amm-branding-color" style="width:100%; margin-bottom:10px;">
						<button id="amm-branding-btn" class="amm-secondary-btn">Apply Branding</button>
					</div>

					<div id="amm-team-list" class="amm-form-card" style="margin-bottom:20px;">No team members found.</div>

					<div id="amm-team-activity-container" style="margin-bottom:20px;">
						<h3>Team Activity Feed</h3>
						<div id="amm-team-activity" class="amm-form-card">Loading team activity...</div>
					</div>

					<h3>Pending Invitations</h3>
					<div id="amm-invite-list" class="amm-form-card">No pending invites.</div>
				</section>

				<!-- Media Tab -->
				<section id="tab-media" class="amm-tab-content">
					<h2>AI Media Engine (DALL-E 3)</h2>
					<p>Generate high-end visual assets for your business strategies. (Cost: 5 Credits per image)</p>
					<div class="amm-form-card">
						<textarea id="amm-image-prompt" placeholder="Describe the image you want to generate (e.g. A futuristic luxury office with a view of Mars)..." style="width:100%; height:80px; margin-bottom:10px;"></textarea>
						<button id="amm-generate-image-btn" class="amm-primary-btn">Generate Visual Asset</button>
					</div>
					<div id="amm-image-output" style="margin-top:20px; text-align:center;"></div>
				</section>

				<!-- Affiliate Tab -->
				<section id="tab-affiliate" class="amm-tab-content">
					<h2>Affiliate Program</h2>
					<div id="amm-affiliate-info" class="amm-form-card">Loading your data...</div>
				</section>

				<!-- Persona Tab -->
				<section id="tab-persona" class="amm-tab-content">
					<h2>Target Audience & Persona Builder</h2>
					<p>Define who your AI Minds are solving problems for. This data will be injected into every generation.</p>
					<div class="amm-form-card">
						<label>Persona Name (e.g. Busy E-com Owner)</label>
						<input type="text" id="amm-persona-name" style="width:100%; margin-bottom:10px;">
						<label>Pain Points & Roadblocks</label>
						<textarea id="amm-persona-pain" style="width:100%; height:80px; margin-bottom:10px;" placeholder="What keeps them up at night?"></textarea>
						<label>Core Desires & Dreams</label>
						<textarea id="amm-persona-desire" style="width:100%; height:80px; margin-bottom:10px;" placeholder="What do they secretly want?"></textarea>
						<label>Buying Triggers</label>
						<input type="text" id="amm-persona-triggers" style="width:100%; margin-bottom:10px;" placeholder="Events that lead to a purchase">
						<button id="amm-save-persona-btn" class="amm-primary-btn">Save Audience Context</button>
					</div>
				</section>

				<!-- Council Tab -->
				<section id="tab-council" class="amm-tab-content">
					<h2>Mind Council & Critique</h2>
					<p>Select multiple minds to brainstorm or use "Critique Mode" to have one mind audit another.</p>
					<div class="amm-form-card" style="margin-bottom:20px;">
						<div style="margin-bottom:15px;">
							<label><input type="radio" name="council-mode" value="sequence" checked> <strong>Sequence Mode</strong> (Mind 1 -> Mind 2 -> Mind 3)</label><br>
							<label><input type="radio" name="council-mode" value="critique"> <strong>Critique Mode</strong> (Mind 1 Creates -> Mind 2 Audits -> Mind 1 Finalizes)</label><br>
							<label><input type="radio" name="council-mode" value="brainstorm"> <strong>Brainstorm Mode</strong> (Parallel outputs from all selected minds)</label>
						</div>
						<div id="amm-council-selectors" style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
							<div id="amm-council-provider-container" style="display:none;">
								<label>Council Provider (PRO)</label>
								<select id="amm-council-provider-select" style="margin-bottom:10px;">
									<option value="gemini">Google Gemini</option>
									<option value="openai">OpenAI GPT-4</option>
									<option value="claude">Anthropic Claude</option>
								</select>
							</div>
							<select class="amm-council-select"><option value="">Select Mind 1...</option></select>
							<select class="amm-council-select"><option value="">Select Mind 2...</option></select>
							<select class="amm-council-select"><option value="">Select Mind 3...</option></select>
						</div>
						<textarea id="amm-council-input" placeholder="What should the council build for you?" style="width:100%; height:100px; margin-bottom:10px;"></textarea>
						<div style="display:flex; gap:10px;">
							<button id="amm-ignite-council-btn" class="amm-primary-btn" style="flex:1;">Ignite Council</button>
							<button id="amm-save-preset-btn" class="amm-secondary-btn">💾 Save Preset</button>
						</div>
					</div>
					<div id="amm-presets-container" style="margin-top:20px; display:none;">
						<h3>Your Saved Presets</h3>
						<div id="amm-presets-list" style="display:flex; gap:10px; flex-wrap:wrap;"></div>
					</div>
					<div id="amm-council-output" class="amm-output-box">The council is waiting to be summoned...</div>
				</section>

				<!-- Settings Tab -->
				<section id="tab-settings" class="amm-tab-content">
					<h2>User Settings</h2>
					<div class="amm-form-card">
						<label>External Webhook URL (Zapier/Make)</label>
						<input type="text" id="amm-set-webhook" placeholder="https://hooks.zapier.com/..." style="width:100%; margin-bottom:20px;">
						<label>Company/Business Name</label>
						<input type="text" id="amm-set-company" style="width:100%; margin-bottom:20px;">
						<label>Default AI Mind</label>
						<select id="amm-set-default-mind" style="width:100%; margin-bottom:20px;"></select>
						<label>Knowledge Base (Your Company Context)</label>
						<textarea id="amm-set-kb" placeholder="About my business, products, target audience..." style="width:100%; height:150px; margin-bottom:20px;"></textarea>
						<label>Context Pro: Knowledge Files (TXT/JSON/CSV)</label>
						<div id="amm-kb-files" style="margin-bottom:10px;"></div>
						<input type="file" id="amm-kb-upload" style="margin-bottom:20px;">
						<p><input type="checkbox" id="amm-set-alerts"> Enable 90% Credit Usage Email Alerts</p>
						<button id="amm-save-settings-btn" class="amm-primary-btn">Save Preferences</button>
					</div>
				</section>

				<!-- Billing Tab -->
				<section id="tab-billing" class="amm-tab-content">
					<h2>Plans & Subscription</h2>
					<div id="amm-plans-grid" class="amm-billing-grid">
						Loading plans...
					</div>

						<div id="amm-portal-container" style="display:none; margin-top:20px; text-align:center; display:flex; gap:10px; justify-content:center;">
							<button onclick="ammPortal()" class="amm-secondary-btn" style="width:200px;">Manage Billing & Invoices</button>
							<button id="amm-sync-sub-btn" class="amm-secondary-btn" style="width:200px; background:#f9f9f9;">🔄 Sync Subscription</button>
						</div>
						<div class="amm-form-card" style="margin-top:20px; text-align:center;">
							<h3>Need more credits?</h3>
							<p>Buy 50 extra credits for just $10.</p>
							<button onclick="ammCheckout('topup_50')" class="amm-secondary-btn" style="width:200px;">Buy Top-up</button>
						</div>

						<div style="margin-top:40px;">
							<h3>Your Credit Usage History</h3>
							<div id="amm-usage-log" class="amm-form-card" style="margin-bottom:20px;">Loading usage...</div>

							<h3>Your Invoices & Subscriptions</h3>
							<div id="amm-billing-history" class="amm-form-card">Loading history...</div>
						</div>
				</section>
			</main>
		</div>

			<div id="amm-support-widget" style="position:fixed; bottom:20px; right:20px; z-index:9999;">
				<button id="amm-support-toggle" style="width:60px; height:60px; border-radius:30px; background:#007cba; color:#fff; border:none; box-shadow:0 10px 20px rgba(0,0,0,0.2); cursor:pointer; font-size:24px;">💬</button>
				<div id="amm-support-chat" style="display:none; position:absolute; bottom:70px; right:0; width:300px; height:400px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); border:1px solid #eee; flex-direction:column; overflow:hidden;">
					<div style="background:#007cba; color:#fff; padding:15px; font-weight:bold;">Success Coach 🚀</div>
					<div id="amm-support-messages" style="flex:1; padding:15px; overflow-y:auto; font-size:13px; line-height:1.4;">
						<div style="background:#f0f0f0; padding:10px; border-radius:8px; margin-bottom:10px;">Hello! I am your Success Coach. How can I help you ignite your business strategy today?</div>
					</div>
					<div style="padding:15px; border-top:1px solid #eee; display:flex; gap:5px;">
						<input type="text" id="amm-support-input" placeholder="Type your question..." style="flex:1; padding:8px; border-radius:6px; border:1px solid #ddd;">
						<button id="amm-support-send" style="background:#007cba; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Send</button>
					</div>
				</div>
			</div>

		<?php
		return ob_get_clean();
	}
}
