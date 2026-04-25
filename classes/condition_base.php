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
 * Abstract base class for rule conditions.
 *
 * @package   local_automator
 * @copyright 2026 DSL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_automator;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class that all condition types must extend.
 */
abstract class condition_base {

    /** @var array Configuration data for this condition instance. */
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
     * Evaluate this condition for a user.
     *
     * @param int $userid
     * @return bool True if the condition is met.
     */
    abstract public function check(int $userid): bool;

    /**
     * Return a plain-text summary of this condition's configuration.
     *
     * @return string
     */
    abstract public function get_config_summary(): string;

    /**
     * Return the human-readable name of this condition type.
     *
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Return the short type identifier (matches the class filename without .php).
     *
     * @return string
     */
    public static function get_type(): string {
        $class = static::class;
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * Factory: create a condition instance from stored type and configdata strings.
     *
     * @param string $type
     * @param string $configdata JSON-encoded config.
     * @return condition_base|null
     */
    public static function create(string $type, string $configdata = ''): ?condition_base {
        $classname = '\\local_automator\\conditions\\' . $type;
        if (!class_exists($classname)) {
            return null;
        }
        $config = $configdata ? json_decode($configdata, true) : [];
        return new $classname($config ?? []);
    }

    /**
     * Discover all available condition types by scanning the conditions directory.
     *
     * @return array Array of ['type' => string, 'name' => string] sorted by name.
     */
    public static function get_all(): array {
        global $CFG;

        $dir = $CFG->dirroot . '/local/automator/classes/conditions';
        if (!is_dir($dir)) {
            return [];
        }

        $types = [];
        foreach (glob($dir . '/*.php') as $file) {
            $type = basename($file, '.php');
            $classname = '\\local_automator\\conditions\\' . $type;
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
