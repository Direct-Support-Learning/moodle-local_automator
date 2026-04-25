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
 * Form for Send Notification action.
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
 * Configuration form for the sendnotification action type.
 */
class action_sendnotification extends \moodleform {

    /**
     * {@inheritdoc}
     */
    public function definition(): void {
        $mform = $this->_form;
        $istenantmanager = tenant_helper::is_tenant_manager();

        $mform->addElement('hidden', 'ruleid');
        $mform->setType('ruleid', PARAM_INT);

        $mform->addElement('hidden', 'actionid');
        $mform->setType('actionid', PARAM_INT);

        $mform->addElement('hidden', 'type', 'sendnotification');
        $mform->setType('type', PARAM_ALPHANUMEXT);

        $mform->addElement(
            'text',
            'subject',
            get_string('subject', 'local_automator'),
            ['size' => 60]
        );
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'body',
            get_string('body', 'local_automator'),
            ['rows' => 6, 'cols' => 60]
        );
        $mform->setType('body', PARAM_TEXT);

        // Send From.
        $sendfromoptions = [
            'matched'  => get_string('send_from_matched', 'local_automator'),
            'specific' => get_string('send_from_specific', 'local_automator'),
        ];
        if (!$istenantmanager) {
            $sendfromoptions['admin'] = get_string('send_from_admin', 'local_automator');
        }

        $mform->addElement(
            'select',
            'sendfrom',
            get_string('send_from', 'local_automator'),
            $sendfromoptions
        );
        $mform->setDefault('sendfrom', 'matched');

        $mform->addElement(
            'autocomplete',
            'sendfromid',
            get_string('specificuserid', 'local_automator'),
            [],
            ['ajax' => 'core_user/form_user_selector', 'multiple' => false]
        );
        $mform->setType('sendfromid', PARAM_INT);
        $mform->hideIf('sendfromid', 'sendfrom', 'neq', 'specific');

        // Send To.
        $sendtooptions = $this->get_sendto_options($istenantmanager);

        $mform->addElement(
            'select',
            'sendto',
            get_string('send_to', 'local_automator'),
            $sendtooptions
        );
        $mform->setDefault('sendto', $istenantmanager ? 'agencymanager' : 'matched');

        $mform->addElement(
            'autocomplete',
            'sendtoid',
            get_string('specificuserid', 'local_automator'),
            [],
            ['ajax' => 'core_user/form_user_selector', 'multiple' => false]
        );
        $mform->setType('sendtoid', PARAM_INT);
        $mform->hideIf('sendtoid', 'sendto', 'neq', 'specific');

        $mform->addElement(
            'text',
            'sendtoids',
            get_string('specificuserids', 'local_automator'),
            ['size' => 40]
        );
        $mform->setType('sendtoids', PARAM_TEXT);
        $mform->hideIf('sendtoids', 'sendto', 'neq', 'multiple');

        // CC.
        $ccbccoptions = ['' => get_string('none', 'moodle')] + $sendtooptions;

        $mform->addElement(
            'select',
            'cc',
            get_string('cc', 'local_automator'),
            $ccbccoptions
        );

        $mform->addElement(
            'autocomplete',
            'ccid',
            get_string('specificuserid', 'local_automator'),
            [],
            ['ajax' => 'core_user/form_user_selector', 'multiple' => false]
        );
        $mform->setType('ccid', PARAM_INT);
        $mform->hideIf('ccid', 'cc', 'neq', 'specific');

        // BCC.
        $mform->addElement(
            'select',
            'bcc',
            get_string('bcc', 'local_automator'),
            $ccbccoptions
        );

        $mform->addElement(
            'autocomplete',
            'bccid',
            get_string('specificuserid', 'local_automator'),
            [],
            ['ajax' => 'core_user/form_user_selector', 'multiple' => false]
        );
        $mform->setType('bccid', PARAM_INT);
        $mform->hideIf('bccid', 'bcc', 'neq', 'specific');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Build the Send To / CC / BCC dropdown options.
     *
     * Tenant managers do not see site admin options.
     *
     * @param bool $istenantmanager
     * @return array
     */
    private function get_sendto_options(bool $istenantmanager): array {
        $options = [
            'matched'        => get_string('send_to_matched', 'local_automator'),
            'agencymanager'  => get_string('send_to_agencymanager', 'local_automator'),
            'specific'       => get_string('send_to_specific', 'local_automator'),
            'multiple'       => get_string('send_to_multiple', 'local_automator'),
        ];

        if (!$istenantmanager) {
            $options['admin']     = get_string('send_to_admin', 'local_automator');
            $options['alladmins'] = get_string('send_to_alladmins', 'local_automator');
        }

        return $options;
    }
}
