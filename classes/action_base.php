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
 * Abstract base class for rule actions.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class that all action types must extend.
 */
abstract class action_base {

    /** @var array Configuration data for this action instance. */
    protected array $config = [];

    /**
     * Constructor.
     *
     * @param array $config Decoded configdata from database.
     */
    public function __construct(array $config = []) {
        $this->config = $config;
    }

    /**
     * Execute this action for a user.
     *
     * @param int $userid The user the rule matched for.
     * @return void
     */
    abstract public function execute(int $userid): void;

    /**
     * Return a plain-text summary of this action's configuration.
     *
     * @return string
     */
    abstract public function get_config_summary(): string;

    /**
     * Return the human-readable name of this action type.
     *
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Return the short type identifier.
     *
     * @return string
     */
    public static function get_type(): string {
        $class = static::class;
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * Factory: create an action instance from stored type and configdata.
     *
     * @param string $type
     * @param string $configdata JSON-encoded config.
     * @return action_base|null
     */
    public static function create(string $type, string $configdata = ''): ?action_base {
        $classname = '\\local_automator\\actions\\' . $type;
        if (!class_exists($classname)) {
            return null;
        }
        $config = $configdata ? json_decode($configdata, true) : [];
        return new $classname($config ?? []);
    }

    /**
     * Discover all available action types by scanning the actions directory.
     *
     * @return array Array of ['type' => string, 'name' => string] sorted by name.
     */
    public static function get_all(): array {
        global $CFG;

        $dir = $CFG->dirroot . '/local/automator/classes/actions';
        if (!is_dir($dir)) {
            return [];
        }

        $types = [];
        foreach (glob($dir . '/*.php') as $file) {
            $type = basename($file, '.php');
            $classname = '\\local_automator\\actions\\' . $type;
            if (class_exists($classname)) {
                $types[] = [
                    'type' => $type,
                    'name' => $classname::get_name(),
                ];
            }
        }

        usort($types, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $types;
    }

    /**
     * Get config value with optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get_config(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }
}
