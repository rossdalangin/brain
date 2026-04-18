# 🚀 AI Multi-Mind SaaS Engine - Command Center Deployment Guide

## 🛠️ The 5-Minute "Money-Printing" Setup

Follow these steps to deploy your AI empire. This isn't just a plugin; it's the core of your new SaaS business.

### 1. Activating the Intelligence Core
1.  Upload the `ai-multi-mind-engine` folder to `/wp-content/plugins/`.
2.  Activate it. Upon activation, the engine will automatically forge 11 high-performance SQL tables—your system's "Digital Vault."

### 2. Plugging in the Brains (API Keys)
Navigate to **AI SaaS Settings** in the Admin Sidebar.
-   **AES-256 Encryption**: Your keys are secured.
-   **Multi-Provider Strategy**: Input keys for Gemini (Default), OpenAI, or Claude.
-   *Pro Tip*: Always keep at least two providers active for maximum reliability and uptime.

### 3. Setting Up the Toll Booth (Payments)
Go to the **Fintech Tab** in Settings.
-   **Stripe**: Input your keys and Webhook Secret.
-   **PayPal**: Add your Client ID for secondary redundancy.
-   **The Pricing Table**: Copy your Stripe Price IDs into our plan mapper. This connects your dashboard buttons directly to recurring revenue.

### 4. Deploying the Command Dashboard
Create a new WordPress Page (e.g., `/dashboard/`).
-   Add the shortcode: `[amm_dashboard]`
-   *The Result*: A modern, dark-mode, high-conversion workspace where your customers will spend hours building their businesses.

### 5. Final Launch Check
-   [ ] Are Webhooks active? (Critical for instant access)
-   [ ] Is the "Mind Council" logic firing?
-   [ ] Are credit limits enforced?

## 🧑‍💻 Developer's Strategic Notes

### The REST-First Engine
The entire dashboard communicates via the `wp-json/amm/v1/` namespace. This means you can swap the WordPress frontend for a custom React/Next.js app without touching a single line of backend logic.

### Extending the Council
Want to add a new "Mind"? Don't just edit files. Use the **Mind Factory** (CPT) to define new personas with custom thinking frameworks. Your business is as flexible as your imagination.

### High-Performance Logs
Check `wp-content/uploads/amm-logs/` for the **Strategic Audit Trail**. This logs every AI call, error, and credit transaction for deep-dive optimization.
