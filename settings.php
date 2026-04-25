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
 * Plugin admin settings.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_automator', get_string('pluginname', 'local_automator'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_automator/enableeventobservers',
        get_string('setting_enableeventobservers', 'local_automator'),
        get_string('setting_enableeventobservers_desc', 'local_automator'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_automator/enablescheduledtask',
        get_string('setting_enablescheduledtask', 'local_automator'),
        get_string('setting_enablescheduledtask_desc', 'local_automator'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_automator/batchsize',
        get_string('setting_batchsize', 'local_automator'),
        get_string('setting_batchsize_desc', 'local_automator'),
        100,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_automator/cacheduration',
        get_string('setting_cacheduration', 'local_automator'),
        get_string('setting_cacheduration_desc', 'local_automator'),
        3600,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_automator/recheckinterval',
        get_string('setting_recheckinterval', 'local_automator'),
        get_string('setting_recheckinterval_desc', 'local_automator'),
        86400,
        PARAM_INT
    ));

    $settings->add(new admin_setting_heading(
        'local_automator/manageheading',
        get_string('manage_rules', 'local_automator'),
        html_writer::link(
            new moodle_url('/local/automator/index.php'),
            get_string('manage_rules', 'local_automator')
        )
    ));
}
