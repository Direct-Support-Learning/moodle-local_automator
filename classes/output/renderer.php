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
 * Plugin renderer.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin renderer — delegates to mustache templates.
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render the rules list page.
     *
     * @param rules_list $renderable
     * @return string HTML
     */
    public function render_rules_list(rules_list $renderable): string {
        return $this->render_from_template(
            'local_automator/rules_list',
            $renderable->export_for_template($this)
        );
    }

    /**
     * Render the rule edit page (conditions + actions sections).
     *
     * @param rule_edit_page $renderable
     * @return string HTML
     */
    public function render_rule_edit_page(rule_edit_page $renderable): string {
        return $this->render_from_template(
            'local_automator/rule_edit',
            $renderable->export_for_template($this)
        );
    }

    /**
     * Render the logs list.
     *
     * @param logs_list $renderable
     * @return string HTML
     */
    public function render_logs_list(logs_list $renderable): string {
        return $this->render_from_template(
            'local_automator/logs_list',
            $renderable->export_for_template($this)
        );
    }
}
