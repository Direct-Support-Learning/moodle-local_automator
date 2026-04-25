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
 * Add a condition to a rule.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_automator\tenant_helper;

$ruleid = required_param('ruleid', PARAM_INT);
$type   = required_param('type', PARAM_ALPHANUMEXT);

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/automator/add_condition.php', ['ruleid' => $ruleid, 'type' => $type]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('condition_add', 'local_automator'));
$PAGE->set_heading(get_string('condition_add', 'local_automator'));

require_login();
require_capability('local/automator:managerules', $context);

$rule = $DB->get_record('local_automator_rules', ['id' => $ruleid], '*', MUST_EXIST);

if (tenant_helper::is_tenant_manager()) {
    $tenantid = tenant_helper::get_current_tenantid();
    if ((int) $rule->tenantid !== $tenantid) {
        throw new \moodle_exception('errorpermission', 'local_automator');
    }
}

$formclass = '\\local_automator\\form\\condition_' . $type;
if (!class_exists($formclass)) {
    throw new \moodle_exception('errorinvalidcondition', 'local_automator');
}

$returnurl = new moodle_url('/local/automator/edit.php', ['id' => $ruleid]);

$form = new $formclass($PAGE->url);
$form->set_data(['ruleid' => $ruleid, 'conditionid' => 0, 'type' => $type]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    require_sesskey();

    $configdata = (array) $data;
    unset($configdata['ruleid'], $configdata['conditionid'], $configdata['type'], $configdata['sesskey']);

    $sortorder = $DB->count_records('local_automator_conditions', ['ruleid' => $ruleid]);

    $DB->insert_record('local_automator_conditions', (object) [
        'ruleid'     => $ruleid,
        'type'       => $type,
        'configdata' => json_encode($configdata),
        'sortorder'  => $sortorder,
    ]);

    redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
