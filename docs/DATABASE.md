# AI Multi-Mind SaaS Engine - Database Schema

The system uses both standard WordPress tables and custom tables for SaaS-specific functionality.

## Custom Tables

### 1. `wp_amm_subscriptions`
Stores active subscription details for users.
- `id`: BIGINT(20) PRIMARY KEY
- `user_id`: BIGINT(20) (FK to wp_users)
- `plan_id`: VARCHAR(50) (free, starter, pro, agency)
- `gateway`: VARCHAR(20) (stripe, paypal)
- `subscription_id`: VARCHAR(100) (Gateway-specific ID)
- `status`: VARCHAR(20) (active, cancelled, expired)
- `current_period_end`: DATETIME
- `created_at`: DATETIME

### 2. `wp_amm_usage`
Tracks AI generation usage for metering.
- `id`: BIGINT(20) PRIMARY KEY
- `user_id`: BIGINT(20) (FK to wp_users)
- `month`: VARCHAR(7) (YYYY-MM)
- `credits_used`: INT(11)
- `last_reset`: DATETIME

### 3. `wp_amm_teams`
Manages team structures for Agency plans.
- `id`: BIGINT(20) PRIMARY KEY
- `owner_id`: BIGINT(20) (FK to wp_users)
- `team_name`: VARCHAR(255)
- `created_at`: DATETIME

### 4. `wp_amm_team_members`
Links users to teams with roles.
- `id`: BIGINT(20) PRIMARY KEY
- `team_id`: BIGINT(20) (FK to wp_amm_teams)
- `user_id`: BIGINT(20) (FK to wp_users)
- `role`: VARCHAR(20) (admin, member)

## WordPress Table Usage
- **`wp_posts`**: Used for CPT `ai_minds` and `ai_outputs`.
- **`wp_postmeta`**: Stores mind configuration (prompts, style) and output metadata.
- **`wp_usermeta`**: Stores user-specific settings and cached subscription status.
