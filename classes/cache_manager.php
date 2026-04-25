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
 * Cache management for condition evaluation results.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Manages caching for condition results and user data.
 */
class cache_manager {

    /**
     * Get a cached condition result.
     *
     * @param int $conditionid
     * @param int $userid
     * @return bool|null Null if not cached.
     */
    public static function get_condition_result(int $conditionid, int $userid): ?bool {
        $cache = \cache::make('local_automator', 'conditionresults');
        $value = $cache->get(self::condition_key($conditionid, $userid));
        if ($value === false) {
            return null;
        }
        return (bool) $value;
    }

    /**
     * Store a condition result in cache.
     *
     * @param int $conditionid
     * @param int $userid
     * @param bool $result
     */
    public static function set_condition_result(int $conditionid, int $userid, bool $result): void {
        $cache = \cache::make('local_automator', 'conditionresults');
        $cache->set(self::condition_key($conditionid, $userid), $result ? 1 : 0);
    }

    /**
     * Invalidate all cached condition results for a user.
     *
     * @param int $userid
     */
    public static function invalidate_user(int $userid): void {
        // Cache API does not support prefix deletion; invalidate tenant cache.
        $tenantcache = \cache::make('local_automator', 'usertenants');
        $tenantcache->delete('user_' . $userid);
    }

    /**
     * Build a unique cache key for a condition + user pair.
     *
     * @param int $conditionid
     * @param int $userid
     * @return string
     */
    private static function condition_key(int $conditionid, int $userid): string {
        return 'c' . $conditionid . 'u' . $userid;
    }
}
