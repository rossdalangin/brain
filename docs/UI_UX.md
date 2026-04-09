# AI Multi-Mind SaaS Engine - UI/UX Design

## Design Philosophy
- **Premium & Minimalist**: Dark mode by default with gold/blue accents to signify elite status.
- **Efficiency First**: Large input areas, instant "Mind" selection, and split-screen generation view.

## Dashboard Pages

### 1. Main Dashboard (Overview)
- **Top Bar**: Credit usage meter (e.g., "15/50 credits used"), Upgrade button.
- **Main View**: Grid of "Quick Start" AI Minds (CEO, Strategist, Funnel Builder).
- **Recent Activity**: List of last 5 generated outputs.

### 2. Generate Page (The Engine)
- **Left Sidebar**:
    - Mind Selection (Dropdown or Icons).
    - Output Type Selection (Business Plan, SOP, etc.).
- **Center Panel**:
    - Large Textarea for "Your Request / Context".
    - "Ignite Mind" Button.
- **Right Panel (Output)**:
    - Real-time streaming text area (simulated or real).
    - Copy, Save to Workspace, and Export (PDF/Doc) buttons.

### 3. AI Minds Library
- Card-based layout showing all available Minds.
- Filter by category (Executive, Marketing, Sales, etc.).
- "Locked" indicator for Minds not in the user's current plan.

### 4. My Outputs (Workspace)
- File explorer style view.
- Folders on the left, list of saved outputs on the right.
- Search bar and bulk actions (Move, Delete).

### 5. Billing & Subscription
- Current Plan Card: Shows status, next billing date, and active payment method.
- Pricing Table: Horizontal cards showing Starter, Pro, and Agency tiers with "Switch Plan" buttons.
- Invoice History: Simple table with Download PDF links.

## Design Components (Text-based Wireframe)
```
+-------------------------------------------------------------+
| [LOGO] AI Multi-Mind      [Credits: 42/50] [User Avatar]    |
+-------------------------------------------------------------+
| [ Dashboard ] |                                             |
| [ Generate  ] |  SELECT YOUR MIND:                          |
| [ Library   ] |  +-------+ +------------+ +---------------+ |
| [ Workspace ] |  | CEO   | | Strategist | | Funnel Builder| |
| [ Billing   ] |  +-------+ +------------+ +---------------+ |
| [ Settings  ] |                                             |
|               |  WHAT ARE WE BUILDING TODAY?                |
|               |  [_______________________________________]  |
|               |  [_______________________________________]  |
|               |                                             |
|               |  [ IGNITE MIND ]                            |
+-------------------------------------------------------------+
```
