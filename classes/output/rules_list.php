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
 * Rules list renderable.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\output;

defined('MOODLE_INTERNAL') || die();

use local_automator\tenant_helper;

/**
 * Provides data for rules_list.mustache.
 */
class rules_list implements \renderable, \templatable {

    /** @var \stdClass[] */
    private array $rules;

    /**
     * Constructor.
     *
     * @param \stdClass[] $rules
     */
    public function __construct(array $rules) {
        $this->rules = $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function export_for_template(\renderer_base $output): array {
        $rows = [];
        foreach ($this->rules as $rule) {
            $scope = $rule->tenantid
                ? get_string('scope_tenant', 'local_automator', tenant_helper::get_tenant_name((int) $rule->tenantid))
                : get_string('scope_sitewide', 'local_automator');

            $editurl   = new \moodle_url('/local/automator/edit.php', ['id' => $rule->id]);
            $deleteurl = new \moodle_url('/local/automator/edit.php', [
                'action'   => 'delete',
                'id'       => $rule->id,
                'sesskey'  => sesskey(),
            ]);

            $rows[] = [
                'id'                => $rule->id,
                'name'              => $rule->name,
                'scope'             => $scope,
                'operator'          => $rule->conditionoperator,
                'enabled'           => (bool) $rule->enabled,
                'editurl'           => $editurl->out(false),
                'deleteurl'         => $deleteurl->out(false),
                'confirmdelete'     => get_string('confirmdeleterule', 'local_automator', $rule->name),
            ];
        }

        return [
            'rules'      => $rows,
            'hasrules'   => !empty($rows),
            'addnewurl'  => (new \moodle_url('/local/automator/edit.php'))->out(false),
            'addnewlabel' => get_string('addnewrule', 'local_automator'),
            'norulesmsg' => get_string('norules', 'local_automator'),
            'issiteadmin' => is_siteadmin(),
        ];
    }
}
