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

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task to back up a single course.
 *
 * @package   local_timemachine
 * @copyright 2025 zMoodle (https://app.zmoodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_single_course extends \core\task\adhoc_task {
    public function execute() {
        $data = $this->get_custom_data();
        $courseid = isset($data->courseid) ? (int)$data->courseid : 0;
        $runid = isset($data->runid) ? (string)$data->runid : null;
        if (!$courseid) {
            mtrace('local_timemachine: adhoc task missing courseid');
            return;
        }
        try {
            \local_timemachine\local\backupper::maybe_backup_course($courseid);
        } catch (\Throwable $e) {
            mtrace('local_timemachine: adhoc error for course ' . $courseid . ': ' . $e->getMessage());
            if ((int)\get_config('local_timemachine', 'verbose')) {
                mtrace($e->getTraceAsString());
            }
            \local_timemachine\local\backupper::handle_failure($courseid, $e, $runid);
        }
    }
}
