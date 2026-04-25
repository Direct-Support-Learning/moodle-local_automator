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
 * Rule edit page renderable.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\output;

defined('MOODLE_INTERNAL') || die();

use local_automator\condition_base;
use local_automator\action_base;

/**
 * Provides data for conditions_section.mustache and actions_section.mustache.
 */
class rule_edit_page implements \renderable, \templatable {

    /** @var \stdClass */
    private \stdClass $rule;

    /** @var \stdClass[] */
    private array $conditions;

    /** @var \stdClass[] */
    private array $actions;

    /**
     * Constructor.
     *
     * @param \stdClass $rule
     * @param \stdClass[] $conditions
     * @param \stdClass[] $actions
     */
    public function __construct(\stdClass $rule, array $conditions, array $actions) {
        $this->rule       = $rule;
        $this->conditions = $conditions;
        $this->actions    = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function export_for_template(\renderer_base $output): array {
        $condrows = [];
        foreach ($this->conditions as $cond) {
            $instance = condition_base::create($cond->type, (string) $cond->configdata);
            $summary  = $instance ? $instance->get_config_summary() : $cond->type;

            $editurl   = new \moodle_url('/local/automator/edit_condition.php', [
                'ruleid' => $this->rule->id,
                'id'     => $cond->id,
            ]);
            $deleteurl = new \moodle_url('/local/automator/delete_condition.php', [
                'ruleid'  => $this->rule->id,
                'id'      => $cond->id,
                'sesskey' => sesskey(),
            ]);

            $condrows[] = [
                'id'        => $cond->id,
                'type'      => $cond->type,
                'summary'   => $summary,
                'editurl'   => $editurl->out(false),
                'deleteurl' => $deleteurl->out(false),
            ];
        }

        $actionrows = [];
        foreach ($this->actions as $act) {
            $instance = action_base::create($act->type, (string) $act->configdata);
            $summary  = $instance ? $instance->get_config_summary() : $act->type;

            $editurl   = new \moodle_url('/local/automator/edit_action.php', [
                'ruleid' => $this->rule->id,
                'id'     => $act->id,
            ]);
            $deleteurl = new \moodle_url('/local/automator/delete_action.php', [
                'ruleid'  => $this->rule->id,
                'id'      => $act->id,
                'sesskey' => sesskey(),
            ]);

            $actionrows[] = [
                'id'        => $act->id,
                'type'      => $act->type,
                'summary'   => $summary,
                'editurl'   => $editurl->out(false),
                'deleteurl' => $deleteurl->out(false),
            ];
        }

        $condtypes = [];
        foreach (condition_base::get_all() as $ct) {
            $condtypes[] = [
                'type' => $ct['type'],
                'name' => $ct['name'],
                'url'  => (new \moodle_url('/local/automator/add_condition.php', [
                    'ruleid' => $this->rule->id,
                    'type'   => $ct['type'],
                ]))->out(false),
            ];
        }

        $actiontypes = [];
        foreach (action_base::get_all() as $at) {
            $actiontypes[] = [
                'type' => $at['type'],
                'name' => $at['name'],
                'url'  => (new \moodle_url('/local/automator/add_action.php', [
                    'ruleid' => $this->rule->id,
                    'type'   => $at['type'],
                ]))->out(false),
            ];
        }

        $isnewrule = empty($this->rule->id);

        return [
            'ruleid'            => $this->rule->id,
            'isnewrule'         => $isnewrule,
            'conditions'        => $condrows,
            'hasconditions'     => !empty($condrows),
            'actions'           => $actionrows,
            'hasactions'        => !empty($actionrows),
            'conditiontypes'    => $condtypes,
            'actiontypes'       => $actiontypes,
            'selectcondlabel'   => get_string('selectconditiontype', 'local_automator'),
            'selectactionlabel' => get_string('selectactiontype', 'local_automator'),
            'noconditionsmsg'   => get_string('noconditions', 'local_automator'),
            'noactionsmsg'      => get_string('noactions', 'local_automator'),
            'conditionslabel'   => get_string('conditions_section', 'local_automator'),
            'actionslabel'      => get_string('actions_section', 'local_automator'),
            'saverulefirstmsg'  => get_string('saverulefirst', 'local_automator'),
        ];
    }
}
