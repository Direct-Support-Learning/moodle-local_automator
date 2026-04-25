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
 * Scheduled task to process automation rules.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\task;

defined('MOODLE_INTERNAL') || die();

use local_automator\rule;

/**
 * Safety-net scheduled task. Runs every 15 minutes.
 *
 * Processes users in batches and evaluates all enabled rules, respecting the
 * configured recheck interval so notifications are not re-sent prematurely.
 */
class process_rules extends \core\task\scheduled_task {

    /**
     * {@inheritdoc}
     */
    public function get_name(): string {
        return get_string('pluginname', 'local_automator') . ' — ' . get_string('manage_rules', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void {
        global $DB;

        if (!get_config('local_automator', 'enablescheduledtask')) {
            mtrace('local_automator: scheduled task disabled — skipping.');
            return;
        }

        $batchsize = (int) get_config('local_automator', 'batchsize');
        if ($batchsize < 1) {
            $batchsize = 100;
        }

        // Check whether any enabled rules exist before fetching users.
        if (!$DB->record_exists('local_automator_rules', ['enabled' => 1])) {
            mtrace('local_automator: no enabled rules — skipping.');
            return;
        }

        $users = $DB->get_records_select(
            'user',
            'deleted = 0 AND suspended = 0 AND confirmed = 1',
            [],
            'id ASC',
            'id',
            0,
            $batchsize
        );

        $count = 0;
        foreach ($users as $user) {
            try {
                rule::evaluate_for_user((int) $user->id, false);
                $count++;
            } catch (\Throwable $e) {
                mtrace('local_automator: error for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        mtrace("local_automator: processed $count users.");
    }
}
