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
 * Admin UI for Moodle Time Machine.
 *
 * Lists backups grouped by course and allows secure download/deletion.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_capability('local/timemachine:manage', context_system::instance());
admin_externalpage_setup('local_timemachine_manage');

$search = optional_param('search', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_ALPHA);
$backupid = optional_param('backupid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$search = trim($search);

$pageurl = new moodle_url('/local/timemachine/index.php', ['search' => $search]);

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('pluginname', 'local_timemachine'));
$PAGE->set_title(get_string('managebackups', 'local_timemachine'));
$PAGE->requires->js_call_amd('local_timemachine/index', 'init');

// Stats for dashboard strip.
$distinctcourses = (int)$DB->get_field_sql('SELECT COUNT(DISTINCT courseid) FROM {local_timemachine_backup}');
$storagestats = \local_timemachine\local\backupper::get_storage_stats();
$totalversions = $storagestats['totalversions'];
$totalbytes = $storagestats['totalbytes'];
$lastsummary = (int)get_config('local_timemachine', 'lastsummarysent');
$since = $lastsummary > 0 ? $lastsummary : (time() - DAYSECS);
$backupsince = (int)$DB->get_field_sql(
    'SELECT COUNT(1) FROM {local_timemachine_backup} WHERE timecreated >= :since',
    ['since' => $since]
);
$sincelabel = $lastsummary > 0 ? userdate($lastsummary) : get_string('stat_never', 'local_timemachine');

// Handle actions.
if ($action === 'download' && $backupid) {
    require_sesskey();
    $rec = $DB->get_record('local_timemachine_backup', ['id' => $backupid], '*', MUST_EXIST);
    if (
        !empty($rec->filepath) &&
        \local_timemachine\local\backupper::is_within_storage($rec->filepath) &&
        is_readable($rec->filepath)
    ) {
        send_file($rec->filepath, basename($rec->filepath), 0, 0, false, true);
    } else {
        throw new moodle_exception('filenotfound', 'error');
    }
    exit;
}

if ($action === 'deletebackup' && $backupid) {
    require_sesskey();
    $rec = $DB->get_record('local_timemachine_backup', ['id' => $backupid], '*', MUST_EXIST);
    \local_timemachine\local\backupper::delete_backup_file($rec->filepath ?? '');
    $DB->delete_records('local_timemachine_backup', ['id' => $backupid]);
    redirect($pageurl);
}

if ($action === 'deletecourse' && $courseid) {
    require_sesskey();
    $backups = $DB->get_records('local_timemachine_backup', ['courseid' => $courseid]);
    foreach ($backups as $b) {
        \local_timemachine\local\backupper::delete_backup_file($b->filepath ?? '');
        $DB->delete_records('local_timemachine_backup', ['id' => $b->id]);
    }
    $DB->delete_records('local_timemachine_course', ['courseid' => $courseid]);
    redirect($pageurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managebackups', 'local_timemachine'));

// Stats summary bar.
echo html_writer::start_div('tm-stats row');
$cards = [
    [
        'label' => get_string('stat_backups_generated', 'local_timemachine'),
        'value' => $totalversions,
        'detail' => get_string('stat_courses_detail', 'local_timemachine', $distinctcourses),
    ],
    [
        'label' => get_string('stat_totalsize', 'local_timemachine'),
        'value' => display_size($totalbytes),
        'detail' => '',
    ],
    [
        'label' => get_string('stat_since_last', 'local_timemachine', $sincelabel),
        'value' => $backupsince,
        'detail' => '',
    ],
];
foreach ($cards as $card) {
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('tm-stat-card');
    echo html_writer::div(s($card['label']), 'tm-stat-label');
    echo html_writer::div(s($card['value']), 'tm-stat-value');
    if (!empty($card['detail'])) {
        echo html_writer::div(s($card['detail']), 'tm-stat-detail');
    }
    echo html_writer::end_div();
    echo html_writer::end_div();
}
echo html_writer::end_div();

// Search form.
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $pageurl->out(false), 'class' => 'tm-search']);
echo html_writer::start_tag('div', ['class' => 'form-inline']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'search',
    'value' => s($search),
    'placeholder' => get_string('searchcourses', 'local_timemachine'),
    'class' => 'form-control',
]);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('search'),
    'class' => 'btn btn-primary ml-2',
]);
echo html_writer::link(new moodle_url($pageurl, ['search' => '']), get_string('clearsearch', 'local_timemachine'), [
    'class' => 'btn btn-secondary ml-2',
]);
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

// Fetch rows: show one row per backup version.
$params = [];
$wheres = [];
if ($search !== '') {
    $escaped = $DB->sql_like_escape($search);
    $wheresql1 = $DB->sql_like('c.fullname', ':s1', false, false);
    $wheresql2 = $DB->sql_like('c.shortname', ':s2', false, false);
    $wheres[] = '(' . $wheresql1 . ' OR ' . $wheresql2 . ')';
    $params['s1'] = '%' . $escaped . '%';
    $params['s2'] = '%' . $escaped . '%';
}
$where = $wheres ? ('WHERE ' . implode(' AND ', $wheres)) : '';

$sql = "
    SELECT b.id AS bid, b.filepath, b.filesize, b.timecreated,
           c.id AS courseid, c.fullname, c.shortname, cat.name AS category
      FROM {local_timemachine_backup} b
      JOIN {course} c ON c.id = b.courseid
      JOIN {course_categories} cat ON cat.id = c.category
      $where
      ORDER BY c.shortname ASC, b.timecreated DESC
";
$rows = $DB->get_records_sql($sql, $params);

// Group rows by course id keeping backups in descending time.
$groups = [];
foreach ($rows as $r) {
    if (!isset($groups[$r->courseid])) {
        $groups[$r->courseid] = (object) [
            'course' => $r,
            'backups' => [],
        ];
    }
    $groups[$r->courseid]->backups[] = $r;
}

// Render table.
$table = new html_table();
$table->head = [
    '',
    get_string('category'),
    get_string('course'),
    get_string('shortname'),
    get_string('backupdate', 'local_timemachine'),
    get_string('size', 'local_timemachine'),
    get_string('actions'),
];

foreach ($groups as $cid => $g) {
    $backups = $g->backups;
    if (empty($backups)) {
        continue;
    }
    $latest = $backups[0];
    $count = count($backups);
    $toggle = '';
    $hue = ($cid * 57) % 360; // Stable tint per course.
    $mainstyle = 'background-color:hsl(' . $hue . ',60%,96%);border-left-color:hsl(' . $hue . ',60%,60%)';
    $childstyle = 'background-color:hsl(' . $hue . ',60%,99%)';
    if ($count > 1) {
        $expandtitle = get_string('expandversions', 'local_timemachine');
        $collapsetitle = get_string('collapseversions', 'local_timemachine');
        $icon = $OUTPUT->pix_icon('t/collapsed', $expandtitle, 'moodle', ['class' => 'icon']);
        $collapsedurl = $OUTPUT->image_url('t/collapsed', 'moodle')->out(false);
        $expandedurl = $OUTPUT->image_url('t/expanded', 'moodle')->out(false);
        $toggle = html_writer::link('#', $icon, [
            'class' => 'tm-toggle',
            'data-courseid' => $cid,
            'aria-expanded' => 'false',
            'title' => $expandtitle,
            'data-title-expand' => $expandtitle,
            'data-title-collapse' => $collapsetitle,
            'data-icon-collapsed' => $collapsedurl,
            'data-icon-expanded' => $expandedurl,
            'data-icon-class-collapsed' => 'fa-chevron-right',
            'data-icon-class-expanded' => 'fa-chevron-down',
        ]);
    }

    $downloadurl = new moodle_url($pageurl, ['action' => 'download', 'backupid' => $latest->bid, 'sesskey' => sesskey()]);
    $deleteburl = new moodle_url($pageurl, ['action' => 'deletebackup', 'backupid' => $latest->bid, 'sesskey' => sesskey()]);
    $deletecurl = new moodle_url($pageurl, ['action' => 'deletecourse', 'courseid' => $cid, 'sesskey' => sesskey()]);
    $actions = html_writer::link($downloadurl, get_string('download')) . ' | ' .
               html_writer::link($deleteburl, get_string('delete'), [
                   'class' => 'tm-confirm',
                   'data-confirm' => get_string('confirm_delete_backup', 'local_timemachine'),
               ]) . ' | ' .
               html_writer::link($deletecurl, get_string('deletecourseall', 'local_timemachine'), [
                   'class' => 'tm-confirm',
                   'data-confirm' => get_string('confirm_delete_course', 'local_timemachine'),
               ]);

    $courseurl = new moodle_url('/course/view.php', ['id' => $cid]);

    $row = new html_table_row([
        new html_table_cell($toggle),
        new html_table_cell(format_string($latest->category)),
        new html_table_cell(format_string($latest->fullname) . ($count > 1 ? ' (' . $count . ')' : '')),
        new html_table_cell(html_writer::link($courseurl, s($latest->shortname), [
            'target' => '_blank',
            'rel' => 'noopener',
        ])),
        new html_table_cell(userdate($latest->timecreated)),
        new html_table_cell($latest->filesize ? display_size($latest->filesize) : '-'),
        new html_table_cell($actions),
    ]);
    $row->attributes['class'] = 'tm-main';
    $row->attributes['style'] = $mainstyle;
    $table->data[] = $row;

    // Older versions as hidden child rows.
    for ($i = 1; $i < $count; $i++) {
        $r = $backups[$i];
        $downloadurl = new moodle_url($pageurl, ['action' => 'download', 'backupid' => $r->bid, 'sesskey' => sesskey()]);
        $deleteburl = new moodle_url($pageurl, ['action' => 'deletebackup', 'backupid' => $r->bid, 'sesskey' => sesskey()]);
        $actions = html_writer::link($downloadurl, get_string('download')) . ' | ' .
                   html_writer::link($deleteburl, get_string('delete'), [
                       'class' => 'tm-confirm',
                       'data-confirm' => get_string('confirm_delete_backup', 'local_timemachine'),
                   ]);
        $childname = html_writer::span('&rarr;', 'tm-arrow', ['aria-hidden' => 'true']) .
            ' ' . format_string($r->fullname);
        $childcourseurl = new moodle_url('/course/view.php', ['id' => $cid]);
        $child = new html_table_row([
            new html_table_cell(''),
            new html_table_cell(''),
            new html_table_cell($childname),
            new html_table_cell(html_writer::link($childcourseurl, s($r->shortname), [
                'target' => '_blank',
                'rel' => 'noopener',
            ])),
            new html_table_cell(userdate($r->timecreated)),
            new html_table_cell($r->filesize ? display_size($r->filesize) : '-'),
            new html_table_cell($actions),
        ]);
        $child->attributes['class'] = 'tm-child tm-child-row';
        $child->attributes['data-parent'] = $cid;
        $child->attributes['style'] = $childstyle;
        $table->data[] = $child;
    }
}

if (empty($table->data)) {
    echo html_writer::div(get_string('nothingtodisplay'), 'alert alert-info');
} else {
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
