# local_automator — Automator Plugin for Moodle LMS

Rule-based automation for DSL's Moodle LMS platform. Replaces Moodle Workplace Dynamic Rules. Supports notifications (initial release) with an extensible architecture for future action types.

## Requirements

- Moodle LMS 4.5+ (requires `2024100700`)
- MuTMS (`tool_mutenancy`)
- `local_dsl_tiers` (tier gating)
- Optional: `local_externalcpr` (CPR certification integration)

## Installation

1. Copy the `local_automator` directory into `<moodleroot>/local/`
2. Run the CLI upgrade: `sudo -u www-data php admin/cli/upgrade.php --non-interactive`
3. Or visit `Site Administration → Notifications` to trigger the web installer

## Post-Deploy Checklist

### 1. Register the feature in `local_dsl_tiers`

Navigate to **Site Administration → DSL Tiers → Features** (`/local/dsl_tiers/admin/features.php`):

1. Select `local_automator` from the installed-plugins dropdown
2. Set the required subscription tier
3. Save

> Until registered, `local_automator` is accessible to all tenants (unregistered components default to Basic).

### 2. Verify scheduled task

In **Site Administration → Server → Scheduled tasks**, confirm `local_automator\task\process_rules` is enabled and running every 15 minutes.

### 3. Verify event observers

In plugin settings (`/admin/settings.php?section=local_automator`), confirm **Enable event-driven triggers** is checked.

### 4. Plugin settings

| Setting | Default | Description |
|---------|---------|-------------|
| Enable event-driven triggers | On | Rules evaluated immediately on user/course events |
| Enable scheduled task | On | Safety-net + time-based conditions (CPR expiry etc.) |
| Batch size | 100 | Users processed per scheduled task run |
| Cache duration | 3600s | Condition result cache TTL |
| Default recheck interval | 86400s | Minimum seconds between rule executions per user |

## Usage

- Site admins: **Site Administration → Local plugins → Automator → Manage rules**
- Direct URL: `/local/automator/index.php`
- Logs: `/local/automator/logs.php`

## Extending the Plugin

### Adding a new condition type

1. Create `classes/conditions/yourcondition.php` extending `\local_automator\condition_base`
2. Implement `check(int $userid): bool`
3. Implement `get_config_summary(): string`
4. Implement `static get_name(): string`
5. Create `classes/form/condition_yourcondition.php` extending `\moodleform`
6. Add language strings to `lang/en/local_automator.php`
7. Auto-discovery handles the rest

### Adding a new action type

1. Create `classes/actions/youraction.php` extending `\local_automator\action_base`
2. Implement `execute(int $userid): void`
3. Implement `get_config_summary(): string`
4. Implement `static get_name(): string`
5. Create `classes/form/action_youraction.php` extending `\moodleform`
6. Add language strings
7. Auto-discovery handles the rest
