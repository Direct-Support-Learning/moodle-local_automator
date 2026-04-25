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
 * Rule create/edit page.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_automator\form\rule_edit as rule_edit_form;
use local_automator\output\rule_edit_page;
use local_automator\tenant_helper;

$id     = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/automator/edit.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

require_login();
require_capability('local/automator:managerules', $context);

if (!\local_dsl_tiers\api::has_feature('local_automator')) {
    redirect(
        new moodle_url('/local/automator/index.php'),
        get_string('errortiergating', 'local_automator'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$indexurl = new moodle_url('/local/automator/index.php');

// Handle delete action.
if ($action === 'delete' && $id) {
    require_sesskey();

    $rule = $DB->get_record('local_automator_rules', ['id' => $id], '*', MUST_EXIST);

    // Tenant managers can only delete their own tenant's rules.
    if (tenant_helper::is_tenant_manager()) {
        $tenantid = tenant_helper::get_current_tenantid();
        if ((int) $rule->tenantid !== $tenantid) {
            throw new \moodle_exception('errorpermission', 'local_automator');
        }
    }

    $DB->delete_records('local_automator_conditions', ['ruleid' => $id]);
    $DB->delete_records('local_automator_actions', ['ruleid' => $id]);
    $DB->delete_records('local_automator_userchecks', ['ruleid' => $id]);
    $DB->delete_records('local_automator_log', ['ruleid' => $id]);
    $DB->delete_records('local_automator_rules', ['id' => $id]);

    redirect($indexurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Load existing rule or prepare new one.
if ($id) {
    $rule = $DB->get_record('local_automator_rules', ['id' => $id], '*', MUST_EXIST);

    if (tenant_helper::is_tenant_manager()) {
        $tenantid = tenant_helper::get_current_tenantid();
        if ((int) $rule->tenantid !== $tenantid) {
            throw new \moodle_exception('errorpermission', 'local_automator');
        }
    }
} else {
    $rule = (object) [
        'id'                => 0,
        'name'              => '',
        'description'       => '',
        'enabled'           => 1,
        'conditionoperator' => 'AND',
        'tenantid'          => tenant_helper::is_tenant_manager()
            ? tenant_helper::get_current_tenantid()
            : null,
    ];
}

$heading = $id
    ? get_string('rule_edit', 'local_automator')
    : get_string('rule_new', 'local_automator');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->requires->js_call_amd('local_automator/rule_editor', 'init');

$form = new rule_edit_form($PAGE->url);
$form->set_data($rule);

if ($form->is_cancelled()) {
    redirect($indexurl);
} else if ($data = $form->get_data()) {
    require_sesskey();

    // Tenant managers cannot set tenantid — it's forced.
    if (tenant_helper::is_tenant_manager()) {
        $data->tenantid = tenant_helper::get_current_tenantid();
    } else {
        // Site admin: empty string means site-wide (NULL).
        $rawtenantid = $data->tenantid ?? '';
        $data->tenantid = ($rawtenantid === '' || $rawtenantid === '0' || !$rawtenantid)
            ? null
            : (int) $rawtenantid;
    }

    $data->timemodified = time();
    $data->usermodified = $USER->id;

    if ($data->id) {
        $DB->update_record('local_automator_rules', $data);
    } else {
        $data->timecreated = time();
        $data->sortorder   = $DB->count_records('local_automator_rules');
        $data->id          = $DB->insert_record('local_automator_rules', $data);
    }

    redirect(
        new moodle_url('/local/automator/edit.php', ['id' => $data->id]),
        get_string('changessaved'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
$form->display();

if ($id) {
    $conditions = array_values($DB->get_records(
        'local_automator_conditions',
        ['ruleid' => $id],
        'sortorder ASC'
    ));
    $actions = array_values($DB->get_records(
        'local_automator_actions',
        ['ruleid' => $id],
        'sortorder ASC'
    ));

    $renderable = new rule_edit_page($rule, $conditions, $actions);
    $renderer   = $PAGE->get_renderer('local_automator');
    echo $renderer->render_rule_edit_page($renderable);
}

echo $OUTPUT->footer();
