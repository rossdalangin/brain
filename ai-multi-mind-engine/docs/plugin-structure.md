# AI Multi-Mind Engine: Plugin Structure & File Manifest

This document provides a technical overview of the plugin's file structure and the purpose of each mission-critical component.

## 📁 Root Directory
- `ai-multi-mind-engine.php`: Main plugin entry point. Handles class loading, hook initialization, and database table creation upon activation.
- `README.md`: High-level overview and installation guide.

## 📁 assets/
- `css/amm-dashboard.css`: Premium SPA-style styles for the user dashboard.
- `js/amm-dashboard.js`: Orchestrates the frontend logic (REST calls, State management, Typewriter UX).

## 📁 includes/
- `class-rest-api.php`: The "Headless" engine. Registers all `amm/v1` endpoints for AI generation, payments, and team management.
- `class-shortcodes.php`: Defines the `[amm_dashboard]` and `[amm_landing_page]` shortcodes.
- `class-prompt-engine.php`: The "Brain." Prepares system and user prompts based on selected Minds and Task Templates.
- `class-ai-provider-manager.php`: Orchestrates API calls to OpenAI, Gemini, and Claude with AES-256 encryption.
- `class-usage-tracker.php`: Manages monthly credit limits, resets, and threshold email alerts.
- `class-team-manager.php`: Handles team creation, role-based permissions, and invitations.
- `class-affiliate-manager.php`: Manages referral tracking, cookie logic, and commission logging.
- `class-analytics-manager.php`: Aggregates SaaS-level metrics (Usage, Revenue, Popularity).
- `class-admin-settings.php`: The Admin Control Center UI.
- `class-admin-meta-boxes.php`: Meta-data configuration for AI Minds and Templates.
- `class-logger.php`: Debug logging for API requests and responses.
- `class-webhook-manager.php`: Pushes generated intelligence to external Zapier/Make webhooks.

## 📁 includes/minds/
- `class-mind-base.php`: Abstract foundation for all AI personas.
- `class-mind-batch-1..5.php`: Hardcoded elite core minds based on business frameworks.
- `class-mind-dynamic.php`: Enables user-created minds via Custom Post Types.

## 📁 includes/payments/
- `class-stripe-handler.php`: Stripe Checkout and Webhook processing with manual signature verification.
- `class-paypal-handler.php`: PayPal REST API integration for subscriptions and orders.

## 📁 tests/
- `verify-logic.php`: Logic verification suite for CLI-based testing.
