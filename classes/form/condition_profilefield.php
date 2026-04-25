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
 * Form for Profile Field condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_automator\conditions\profilefield;

/**
 * Configuration form for the profilefield condition type.
 */
class condition_profilefield extends \moodleform {

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

        $mform->addElement('hidden', 'type', 'profilefield');
        $mform->setType('type', PARAM_ALPHANUMEXT);

        // Build field list from standard + custom fields.
        $fieldoptions = [];
        foreach (profilefield::STANDARD_FIELDS as $f) {
            $fieldoptions[$f] = $f;
        }

        $customfields = $DB->get_records('user_info_field', null, 'name ASC', 'shortname, name');
        foreach ($customfields as $cf) {
            if ($cf->shortname !== 'dsp_job') {
                $fieldoptions[$cf->shortname] = $cf->name . ' (' . $cf->shortname . ')';
            }
        }

        $mform->addElement(
            'select',
            'field',
            get_string('field', 'local_automator'),
            $fieldoptions
        );
        $mform->addRule('field', null, 'required', null, 'client');

        $operatoroptions = [
            'equals'     => get_string('field_equals', 'local_automator'),
            'notequals'  => get_string('field_notequals', 'local_automator'),
            'contains'   => get_string('field_contains', 'local_automator'),
            'isempty'    => get_string('field_isempty', 'local_automator'),
            'isnotempty' => get_string('field_isnotempty', 'local_automator'),
        ];
        $mform->addElement(
            'select',
            'operator',
            get_string('operator', 'local_automator'),
            $operatoroptions
        );

        $mform->addElement(
            'text',
            'value',
            get_string('value', 'local_automator'),
            ['size' => 40]
        );
        $mform->setType('value', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
