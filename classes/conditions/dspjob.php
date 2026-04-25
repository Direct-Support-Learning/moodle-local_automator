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
 * DSP Job condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\conditions;

defined('MOODLE_INTERNAL') || die();

use local_automator\condition_base;

/**
 * Checks whether a user has a specific active DSP job type.
 */
class dspjob extends condition_base {

    /**
     * {@inheritdoc}
     */
    public static function get_name(): string {
        return get_string('condition_dspjob', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function check(int $userid): bool {
        global $DB;

        $job      = $this->get_config('job', '');
        $operator = $this->get_config('operator', 'has');

        if (!$job) {
            return false;
        }

        $sql = 'SELECT uid.data
                  FROM {user_info_data} uid
                  JOIN {user_info_field} uif ON uif.id = uid.fieldid
                 WHERE uif.shortname = :shortname AND uid.userid = :userid';

        $record = $DB->get_record_sql($sql, ['shortname' => 'dsp_job', 'userid' => $userid]);

        if (!$record || empty($record->data)) {
            return $operator === 'hasnot';
        }

        // JSON_CONTAINS is MySQL-specific. Use PHP fallback for portability.
        $jobs = json_decode($record->data, true);
        if (!is_array($jobs)) {
            return $operator === 'hasnot';
        }

        $hasjob = in_array($job, $jobs);
        return $operator === 'has' ? $hasjob : !$hasjob;
    }

    /**
     * {@inheritdoc}
     */
    public function get_config_summary(): string {
        $job      = $this->get_config('job', '');
        $operator = $this->get_config('operator', 'has');
        return get_string('condition_dspjob_summary', 'local_automator', (object) [
            'job'      => $job,
            'operator' => get_string('job_' . $operator, 'local_automator'),
        ]);
    }

    /**
     * Get available DSP job options from the dsp_job custom field configdata.
     *
     * @return array shortname => label
     */
    public static function get_job_options(): array {
        global $DB;

        $field = $DB->get_record('user_info_field', ['shortname' => 'dsp_job'], 'configdata');
        if (!$field || empty($field->configdata)) {
            return [];
        }

        $config = unserialize($field->configdata);
        if (!isset($config['options']) || !is_array($config['options'])) {
            return [];
        }

        $options = [];
        foreach ($config['options'] as $option) {
            $shortname = trim($option);
            if ($shortname !== '') {
                $options[$shortname] = $shortname;
            }
        }

        return $options;
    }
}
