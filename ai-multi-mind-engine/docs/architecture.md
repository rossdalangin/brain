# SaaS Architecture & Database Schema

## System Architecture
The AI Multi-Mind SaaS Engine is built on a "Service-Oriented" WordPress architecture. It treats WordPress as an application framework rather than just a CMS.

### Key Layers
1. **Presentation Layer**: Custom shortcode-driven dashboard with modern CSS/JS assets.
2. **API Layer**: `amm/v1` REST namespace handling all asynchronous operations.
3. **Orchestration Layer**: `AMM_AI_Provider_Manager` and `AMM_Prompt_Engine` for LLM multi-tenancy.
4. **Data Layer**: Custom SQL tables for high-frequency transactional data (billing, usage) to avoid `wp_options` or `wp_postmeta` bloat.

## Database Schema (Custom Tables)

### wp_amm_subscriptions
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | WP User ID |
| plan_id | VARCHAR | starter, pro, agency |
| gateway | VARCHAR | stripe, paypal |
| subscription_id | VARCHAR | External ID |
| status | VARCHAR | active, cancelled, expired |
| current_period_end | DATETIME | Expiry check |

### wp_amm_usage
| Column | Type | Description |
|--------|------|-------------|
| user_id | BIGINT | Primary Key (Composite) |
| month | VARCHAR | YYYY-MM |
| credits_used | INT | Metering count |

### wp_amm_teams
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary Key |
| owner_id | BIGINT | Agency owner |
| team_name | VARCHAR | Display name |
| custom_logo | TEXT | White-label URL |

### wp_amm_affiliates
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | Affiliate User |
| affiliate_code | VARCHAR | Unique Ref Code |
| total_commissions | DECIMAL| Accumulated earnings |
