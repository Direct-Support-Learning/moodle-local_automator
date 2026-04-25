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
 * Form for CPR Certification Status condition.
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
 * Configuration form for the cprcertification condition type.
 */
class condition_cprcertification extends \moodleform {

    /**
     * {@inheritdoc}
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'ruleid');
        $mform->setType('ruleid', PARAM_INT);

        $mform->addElement('hidden', 'conditionid');
        $mform->setType('conditionid', PARAM_INT);

        $mform->addElement('hidden', 'type', 'cprcertification');
        $mform->setType('type', PARAM_ALPHANUMEXT);

        $operatoroptions = [
            'certified' => get_string('cprstatus_certified', 'local_automator'),
            'expiring'  => get_string('cprstatus_expiring', 'local_automator', 30),
            'expired'   => get_string('cprstatus_expired', 'local_automator'),
            'nocert'    => get_string('cprstatus_nocert', 'local_automator'),
        ];
        $mform->addElement(
            'select',
            'operator',
            get_string('operator', 'local_automator'),
            $operatoroptions
        );

        $mform->addElement(
            'text',
            'expiresindays',
            get_string('expiresindays', 'local_automator'),
            ['size' => 5]
        );
        $mform->setType('expiresindays', PARAM_INT);
        $mform->setDefault('expiresindays', 30);
        $mform->hideIf('expiresindays', 'operator', 'neq', 'expiring');

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
