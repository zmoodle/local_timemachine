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
 * Adhoc task enforcing retention limits across all courses.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enforce_retention extends \core\task\adhoc_task {
    /**
     * Execute retention enforcement.
     *
     * @return void
     */
    public function execute() {
        global $DB;
        $keep = (int)\get_config('local_timemachine', 'retentionversions');
        if ($keep <= 0) {
            $keep = \local_timemachine\local\backupper::MAX_VERSIONS;
        }
        mtrace('local_timemachine: ' . get_string('task_enforce_retention_start', 'local_timemachine', $keep));
        $sql = 'SELECT DISTINCT courseid FROM {local_timemachine_backup}';
        $courseids = $DB->get_records_sql($sql);
        foreach ($courseids as $c) {
            try {
                \local_timemachine\local\backupper::enforce_retention((int)$c->courseid);
            } catch (\Throwable $e) {
                mtrace('local_timemachine: ' . get_string('task_enforce_retention_error', 'local_timemachine', (object)[
                    'courseid' => $c->courseid,
                    'message' => $e->getMessage(),
                ]));
            }
        }
    }
}
