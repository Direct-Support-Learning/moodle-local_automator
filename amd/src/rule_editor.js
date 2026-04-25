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
 * AMD module for the rule editor page.
 *
 * Handles:
 *   - Add condition/action dropdown navigation
 *   - Delete confirmation dialogs
 *
 * @module     local_automator/rule_editor
 * @copyright  2026 DSL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    /**
     * Navigate to the selected condition or action add URL.
     *
     * @param {Event} e
     */
    var handleTypeSelect = function(e) {
        var url = $(e.target).val();
        if (url) {
            window.location.href = url;
        }
    };

    /**
     * Show a native confirm dialog before following a delete link.
     *
     * @param {Event} e
     */
    var handleDeleteConfirm = function(e) {
        var message = $(e.currentTarget).data('confirm');
        if (message && !window.confirm(message)) {
            e.preventDefault();
        }
    };

    return {
        /**
         * Initialise the rule editor page.
         */
        init: function() {
            // Condition/action type dropdowns.
            $(document).on('change', '[data-action="add-condition"], [data-action="add-action"]', handleTypeSelect);

            // Delete confirmation links.
            $(document).on('click', '.local-automator-confirm-delete, .local-automator-delete-rule', handleDeleteConfirm);
        }
    };
});
