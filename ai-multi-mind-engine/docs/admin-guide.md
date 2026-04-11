# Admin Management Guide

## Overview
The Admin Area (**AI SaaS Settings**) is the nerve center of the platform. Only users with `manage_options` capability can access this area.

## 1. API Configuration
- **Providers**: You can toggle between Gemini, OpenAI, and Claude.
- **Security**: All keys are encrypted using `AUTH_SALT`. If you change your site's salts, you MUST re-enter the keys.
- **Fallback**: "Free Jules" is a mock engine used for testing or as a fallback if keys are missing.

## 2. Stripe & PayPal Setup
- **Price IDs**: You must create corresponding Products/Prices in your Stripe Dashboard and enter the IDs here.
- **Webhooks**:
  - Point Stripe webhooks to `https://your-site.com/wp-json/amm/v1/stripe-webhook`.
  - Point PayPal webhooks to `https://your-site.com/wp-json/amm/v1/paypal-webhook`.
- **Secrets**: Ensure Webhook Secrets are entered to enable payment verification.

## 3. Usage & Credit Management
- **Manual Resets**: You can reset a user's monthly credits from the user table in settings.
- **Global Limits**: Plan limits are defined in `AMM_Usage_Tracker` but can be monitored here.

## 4. AI Mind Management
- **Custom Minds**: Use the **AI Minds** menu to create new personas.
- **Featured Minds**: Mark a mind as "Featured" via the Meta Box to highlight it on the dashboard.
- **Categories**: Organize minds into categories (Marketing, Executive, etc.) for easy filtering.

## 5. Affiliate System
- **Commission Rates**: Set globally or per-user (Custom Development recommended for complex logic).
- **Payouts**: Current version logs commissions; manual payouts are required after verifying referrals.

## 6. System Health
- **API Test**: Use the "Test" button to verify connectivity with AI providers.
- **Logs**: If enabled, check `api-debug.log` in the plugin folder for detailed request/response data.
