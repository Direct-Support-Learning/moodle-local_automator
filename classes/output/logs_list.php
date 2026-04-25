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
 * Logs list renderable.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\output;

defined('MOODLE_INTERNAL') || die();

use local_automator\tenant_helper;

/**
 * Provides data for logs_list.mustache.
 */
class logs_list implements \renderable, \templatable {

    /** @var array */
    private array $logs;

    /** @var int */
    private int $totalcount;

    /** @var int */
    private int $page;

    /** @var int */
    private int $perpage;

    /**
     * Constructor.
     *
     * @param array $logs
     * @param int $totalcount
     * @param int $page
     * @param int $perpage
     */
    public function __construct(array $logs, int $totalcount, int $page = 0, int $perpage = 50) {
        $this->logs       = $logs;
        $this->totalcount = $totalcount;
        $this->page       = $page;
        $this->perpage    = $perpage;
    }

    /**
     * {@inheritdoc}
     */
    public function export_for_template(\renderer_base $output): array {
        $rows = [];
        foreach ($this->logs as $log) {
            $rows[] = [
                'rulename'    => $log->rulename ?? '',
                'scope'       => $log->tenantid
                    ? get_string('scope_tenant', 'local_automator', tenant_helper::get_tenant_name((int) $log->tenantid))
                    : get_string('scope_sitewide', 'local_automator'),
                'username'    => fullname($log),
                'status'      => $log->status,
                'issuccess'   => $log->status === 'success',
                'message'     => $log->message ?? '',
                'timecreated' => userdate($log->timecreated),
            ];
        }

        return [
            'logs'      => $rows,
            'haslogs'   => !empty($rows),
            'nologsmsg' => get_string('nologs', 'local_automator'),
        ];
    }
}
