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
 * Profile field condition.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator\conditions;

defined('MOODLE_INTERNAL') || die();

use local_automator\condition_base;

/**
 * Checks a standard or custom user profile field against a value.
 */
class profilefield extends condition_base {

    /** @var string[] Standard user table fields. */
    const STANDARD_FIELDS = [
        'firstname', 'lastname', 'email', 'city', 'country',
        'department', 'institution', 'phone1', 'phone2', 'idnumber',
    ];

    /**
     * {@inheritdoc}
     */
    public static function get_name(): string {
        return get_string('condition_profilefield', 'local_automator');
    }

    /**
     * {@inheritdoc}
     */
    public function check(int $userid): bool {
        global $DB;

        $field    = $this->get_config('field', '');
        $operator = $this->get_config('operator', 'equals');
        $value    = $this->get_config('value', '');

        if (!$field) {
            return false;
        }

        $fieldvalue = $this->get_field_value($userid, $field);

        return $this->compare($fieldvalue, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get_config_summary(): string {
        $field    = $this->get_config('field', '');
        $operator = $this->get_config('operator', 'equals');
        $value    = $this->get_config('value', '');
        return get_string('condition_profilefield_summary', 'local_automator', (object) [
            'field'    => $field,
            'operator' => get_string('field_' . $operator, 'local_automator'),
            'value'    => $value,
        ]);
    }

    /**
     * Retrieve the field value for a user.
     *
     * @param int $userid
     * @param string $field
     * @return string
     */
    private function get_field_value(int $userid, string $field): string {
        global $DB;

        if (in_array($field, self::STANDARD_FIELDS)) {
            $user = $DB->get_record('user', ['id' => $userid], $field);
            return $user ? (string) $user->$field : '';
        }

        // Custom profile field.
        $sql = 'SELECT uid.data
                  FROM {user_info_data} uid
                  JOIN {user_info_field} uif ON uif.id = uid.fieldid
                 WHERE uif.shortname = :shortname AND uid.userid = :userid';

        $record = $DB->get_record_sql($sql, ['shortname' => $field, 'userid' => $userid]);
        return $record ? (string) $record->data : '';
    }

    /**
     * Compare a field value against an operator and target value.
     *
     * @param string $fieldvalue
     * @param string $operator
     * @param string $value
     * @return bool
     */
    private function compare(string $fieldvalue, string $operator, string $value): bool {
        switch ($operator) {
            case 'equals':
                return $fieldvalue === $value;
            case 'notequals':
                return $fieldvalue !== $value;
            case 'contains':
                return strpos($fieldvalue, $value) !== false;
            case 'isempty':
                return $fieldvalue === '';
            case 'isnotempty':
                return $fieldvalue !== '';
            default:
                return false;
        }
    }
}
