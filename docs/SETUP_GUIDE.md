# AI Multi-Mind SaaS Engine - Setup & Developer Guide

## 🚀 Quick Start Setup

1. **Install the Plugin**: Upload the `ai-multi-mind-engine` folder to your `/wp-content/plugins/` directory and activate it.
2. **Configure API Keys**: Navigate to `AI SaaS Settings` in the WP Admin.
    - Input your **Gemini**, **OpenAI**, and/or **Claude** API keys.
    - Set your default provider.
3. **Setup Billing (Stripe)**:
    - Input your Stripe Secret Key and Webhook Secret.
    - Create your products/prices in the Stripe Dashboard and copy the Price IDs into the plugin settings.
    - Point your Stripe Webhook to `https://yourdomain.com/wp-json/amm/v1/stripe-webhook` (ensure you've updated the REST API logic or created a dedicated endpoint).
4. **Deploy the Dashboard**: Create a new page in WordPress and add the `[amm_dashboard]` shortcode.
5. **Add AI Minds**: Use the `AI Minds` menu in the WP sidebar to add custom personas using the Meta Box configuration.

## 🛠 Developer Info

### Custom Tables
The plugin creates 6 custom tables upon activation for subscriptions, usage, teams, and affiliates. See `docs/DATABASE.md` for details.

### REST API Namespace
All endpoints are under `wp-json/amm/v1/`.
- `POST /generate`: The core engine endpoint.
- `GET /user`: Fetches current plan and usage stats.
- `GET /minds`: Fetches the dynamic library of minds.

### Adding New Core Minds
To add a permanent core mind, create a new class in `includes/minds/` extending `AMM_Mind_Base` and register it in `includes/class-prompt-engine.php` and `includes/class-rest-api.php`.

### Scaling to Headless
The architecture is 100% decoupled. You can build a React/Next.js frontend that communicates exclusively via the REST API, using the built-in Nonce or JWT for authentication.
