<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action_add'] = 'Add action';
$string['automator:managerules'] = 'Manage automation rules';
$string['automator:viewlogs'] = 'View automation logs';
$string['action_delete'] = 'Delete action';
$string['action_edit'] = 'Edit action';
$string['action_sendnotification'] = 'Send notification';
$string['action_sendnotification_summary'] = 'Subject: {$a->subject} → {$a->sendto}';
$string['action_type'] = 'Action type';
$string['actions'] = 'Actions';
$string['actions_section'] = 'Actions';
$string['addnewrule'] = 'Add new rule';
$string['bcc'] = 'BCC';
$string['body'] = 'Body';
$string['cancel'] = 'Cancel';
$string['cc'] = 'CC';
$string['condition_add'] = 'Add condition';
$string['condition_coursecompletion'] = 'Course completion';
$string['condition_coursecompletion_summary'] = 'Course {$a->course}: {$a->operator}';
$string['condition_cprcertification'] = 'CPR certification status';
$string['condition_cprcertification_summary'] = 'CPR: {$a->operator}';
$string['condition_delete'] = 'Delete condition';
$string['condition_edit'] = 'Edit condition';
$string['condition_dspjob'] = 'DSP job';
$string['condition_dspjob_summary'] = 'DSP job {$a->operator}: {$a->job}';
$string['condition_profilefield'] = 'Profile field';
$string['condition_profilefield_summary'] = '{$a->field} {$a->operator} {$a->value}';
$string['condition_type'] = 'Condition type';
$string['conditions'] = 'Conditions';
$string['conditions_section'] = 'Conditions';
$string['confirmdelete'] = 'Are you sure you want to delete this?';
$string['confirmdeleterule'] = 'Are you sure you want to delete the rule "{$a}"?';
$string['course'] = 'Course';
$string['cprstatus_certified'] = 'Is certified';
$string['cprstatus_expired'] = 'Is expired';
$string['cprstatus_expiring'] = 'Expires within {$a} days';
$string['cprstatus_expiring_option'] = 'Expires within...';
$string['cprstatus_nocert'] = 'Has no certification';
$string['days'] = 'Days';
$string['description'] = 'Description';
$string['enabled'] = 'Enabled';
$string['errorinvalidrule'] = 'Invalid rule ID.';
$string['errorinvalidcondition'] = 'Invalid condition ID.';
$string['errorinvalidaction'] = 'Invalid action ID.';
$string['errornotificationfailed'] = 'Notification failed for user {$a}.';
$string['errorpermission'] = 'You do not have permission to perform this action.';
$string['errortiergating'] = 'The Automator feature is not available on your current plan. Please contact your administrator to upgrade.';
$string['expiresindays'] = 'Days before expiry';
$string['field'] = 'Field';
$string['field_contains'] = 'contains';
$string['field_equals'] = 'equals';
$string['field_isempty'] = 'is empty';
$string['field_isnotempty'] = 'is not empty';
$string['field_notequals'] = 'does not equal';
$string['job_has'] = 'Has job';
$string['job_hasnot'] = 'Does not have job';
$string['logs'] = 'Logs';
$string['logs_heading'] = 'Automator execution logs';
$string['logstatus_error'] = 'Error';
$string['logstatus_success'] = 'Success';
$string['manage_rules'] = 'Manage rules';
$string['message'] = 'Message';
$string['name'] = 'Name';
$string['newrule'] = 'New rule';
$string['noconditions'] = 'No conditions configured.';
$string['noactions'] = 'No actions configured.';
$string['nologs'] = 'No log entries found.';
$string['norules'] = 'No rules have been created yet.';
$string['operator'] = 'Operator';
$string['operator_all'] = 'All conditions must match (AND)';
$string['operator_any'] = 'At least one condition must match (ANY)';
$string['operator_completed'] = 'Has completed';
$string['operator_notcompleted'] = 'Has not completed';
$string['pluginname'] = 'Automator';
$string['privacy:metadata'] = 'The Automator plugin stores rule execution logs per user and rule-check timestamps per user.';
$string['privacy:metadata:local_automator_log:message'] = 'The log message from the rule execution.';
$string['privacy:metadata:local_automator_log:ruleid'] = 'The rule that was executed.';
$string['privacy:metadata:local_automator_log:status'] = 'The execution status (success or error).';
$string['privacy:metadata:local_automator_log:timecreated'] = 'When the execution occurred.';
$string['privacy:metadata:local_automator_log:userid'] = 'The user the rule was evaluated for.';
$string['privacy:metadata:local_automator_userchecks:lastchecked'] = 'When this rule was last checked for this user.';
$string['privacy:metadata:local_automator_userchecks:ruleid'] = 'The rule that was checked.';
$string['privacy:metadata:local_automator_userchecks:userid'] = 'The user the rule was checked for.';
$string['rule_edit'] = 'Edit rule';
$string['rule_new'] = 'New rule';
$string['rulename'] = 'Rule name';
$string['rules'] = 'Rules';
$string['saverulefirst'] = 'Save the rule above first to start adding conditions and actions.';
$string['rules_heading'] = 'Automation rules';
$string['scope'] = 'Scope';
$string['scope_sitewide'] = 'Site-wide';
$string['scope_tenant'] = '{$a}';
$string['selectconditiontype'] = 'Select condition type...';
$string['selectactiontype'] = 'Select action type...';
$string['send_from'] = 'Send from';
$string['send_from_admin'] = 'Site admin';
$string['send_from_matched'] = 'Matched user';
$string['send_from_specific'] = 'Specific user';
$string['send_to'] = 'Send to';
$string['send_to_admin'] = 'Site admin';
$string['send_to_agencymanager'] = 'Agency manager(s)';
$string['send_to_alladmins'] = 'All site admins';
$string['send_to_matched'] = 'Matched user';
$string['send_to_multiple'] = 'Multiple users (comma-separated IDs)';
$string['send_to_specific'] = 'Specific user';
$string['setting_batchsize'] = 'Batch size';
$string['setting_batchsize_desc'] = 'Number of users to process per scheduled task run.';
$string['setting_cacheduration'] = 'Cache duration (seconds)';
$string['setting_cacheduration_desc'] = 'How long to cache condition evaluation results.';
$string['setting_enableeventobservers'] = 'Enable event-driven triggers';
$string['setting_enableeventobservers_desc'] = 'When enabled, rules are evaluated immediately when relevant events fire.';
$string['setting_enablescheduledtask'] = 'Enable scheduled task';
$string['setting_enablescheduledtask_desc'] = 'When enabled, a scheduled task evaluates time-based conditions every 15 minutes.';
$string['setting_recheckinterval'] = 'Default recheck interval (seconds)';
$string['setting_recheckinterval_desc'] = 'Minimum time between rule executions for the same user. Prevents duplicate notifications.';
$string['specificuserid'] = 'User ID';
$string['specificuserids'] = 'User IDs (comma-separated)';
$string['status'] = 'Status';
$string['subject'] = 'Subject';
$string['tenant'] = 'Tenant';
$string['tenantscope'] = 'Tenant scope';
$string['tenantscope_help'] = 'Site admins can create rules that apply to all users (Site-wide) or to users in a specific tenant. Tenant managers can only create rules for their own tenant.';
$string['timestamp'] = 'Timestamp';
$string['user'] = 'User';
$string['value'] = 'Value';
$string['viewlogs'] = 'View logs';
