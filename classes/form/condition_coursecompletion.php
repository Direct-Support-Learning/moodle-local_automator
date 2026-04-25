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
 * Form for Course Completion condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Configuration form for the coursecompletion condition type.
 */
class condition_coursecompletion extends \moodleform {

    /**
     * {@inheritdoc}
     */
    public function definition(): void {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'ruleid');
        $mform->setType('ruleid', PARAM_INT);

        $mform->addElement('hidden', 'conditionid');
        $mform->setType('conditionid', PARAM_INT);

        $mform->addElement('hidden', 'type', 'coursecompletion');
        $mform->setType('type', PARAM_ALPHANUMEXT);

        $courses = $DB->get_records_select(
            'course',
            'id <> :siteid',
            ['siteid' => SITEID],
            'fullname ASC',
            'id, fullname'
        );
        $courseoptions = [];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }

        $mform->addElement(
            'select',
            'courseid',
            get_string('course', 'local_automator'),
            $courseoptions
        );
        $mform->addRule('courseid', null, 'required', null, 'client');

        $operatoroptions = [
            'completed'    => get_string('operator_completed', 'local_automator'),
            'notcompleted' => get_string('operator_notcompleted', 'local_automator'),
        ];
        $mform->addElement(
            'select',
            'operator',
            get_string('operator', 'local_automator'),
            $operatoroptions
        );

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
