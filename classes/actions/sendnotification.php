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

        $from   = $this->resolve_sender($this->get_config('sendfrom', 'matched'), $user);
        $tolist = $this->resolve_recipients($this->get_config('sendto', 'matched'), $user, 'sendtoid', 'sendtoids');
        $cclist = $this->resolve_recipients($this->get_config('cc', ''), $user, 'ccid');
        $bcclist = $this->resolve_recipients($this->get_config('bcc', ''), $user, 'bccid');

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
        if ($sendfrom === 'admin') {
            return \core_user::get_support_user();
        }
        return \core_user::get_support_user();
    }

    /**
     * Resolve a recipient config value to an array of user objects.
     *
     * @param string $sendto
     * @param \stdClass $matcheduser
     * @param string $specificidkey Config key for the single-user autocomplete ID.
     * @param string $multipleidskey Config key for comma-separated IDs.
     * @return \stdClass[]
     */
    private function resolve_recipients(
        string $sendto,
        \stdClass $matcheduser,
        string $specificidkey = 'sendtoid',
        string $multipleidskey = 'sendtoids'
    ): array {
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

        if ($sendto === 'agencymanager') {
            return $this->get_agency_managers((int) $matcheduser->id);
        }

        if ($sendto === 'specific') {
            $specificid = (int) $this->get_config($specificidkey, 0);
            if ($specificid) {
                $user = $DB->get_record('user', ['id' => $specificid]);
                return $user ? [$user] : [];
            }
            return [];
        }

        if ($sendto === 'multiple') {
            $ids = $this->get_config($multipleidskey, '');
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

    /**
     * Find agency managers for the matched user's tenant.
     *
     * Returns users with the agencymanager role in any context who are also
     * members of the matched user's tenant cohort. Falls back to all
     * agencymanager-role holders when MuTMS is not installed.
     *
     * @param int $userid The matched user.
     * @return \stdClass[]
     */
    private function get_agency_managers(int $userid): array {
        global $DB;

        $roleid = $DB->get_field('role', 'id', ['shortname' => 'agencymanager']);
        if (!$roleid) {
            return [];
        }

        $tenantid = \local_automator\tenant_helper::get_user_tenantid($userid);

        if ($tenantid === null) {
            // No tenant — return all agency managers at system context.
            $sql = 'SELECT DISTINCT u.*
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                     WHERE ra.roleid = :roleid
                       AND u.deleted = 0 AND u.suspended = 0';
            return array_values($DB->get_records_sql($sql, ['roleid' => $roleid]));
        }

        // Tenant-scoped: return agency managers who are also in the tenant's cohort.
        $sql = 'SELECT DISTINCT u.*
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {cohort_members} cm ON cm.userid = u.id
                  JOIN {tool_mutenancy_tenant} t ON t.cohortid = cm.cohortid
                 WHERE ra.roleid = :roleid
                   AND t.id = :tenantid
                   AND u.deleted = 0 AND u.suspended = 0';

        $managers = array_values($DB->get_records_sql($sql, [
            'roleid'   => $roleid,
            'tenantid' => $tenantid,
        ]));

        // If no tenant-scoped agency managers, fall back to system-level ones.
        if (empty($managers)) {
            $sql = 'SELECT DISTINCT u.*
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                     WHERE ra.roleid = :roleid
                       AND u.deleted = 0 AND u.suspended = 0';
            $managers = array_values($DB->get_records_sql($sql, ['roleid' => $roleid]));
        }

        return $managers;
    }
}
