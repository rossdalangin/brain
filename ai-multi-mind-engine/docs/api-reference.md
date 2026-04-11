# API Reference (REST amm/v1)

The AI Multi-Mind SaaS Engine is built on a headless-ready REST API. All endpoints require a valid WP Nonce (`X-WP-Nonce`) or Authentication header.

## 1. Generation
- **Endpoint**: `POST /generate`
- **Body**:
  - `mind_id` (string): e.g., 'ceo', 'funnel_builder'
  - `output_type` (string): 'business_plan', 'sop', etc.
  - `user_input` (string): The context/request.
  - `language` (string): 'English', 'Spanish', etc.
- **Returns**: JSON with `content` and updated `usage`.

## 2. Minds Library
- **Endpoint**: `GET /minds`
- **Returns**: Array of all available AI Minds (Core + Custom).

## 3. Workspace (Outputs)
- **Endpoint**: `GET /outputs`
- **Returns**: User's saved generation history.
- **Endpoint**: `POST /duplicate` (body: `post_id`)
- **Endpoint**: `POST /share` (body: `post_id`)

## 4. Billing & Checkout
- **Endpoint**: `POST /checkout`
- **Body**: `plan_id`, `gateway` (stripe/paypal)
- **Returns**: Redirect URL to payment gateway.
- **Endpoint**: `GET /billing-portal` (Stripe only)

## 5. Team Management
- **Endpoint**: `GET /teams`
- **Endpoint**: `POST /invite` (body: `email`, `team_id`)
- **Endpoint**: `POST /update-branding` (body: `logo`, `color`)

## 6. Growth & Affiliates
- **Endpoint**: `GET /affiliate`
- **Returns**: Referral code, links, and commission stats.

## 7. Public (No Auth)
- **Endpoint**: `GET /public-output?id={ID}`
- **Returns**: Content of a shared intelligence piece.
