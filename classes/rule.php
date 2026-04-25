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
 * Rule evaluation engine.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles loading, evaluating, and executing automation rules.
 */
class rule {

    /**
     * Evaluate and execute all applicable rules for a user.
     *
     * Evaluates site-wide rules plus rules matching the user's tenant.
     *
     * @param int $userid
     * @param bool $forcerecheck Skip the recheck interval check.
     */
    public static function evaluate_for_user(int $userid, bool $forcerecheck = false): void {
        global $DB;

        $tenantid = tenant_helper::get_user_tenantid($userid);

        // Build rule query: site-wide rules + tenant-specific rules.
        $params = ['enabled' => 1];
        if ($tenantid !== null) {
            $tenantclause = '(tenantid IS NULL OR tenantid = :tenantid)';
            $params['tenantid'] = $tenantid;
        } else {
            // Site admins / untenanted users only get site-wide rules.
            $tenantclause = 'tenantid IS NULL';
        }

        $rules = $DB->get_records_select(
            'local_automator_rules',
            "enabled = :enabled AND $tenantclause",
            $params,
            'sortorder ASC'
        );

        $recheckinterval = (int) get_config('local_automator', 'recheckinterval');
        if (!$recheckinterval) {
            $recheckinterval = 86400;
        }

        foreach ($rules as $rulerecord) {
            if (!$forcerecheck && !self::should_check($rulerecord->id, $userid, $recheckinterval)) {
                continue;
            }

            self::evaluate_rule($rulerecord, $userid);
        }
    }

    /**
     * Evaluate a single rule record for a user and execute actions if conditions match.
     *
     * @param \stdClass $rulerecord
     * @param int $userid
     */
    public static function evaluate_rule(\stdClass $rulerecord, int $userid): void {
        global $DB;

        $conditions = $DB->get_records(
            'local_automator_conditions',
            ['ruleid' => $rulerecord->id],
            'sortorder ASC'
        );

        $matched = self::evaluate_conditions($conditions, $rulerecord->conditionoperator, $userid);

        self::update_last_checked($rulerecord->id, $userid);

        if (!$matched) {
            return;
        }

        $actions = $DB->get_records(
            'local_automator_actions',
            ['ruleid' => $rulerecord->id],
            'sortorder ASC'
        );

        foreach ($actions as $actionrecord) {
            self::execute_action($actionrecord, $rulerecord->id, $userid);
        }
    }

    /**
     * Evaluate a set of conditions for a user.
     *
     * @param array $conditions
     * @param string $operator AND or ANY
     * @param int $userid
     * @return bool
     */
    private static function evaluate_conditions(array $conditions, string $operator, int $userid): bool {
        if (empty($conditions)) {
            return false;
        }

        foreach ($conditions as $condrecord) {
            $cached = cache_manager::get_condition_result((int) $condrecord->id, $userid);

            if ($cached !== null) {
                $result = $cached;
            } else {
                $condition = condition_base::create($condrecord->type, (string) $condrecord->configdata);
                if ($condition === null) {
                    $result = false;
                } else {
                    $result = $condition->check($userid);
                }
                cache_manager::set_condition_result((int) $condrecord->id, $userid, $result);
            }

            if ($operator === 'ANY' && $result) {
                return true;
            }
            if ($operator === 'AND' && !$result) {
                return false;
            }
        }

        return $operator === 'AND';
    }

    /**
     * Execute a single action for a user, logging the result.
     *
     * @param \stdClass $actionrecord
     * @param int $ruleid
     * @param int $userid
     */
    private static function execute_action(\stdClass $actionrecord, int $ruleid, int $userid): void {
        global $DB;

        $action = action_base::create($actionrecord->type, (string) $actionrecord->configdata);
        if ($action === null) {
            self::log($ruleid, $userid, 'error', "Unknown action type: {$actionrecord->type}");
            return;
        }

        try {
            $action->execute($userid);
            self::log($ruleid, $userid, 'success', "Action {$actionrecord->type} executed.");
        } catch (\Throwable $e) {
            self::log($ruleid, $userid, 'error', $e->getMessage());
        }
    }

    /**
     * Check if a rule should be evaluated for a user based on the recheck interval.
     *
     * @param int $ruleid
     * @param int $userid
     * @param int $recheckinterval Seconds.
     * @return bool
     */
    private static function should_check(int $ruleid, int $userid, int $recheckinterval): bool {
        global $DB;

        $record = $DB->get_record('local_automator_userchecks', ['ruleid' => $ruleid, 'userid' => $userid]);
        if (!$record) {
            return true;
        }
        return (time() - $record->lastchecked) >= $recheckinterval;
    }

    /**
     * Record the current time as the last check for a user/rule pair.
     *
     * @param int $ruleid
     * @param int $userid
     */
    private static function update_last_checked(int $ruleid, int $userid): void {
        global $DB;

        $record = $DB->get_record('local_automator_userchecks', ['ruleid' => $ruleid, 'userid' => $userid]);
        if ($record) {
            $record->lastchecked = time();
            $DB->update_record('local_automator_userchecks', $record);
        } else {
            $DB->insert_record('local_automator_userchecks', (object) [
                'ruleid'      => $ruleid,
                'userid'      => $userid,
                'lastchecked' => time(),
            ]);
        }
    }

    /**
     * Write an entry to the execution log.
     *
     * @param int $ruleid
     * @param int $userid
     * @param string $status success or error
     * @param string $message
     */
    public static function log(int $ruleid, int $userid, string $status, string $message): void {
        global $DB;

        $DB->insert_record('local_automator_log', (object) [
            'ruleid'      => $ruleid,
            'userid'      => $userid,
            'status'      => $status,
            'message'     => $message,
            'timecreated' => time(),
        ]);
    }
}
