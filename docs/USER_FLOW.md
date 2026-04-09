# AI Multi-Mind SaaS Engine - User Flows

## 1. Onboarding Flow
1. User lands on landing page.
2. Clicks "Start for Free".
3. Redirected to WP Registration/Login.
4. After login, redirected to `/dashboard/`.
5. Shown a 3-step "Tour" of how to select a Mind and generate their first output.

## 2. AI Generation Flow
1. User navigates to "Generate".
2. Selects "Elite CEO" Mind.
3. Selects "Business Plan" as Output Type.
4. Enters "Scaling a boutique coffee chain to 10 locations" in the request box.
5. Clicks "Ignite Mind".
6. System checks:
    - User is logged in?
    - User has active subscription?
    - User has remaining credits?
7. REST API called: `POST /amm/v1/generate`.
8. Provider Manager calls Gemini/OpenAI with compiled prompts.
9. Output returned to UI.
10. Usage Tracker increments `credits_used`.
11. User clicks "Save to Workspace".

## 3. Subscription Upgrade Flow
1. User hits credit limit or clicks "Upgrade".
2. User selects "Pro Plan".
3. Stripe Handler creates Checkout Session.
4. User redirected to Stripe.
5. Successful payment.
6. Stripe Webhook sends `checkout.session.completed`.
7. `wp_amm_subscriptions` table updated.
8. User redirected back to Dashboard with "Success" notification.
9. Premium Minds unlocked instantly.

## 4. Team Management (Agency Flow)
1. Agency Owner goes to "Settings > Team".
2. Enters email of team member.
3. Invites sent via email.
4. Team member accepts and joins the shared workspace.
5. Team member can see/edit outputs created by the Owner (if permissions allow).
