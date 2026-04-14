# SaaS Deployer's Master Checklist 🚀

Follow this guide to move from "Activated Plugin" to a "Million-Dollar SaaS Launch."

## 1. Technical Hardening
- [ ] **Encryption Check**: Ensure `AUTH_SALT` is set in your `wp-config.php`. All API keys are encrypted using this salt.
- [ ] **SSL Required**: Ensure your site is running on HTTPS. Stripe and PayPal webhooks require secure endpoints.
- [ ] **Logging**: Enable `amm_api_logging` in Settings for the first 48 hours of launch to monitor for provider errors.

## 2. Payment Integration
- [ ] **Stripe Production**: Swap test keys for Live Secret Keys. Create your 3 Price IDs (Starter, Pro, Agency).
- [ ] **PayPal Production**: Ensure your PayPal App is set to "Live" and you have entered the Webhook ID.
- [ ] **Webhook Verification**: Perform one real $1 transaction on both gateways to verify the `amm_plan_id` updates correctly.

## 3. The "Elite Minds" Setup
- [ ] **Default Mind**: Set your platform's default mind (e.g., CEO Mind).
- [ ] **Global Context**: Add your site's unique rules to "Global System Context." (e.g., "Always maintain a premium, professional tone.")

## 4. Affiliate Growth
- [ ] **Test Referral**: Create a test affiliate account. Click the referral link and register a new user. Verify the referral is logged in the Admin Affiliate table.
- [ ] **Payout Terms**: Define your payout schedule (e.g., Net-30) in your terms of service.

## 5. Marketing & Launch
- [ ] **Landing Page**: Customize the included `landing-page.html` and set it as your WordPress home page.
- [ ] **Auth Integration**: Use the `[amm_auth]` shortcode for a dedicated login/register page.
- [ ] **30-Day Calendar**: Start the Social Media calendar (found in `/marketing/`) to build authority before the hard launch.

## 6. Post-Launch Monitoring
- [ ] **Revenue Insights**: Monitor MRR and Churn daily in the SaaS Admin Dashboard.
- [ ] **Success Coach Logs**: Periodically check the `audit_trail` to see what questions users are asking the Success Coach—this is your best source for new feature ideas.
