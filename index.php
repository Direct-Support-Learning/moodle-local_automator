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
 * Rules management index page.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_automator\output\rules_list;
use local_automator\tenant_helper;

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/automator/index.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('rules_heading', 'local_automator'));
$PAGE->set_heading(get_string('rules_heading', 'local_automator'));

require_login();
require_capability('local/automator:managerules', $context);

// Tier gate.
if (!\local_dsl_tiers\api::has_feature('local_automator')) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('errortiergating', 'local_automator'), 'error');
    echo $OUTPUT->footer();
    die;
}

$PAGE->requires->js_call_amd('local_automator/rule_editor', 'init');

// Load rules scoped by user type.
$params = ['enabled_all' => 1]; // Unused, just for clarity.
if (tenant_helper::is_tenant_manager()) {
    $tenantid = tenant_helper::get_current_tenantid();
    $where    = 'tenantid = :tenantid';
    $params   = ['tenantid' => $tenantid];
} else {
    $where  = '1=1';
    $params = [];
}

$rules = $DB->get_records_select('local_automator_rules', $where, $params, 'sortorder ASC, name ASC');

$renderable = new rules_list(array_values($rules));
$renderer   = $PAGE->get_renderer('local_automator');

echo $OUTPUT->header();
echo $renderer->render_rules_list($renderable);
echo $OUTPUT->footer();
