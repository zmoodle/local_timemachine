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
 * Library callbacks for local_timemachine.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Triggered when retention setting changes.
 *
 * @param string $name
 * @param mixed $value
 */
function local_timemachine_retention_updated($name, $value) {
    $task = new \local_timemachine\task\enforce_retention();
    $task->set_component('local_timemachine');
    \core\task\manager::queue_adhoc_task($task, true);
}
