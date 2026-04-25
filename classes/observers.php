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
 * Event observers.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Observes Moodle core events and triggers rule evaluation.
 */
class observers {

    /**
     * Handle user_created event.
     *
     * @param \core\event\user_created $event
     */
    public static function user_created(\core\event\user_created $event): void {
        if (!get_config('local_automator', 'enableeventobservers')) {
            return;
        }
        self::process_user($event->relateduserid ?? $event->userid);
    }

    /**
     * Handle user_updated event.
     *
     * @param \core\event\user_updated $event
     */
    public static function user_updated(\core\event\user_updated $event): void {
        if (!get_config('local_automator', 'enableeventobservers')) {
            return;
        }
        $userid = $event->relateduserid ?? $event->userid;
        cache_manager::invalidate_user($userid);
        self::process_user($userid);
    }

    /**
     * Handle course_completed event.
     *
     * @param \core\event\course_completed $event
     */
    public static function course_completed(\core\event\course_completed $event): void {
        if (!get_config('local_automator', 'enableeventobservers')) {
            return;
        }
        $userid = $event->relateduserid ?? $event->userid;
        cache_manager::invalidate_user($userid);
        self::process_user($userid);
    }

    /**
     * Handle user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event): void {
        if (!get_config('local_automator', 'enableeventobservers')) {
            return;
        }
        self::process_user($event->relateduserid ?? $event->userid);
    }

    /**
     * Evaluate all rules for a user, bypassing recheck interval.
     *
     * Event-driven checks should always run regardless of last-checked time.
     *
     * @param int $userid
     */
    private static function process_user(int $userid): void {
        if (!$userid) {
            return;
        }
        try {
            rule::evaluate_for_user($userid, true);
        } catch (\Throwable $e) {
            debugging('local_automator observer error for user ' . $userid . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
