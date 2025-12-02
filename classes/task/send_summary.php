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

namespace local_timemachine\task;

/**
 * Scheduled task that sends the daily backup summary email.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_summary extends \core\task\scheduled_task {
    /**
     * Task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_send_summary', 'local_timemachine');
    }

    /**
     * Execute daily summary sending.
     *
     * @return void
     */
    public function execute() {
        try {
            \local_timemachine\local\backupper::send_daily_summary();
        } catch (\Throwable $e) {
            mtrace('local_timemachine: ' . get_string('task_error_send_summary', 'local_timemachine', $e->getMessage()));
            if ((int)\get_config('local_timemachine', 'verbose')) {
                mtrace($e->getTraceAsString());
            }
        }
    }
}
