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
 * Log viewer for Moodle Time Machine.
 *
 * @package   local_timemachine
 * @copyright 2025 zMoodle (https://app.zmoodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/timemachine:manage', context_system::instance());
admin_externalpage_setup('local_timemachine_manage');

$since = max(0, (int)optional_param('since', 0, PARAM_INT));
$courseid = max(0, (int)optional_param('courseid', 0, PARAM_INT));

$pageurl = new moodle_url('/local/timemachine/log.php', ['since' => $since, 'courseid' => $courseid]);

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('pluginname', 'local_timemachine'));
$PAGE->set_title(get_string('log_title', 'local_timemachine'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('log_title', 'local_timemachine'));

// Filter form.
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $pageurl->out(false)]);
echo html_writer::start_tag('div', ['class' => 'form-inline']);
echo html_writer::label(get_string('log_since', 'local_timemachine'), 'since');
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'since',
    'id' => 'since',
    'value' => (int)$since,
    'class' => 'form-control ml-2',
    'style' => 'max-width:200px'
]);
echo html_writer::label(get_string('log_courseid', 'local_timemachine'), 'courseid', false, ['class' => 'ml-3']);
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'courseid',
    'id' => 'courseid',
    'value' => (int)$courseid,
    'class' => 'form-control ml-2',
    'style' => 'max-width:200px'
]);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('search'),
    'class' => 'btn btn-primary ml-3'
]);
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

$params = [];
$where = '1=1';
if ($since > 0) { $where .= ' AND l.timecreated > :t'; $params['t'] = $since; }
if ($courseid > 0) { $where .= ' AND l.courseid = :cid'; $params['cid'] = $courseid; }

$sql = "
    SELECT l.id, l.timecreated, l.courseid, l.level, l.message, l.details, l.runid,
           c.fullname AS coursename
      FROM {local_timemachine_log} l
 LEFT JOIN {course} c ON c.id = l.courseid
     WHERE $where
  ORDER BY l.timecreated DESC, l.id DESC
";

$logs = $DB->get_records_sql($sql, $params);

if (!$logs) {
    echo html_writer::div(get_string('log_empty', 'local_timemachine'), 'alert alert-info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('log_time', 'local_timemachine'),
        get_string('log_course', 'local_timemachine'),
        get_string('level'),
        get_string('message'),
        get_string('details')
    ];
    foreach ($logs as $l) {
        $time = userdate((int)$l->timecreated, '%Y-%m-%d %H:%M:%S');
        $course = $l->coursename ? $l->coursename . ' (ID ' . $l->courseid . ')' : ('ID ' . $l->courseid);
        $details = '';
        if (!empty($l->details)) {
            $details = html_writer::tag('pre', s($l->details), ['style' => 'max-height:200px;overflow:auto']);
        }
        $table->data[] = [
            s($time),
            s($course),
            s($l->level),
            s($l->message),
            $details,
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
