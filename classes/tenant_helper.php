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
 * Tenant resolution helper.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves MuTMS tenant information for users.
 *
 * All methods degrade gracefully when tool_mutenancy is not installed,
 * returning null / empty values so the plugin works in non-MuTMS environments.
 */
class tenant_helper {

    /**
     * Whether tool_mutenancy is installed and its tenancy class is available.
     *
     * @return bool
     */
    public static function is_mutenancy_available(): bool {
        return class_exists('\tool_mutenancy\local\tenancy');
    }

    /**
     * Get the tenant ID for the current session user.
     *
     * Returns null for site admins, untenanted users, and non-MuTMS installs.
     *
     * @return int|null
     */
    public static function get_current_tenantid(): ?int {
        if (!self::is_mutenancy_available()) {
            return null;
        }
        return \tool_mutenancy\local\tenancy::get_current_tenantid();
    }

    /**
     * Get the tenant ID for a specific user by querying cohort membership.
     *
     * @param int $userid
     * @return int|null
     */
    public static function get_user_tenantid(int $userid): ?int {
        global $DB;

        if (!self::is_mutenancy_available()) {
            return null;
        }

        $cache = \cache::make('local_automator', 'usertenants');
        $cached = $cache->get('user_' . $userid);
        if ($cached !== false) {
            return $cached === 0 ? null : (int) $cached;
        }

        $sql = 'SELECT t.id
                  FROM {tool_mutenancy_tenant} t
                  JOIN {cohort_members} cm ON cm.cohortid = t.cohortid
                 WHERE cm.userid = :userid
                 LIMIT 1';

        $record = $DB->get_record_sql($sql, ['userid' => $userid]);
        $tenantid = $record ? (int) $record->id : null;

        // Store 0 to represent null (cache can't store null).
        $cache->set('user_' . $userid, $tenantid ?? 0);

        return $tenantid;
    }

    /**
     * Get the tenant name by ID.
     *
     * @param int|null $tenantid
     * @return string Empty string if no tenant or MuTMS not installed.
     */
    public static function get_tenant_name(?int $tenantid): string {
        global $DB;

        if ($tenantid === null || !self::is_mutenancy_available()) {
            return '';
        }

        $tenant = $DB->get_record('tool_mutenancy_tenant', ['id' => $tenantid], 'name');
        return $tenant ? $tenant->name : '';
    }

    /**
     * Get all tenants as id => name array.
     *
     * Returns empty array when MuTMS is not installed.
     *
     * @return array
     */
    public static function get_all_tenants(): array {
        global $DB;

        if (!self::is_mutenancy_available()) {
            return [];
        }

        $tenants = $DB->get_records('tool_mutenancy_tenant', null, 'name ASC', 'id, name');
        $result = [];
        foreach ($tenants as $tenant) {
            $result[$tenant->id] = $tenant->name;
        }
        return $result;
    }

    /**
     * Check if the current user is a tenant manager (not a site admin).
     *
     * Always returns false when MuTMS is not installed.
     *
     * @return bool
     */
    public static function is_tenant_manager(): bool {
        return !is_siteadmin() && self::get_current_tenantid() !== null;
    }
}
