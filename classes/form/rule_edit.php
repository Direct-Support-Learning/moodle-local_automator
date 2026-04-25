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
 * Rule edit form.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_automator\tenant_helper;

/**
 * Form for creating and editing automation rules.
 */
class rule_edit extends \moodleform {

    /**
     * {@inheritdoc}
     */
    public function definition(): void {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'text',
            'name',
            get_string('name', 'local_automator'),
            ['size' => 60]
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_automator'),
            ['rows' => 3, 'cols' => 60]
        );
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement(
            'advcheckbox',
            'enabled',
            get_string('enabled', 'local_automator')
        );
        $mform->setDefault('enabled', 1);

        $operatoroptions = [
            'AND' => get_string('operator_all', 'local_automator'),
            'ANY' => get_string('operator_any', 'local_automator'),
        ];
        $mform->addElement(
            'select',
            'conditionoperator',
            get_string('operator', 'local_automator'),
            $operatoroptions
        );
        $mform->setDefault('conditionoperator', 'AND');

        if (is_siteadmin()) {
            $tenants = ['' => get_string('scope_sitewide', 'local_automator')];
            $tenants += tenant_helper::get_all_tenants();
            $mform->addElement(
                'select',
                'tenantid',
                get_string('tenantscope', 'local_automator'),
                $tenants
            );
            $mform->addHelpButton('tenantscope', 'tenantscope', 'local_automator');
        } else {
            $mform->addElement('hidden', 'tenantid');
        }
        $mform->setType('tenantid', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * {@inheritdoc}
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($data['name'])) {
            $errors['name'] = get_string('required');
        }

        return $errors;
    }
}
