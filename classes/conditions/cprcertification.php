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
 * CPR Certification Status condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\conditions;

defined('MOODLE_INTERNAL') || die();

use local_automator\condition_base;

/**
 * Checks the status of a user's CPR/FA certification.
 *
 * Expiry = timecompleted + 730 days (2 years).
 */
class cprcertification extends condition_base {

    /** @var int CPR certification validity in seconds (2 years). */
    const VALIDITY_SECONDS = 730 * DAYSECS;

    /**
     * {@inheritdoc}
     */
    public static function get_name(): string {
        return get_string('condition_cprcertification', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function check(int $userid): bool {
        $operator      = $this->get_config('operator', 'certified');
        $expiresindays = (int) $this->get_config('expiresindays', 30);

        $expirytime = $this->get_expiry_time($userid);

        switch ($operator) {
            case 'certified':
                return $expirytime !== null && $expirytime > time();
            case 'expiring':
                if ($expirytime === null || $expirytime <= time()) {
                    return false;
                }
                return ($expirytime - time()) <= ($expiresindays * DAYSECS);
            case 'expired':
                return $expirytime !== null && $expirytime <= time();
            case 'nocert':
                return $expirytime === null;
            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get_config_summary(): string {
        $operator      = $this->get_config('operator', 'certified');
        $expiresindays = (int) $this->get_config('expiresindays', 30);

        if ($operator === 'expiring') {
            $opstring = get_string('cprstatus_expiring', 'local_automator', $expiresindays);
        } else {
            $opstring = get_string('cprstatus_' . $operator, 'local_automator');
        }

        return get_string('condition_cprcertification_summary', 'local_automator', (object) [
            'operator' => $opstring,
        ]);
    }

    /**
     * Determine the CPR certification expiry time for a user.
     *
     * Checks both Moodle course completions and the local_externalcpr plugin.
     *
     * @param int $userid
     * @return int|null Unix timestamp of expiry, or null if no certification.
     */
    private function get_expiry_time(int $userid): ?int {
        global $DB;

        // Find all CPR courses via custom field certification_type = cpr_first_aid.
        $sql = 'SELECT cc.timecompleted
                  FROM {course_completions} cc
                  JOIN {customfield_data} cfd ON cfd.instanceid = cc.course
                  JOIN {customfield_field} cff ON cff.id = cfd.fieldid
                 WHERE cff.shortname = :shortname
                   AND cfd.value = :certtype
                   AND cc.userid = :userid
                   AND cc.timecompleted IS NOT NULL
              ORDER BY cc.timecompleted DESC';

        $completions = $DB->get_records_sql($sql, [
            'shortname' => 'certification_type',
            'certtype'  => 'cpr_first_aid',
            'userid'    => $userid,
        ]);

        $latestexpiry = null;
        foreach ($completions as $completion) {
            $expiry = (int) $completion->timecompleted + self::VALIDITY_SECONDS;
            if ($latestexpiry === null || $expiry > $latestexpiry) {
                $latestexpiry = $expiry;
            }
        }

        // Also check local_externalcpr if installed.
        if (class_exists('\local_externalcpr\api')) {
            $external = \local_externalcpr\api::get_active_certification($userid);
            if ($external && !empty($external->expirytime)) {
                if ($latestexpiry === null || $external->expirytime > $latestexpiry) {
                    $latestexpiry = (int) $external->expirytime;
                }
            }
        }

        return $latestexpiry;
    }
}
