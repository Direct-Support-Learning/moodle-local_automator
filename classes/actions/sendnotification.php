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
 * Send notification action.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\actions;

defined('MOODLE_INTERNAL') || die();

use local_automator\action_base;
use local_automator\tenant_helper;

/**
 * Sends an email notification via email_to_user().
 */
class sendnotification extends action_base {

    /**
     * {@inheritdoc}
     */
    public static function get_name(): string {
        return get_string('action_sendnotification', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $userid): void {
        global $DB, $CFG;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        $subject = $this->resolve_placeholders($this->get_config('subject', ''), $user);
        $body    = $this->resolve_placeholders($this->get_config('body', ''), $user);

        $from = $this->resolve_sender($this->get_config('sendfrom', 'admin'), $user);
        $tolist = $this->resolve_recipients($this->get_config('sendto', 'matched'), $user);
        $cclist  = $this->resolve_recipients($this->get_config('cc', ''), $user);
        $bcclist = $this->resolve_recipients($this->get_config('bcc', ''), $user);

        foreach ($tolist as $touser) {
            email_to_user($touser, $from, $subject, $body, '', '', '', true, '', '', 79, $cclist, $bcclist);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get_config_summary(): string {
        return get_string('action_sendnotification_summary', 'local_automator', (object) [
            'subject' => $this->get_config('subject', ''),
            'sendto'  => $this->get_config('sendto', 'matched'),
        ]);
    }

    /**
     * Replace template placeholders with actual user values.
     *
     * @param string $text
     * @param \stdClass $user
     * @return string
     */
    private function resolve_placeholders(string $text, \stdClass $user): string {
        global $SITE;

        $tenantname = tenant_helper::get_tenant_name(tenant_helper::get_user_tenantid((int) $user->id));

        $replacements = [
            '{fullname}'   => fullname($user),
            '{firstname}'  => $user->firstname,
            '{lastname}'   => $user->lastname,
            '{sitename}'   => $SITE->fullname,
            '{tenantname}' => $tenantname,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Resolve the sender user object.
     *
     * @param string $sendfrom
     * @param \stdClass $matcheduser
     * @return \stdClass
     */
    private function resolve_sender(string $sendfrom, \stdClass $matcheduser): \stdClass {
        global $DB;

        if ($sendfrom === 'matched') {
            return $matcheduser;
        }
        if ($sendfrom === 'specific') {
            $specificid = (int) $this->get_config('sendfromid', 0);
            if ($specificid) {
                $user = $DB->get_record('user', ['id' => $specificid]);
                if ($user) {
                    return $user;
                }
            }
        }
        return \core_user::get_support_user();
    }

    /**
     * Resolve a recipient config value to an array of user objects.
     *
     * @param string $sendto
     * @param \stdClass $matcheduser
     * @return \stdClass[]
     */
    private function resolve_recipients(string $sendto, \stdClass $matcheduser): array {
        global $DB;

        if (empty($sendto)) {
            return [];
        }

        if ($sendto === 'matched') {
            return [$matcheduser];
        }

        if ($sendto === 'admin') {
            $admin = get_admin();
            return $admin ? [$admin] : [];
        }

        if ($sendto === 'alladmins') {
            return array_values(get_admins());
        }

        if ($sendto === 'specific') {
            $specificid = (int) $this->get_config('sendtoid', 0);
            if ($specificid) {
                $user = $DB->get_record('user', ['id' => $specificid]);
                return $user ? [$user] : [];
            }
            return [];
        }

        if ($sendto === 'multiple') {
            $ids = $this->get_config('sendtoids', '');
            $result = [];
            foreach (explode(',', $ids) as $rawid) {
                $id = (int) trim($rawid);
                if ($id > 0) {
                    $user = $DB->get_record('user', ['id' => $id]);
                    if ($user) {
                        $result[] = $user;
                    }
                }
            }
            return $result;
        }

        return [];
    }
}
