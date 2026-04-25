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
 * Execution logs page.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_automator\output\logs_list;
use local_automator\tenant_helper;

$page    = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/automator/logs.php', ['page' => $page]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('logs_heading', 'local_automator'));
$PAGE->set_heading(get_string('logs_heading', 'local_automator'));

require_login();
require_capability('local/automator:viewlogs', $context);

if (!\local_dsl_tiers\api::has_feature('local_automator')) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('errortiergating', 'local_automator'), 'error');
    echo $OUTPUT->footer();
    die;
}

$params = [];
if (tenant_helper::is_tenant_manager()) {
    $tenantid = tenant_helper::get_current_tenantid();
    $where    = 'r.tenantid = :tenantid';
    $params['tenantid'] = $tenantid;
} else {
    $where = '1=1';
}

$sql = "SELECT l.*, r.name AS rulename, r.tenantid,
               u.firstname, u.lastname
          FROM {local_automator_log} l
          JOIN {local_automator_rules} r ON r.id = l.ruleid
          JOIN {user} u ON u.id = l.userid
         WHERE $where
      ORDER BY l.timecreated DESC";

$totalcount = $DB->count_records_sql(
    "SELECT COUNT(1) FROM {local_automator_log} l
       JOIN {local_automator_rules} r ON r.id = l.ruleid
      WHERE $where",
    $params
);

$logs = array_values($DB->get_records_sql($sql, $params, $page * $perpage, $perpage));

$renderable = new logs_list($logs, $totalcount, $page, $perpage);
$renderer   = $PAGE->get_renderer('local_automator');

echo $OUTPUT->header();
echo $renderer->render_logs_list($renderable);
echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
echo $OUTPUT->footer();
