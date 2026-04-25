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
 * Delete a condition from a rule.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_automator\tenant_helper;

$ruleid      = required_param('ruleid', PARAM_INT);
$conditionid = required_param('id', PARAM_INT);

require_sesskey();

$context = context_system::instance();

require_login();
require_capability('local/automator:managerules', $context);

$rule = $DB->get_record('local_automator_rules', ['id' => $ruleid], '*', MUST_EXIST);

if (tenant_helper::is_tenant_manager()) {
    $tenantid = tenant_helper::get_current_tenantid();
    if ((int) $rule->tenantid !== $tenantid) {
        throw new \moodle_exception('errorpermission', 'local_automator');
    }
}

$DB->delete_records('local_automator_conditions', ['id' => $conditionid, 'ruleid' => $ruleid]);

redirect(
    new moodle_url('/local/automator/edit.php', ['id' => $ruleid]),
    get_string('changessaved'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
