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
 * Course completion condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\conditions;

defined('MOODLE_INTERNAL') || die();

use local_automator\condition_base;

/**
 * Checks whether a user has completed (or not completed) a specific course.
 */
class coursecompletion extends condition_base {

    /**
     * {@inheritdoc}
     */
    public static function get_name(): string {
        return get_string('condition_coursecompletion', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function check(int $userid): bool {
        global $DB;

        $courseid = (int) $this->get_config('courseid', 0);
        $operator = $this->get_config('operator', 'completed');

        if (!$courseid) {
            return false;
        }

        $completed = $DB->record_exists_select(
            'course_completions',
            'userid = :userid AND course = :courseid AND timecompleted IS NOT NULL',
            ['userid' => $userid, 'courseid' => $courseid]
        );

        return $operator === 'completed' ? $completed : !$completed;
    }

    /**
     * {@inheritdoc}
     */
    public function get_config_summary(): string {
        global $DB;

        $courseid = (int) $this->get_config('courseid', 0);
        $operator = $this->get_config('operator', 'completed');

        $coursename = '';
        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid], 'fullname');
            $coursename = $course ? $course->fullname : "ID $courseid";
        }

        return get_string('condition_coursecompletion_summary', 'local_automator', (object) [
            'course'   => $coursename,
            'operator' => get_string('operator_' . $operator, 'local_automator'),
        ]);
    }
}
