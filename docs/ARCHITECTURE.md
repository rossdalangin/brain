# AI Multi-Mind SaaS Engine - Architecture

## High-Level Overview
The AI Multi-Mind SaaS Engine is a production-ready SaaS platform built on top of WordPress. It leverages WordPress's robust user management and content handling while adding a sophisticated AI orchestration layer and a custom subscription billing engine.

## Core Components

### 1. WordPress Layer
- **User Management**: Standard WP Users with custom roles (Free, Starter, Pro, Agency).
- **Custom Post Types (CPT)**:
    - `ai_minds`: Stores AI Mind configurations (Role, Thinking Framework, Hidden Prompts).
    - `ai_outputs`: Stores generated outputs, linked to users and organized by folders (taxonomies).
- **REST API**: Custom endpoints for frontend interactions, making the system "Headless Ready".

### 2. AI Multi-Mind System
- **Mind Controller**: Orchestrates which "Mind" is active.
- **Provider Manager**: Abstracts AI API calls (Gemini, OpenAI, Claude).
- **Prompt Engine**: Injects hidden prompt engineering, decision styles, and output structures into the user's request.

### 3. Subscription & Billing Engine
- **Stripe & PayPal Handlers**: Specialized classes for payment processing and webhook handling.
- **Access Manager**: Enforces feature and usage limits based on the user's active subscription plan.

### 4. Storage & Collaboration
- **Workspace Manager**: Handles saving, editing, and organizing AI outputs.
- **Team Manager**: Custom tables and logic for shared workspaces in Agency plans.

## Scalability Path
1. **Phase 1 (Current)**: WordPress-centric plugin with a modern dashboard UI within WP.
2. **Phase 2 (Headless)**: Decoupled React/Next.js frontend communicating via WP REST API.
3. **Phase 3 (Mobile)**: React Native app leveraging the same REST API.

## Security
- API Key Encryption: AI provider keys are encrypted in the database.
- Webhook Verification: Strict signature checking for Stripe and PayPal webhooks.
- Capability Checks: Every REST API request is validated against user roles and active subscription status.
