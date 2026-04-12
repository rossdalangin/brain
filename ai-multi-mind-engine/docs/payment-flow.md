# Payment Integration Flow

The AI Multi-Mind SaaS Engine uses a robust, secure flow for both Stripe and PayPal integrations.

## 1. Stripe Subscription Flow
1. **Initiation**: User selects a plan on the Dashboard.
2. **Checkout**: `AMM_REST_API` calls `AMM_Stripe_Handler::create_checkout_session`.
3. **Redirection**: Stripe hosts the checkout page.
4. **Fulfillment**: On success, Stripe sends a `checkout.session.completed` webhook.
5. **Security**: `AMM_Stripe_Handler::verify_signature` manually validates the webhook using the `webhook_secret`.
6. **Provisioning**: User's `amm_plan_id` is updated, and the subscription is logged in `wp_amm_subscriptions`.

## 2. PayPal Subscription Flow
1. **Auth**: `AMM_PayPal_Handler` gets an OAuth2 token using Client ID/Secret.
2. **Subscription Create**: Calls `/v1/billing/subscriptions` to create a session.
3. **Approval**: User is redirected to PayPal to approve the recurring payment.
4. **Webhook**: PayPal sends `BILLING.SUBSCRIPTION.ACTIVATED`.
5. **Verification**: The engine calls PayPal's verification endpoint to ensure the webhook is authentic.

## 3. One-Time Purchases (Marketplace)
- **Mind/Template Unlock**: Uses Stripe 'Payment' mode (one-time).
- **Metadata**: We pass `mind_id` or `template_id` in Stripe metadata to track what was purchased.
- **Access**: After webhook confirmation, the purchase is logged in `wp_amm_purchases`.

## 4. Dunning & Cancellation
- **Stripe Portal**: Users can manage their own cancellations via the hosted Billing Portal.
- **Webhook Events**: We listen for `customer.subscription.deleted` to revoke access instantly.
