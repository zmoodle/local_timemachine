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

defined('MOODLE_INTERNAL') || die();

// English strings for local_timemachine.

$string['pluginname'] = 'Moodle Time Machine';
$string['timemachine:manage'] = 'Manage Moodle Time Machine';
$string['managebackups'] = 'Manage course backups';
$string['task_backup_courses'] = 'Run Moodle Time Machine backups';
$string['task_send_summary'] = 'Send Moodle Time Machine summary email';
$string['setting_categoryids'] = 'Categories to back up';
$string['setting_categoryids_desc'] = 'When set, all courses in the selected categories are included in automatic backups.';
$string['setting_ftpenabled'] = 'Enable FTP upload';
$string['setting_ftpenabled_desc'] = 'If enabled, each backup is also uploaded to the configured FTP server.';
$string['setting_ftphost'] = 'FTP host';
$string['setting_ftpport'] = 'FTP port';
$string['setting_ftpuser'] = 'FTP username';
$string['setting_ftppass'] = 'FTP password';
$string['setting_ftppath'] = 'FTP path';
$string['setting_ftppassive'] = 'Use passive mode';
$string['setting_verbose'] = 'Verbose logging';
$string['setting_verbose_desc'] = 'Include detailed progress messages and stack traces in scheduled task logs.';
$string['setting_retentionversions'] = 'Retention (versions per course)';
$string['setting_retentionversions_desc'] = 'Maximum number of backup versions to keep for each course. Older versions are deleted automatically. Default: 7.';
$string['setting_backup_users'] = 'Include users';
$string['setting_backup_role_assignments'] = 'Include role assignments';
$string['setting_backup_activities'] = 'Include activities';
$string['setting_backup_blocks'] = 'Include blocks';
$string['setting_backup_filters'] = 'Include filters';
$string['setting_backup_comments'] = 'Include comments';
$string['setting_backup_badges'] = 'Include badges';
$string['setting_backup_calendarevents'] = 'Include calendar events';
$string['setting_backup_userscompletion'] = 'Include users completion';
$string['setting_notifyonfail'] = 'Notify on repeated failures';
$string['setting_notifyfailthreshold'] = 'Failure notification threshold';
$string['setting_notifyfailthreshold_desc'] = 'After this many consecutive backup failures for a course, email the site admin with error details.';
$string['searchcourses'] = 'Search courses';
$string['backupdate'] = 'Backup date';
$string['size'] = 'Size';
$string['deletecourseall'] = 'Delete all for course';
$string['privacy:metadata'] = 'Moodle Time Machine does not store personal data.';
$string['backupfailed'] = 'Backup failed.';
$string['expandversions'] = 'Show all versions';
$string['collapseversions'] = 'Hide versions';

// Logs UI.
$string['log_title'] = 'Backup failure logs';
$string['log_since'] = 'Since (UNIX timestamp)';
$string['log_courseid'] = 'Course ID';
$string['log_empty'] = 'No log entries for the selected filter.';
$string['log_time'] = 'Time';
$string['log_course'] = 'Course';

// Summary email.
$string['summary_email_subject'] = 'Moodle Time Machine: daily backup summary';
$string['summary_email_since'] = 'Period start: {$a}';
$string['summary_email_successes'] = 'Successful backups: {$a}';
$string['summary_email_totalmb'] = 'Total size (MB): {$a}';
$string['summary_email_fail_header'] = 'Courses with failed backups:';
$string['summary_email_no_fail'] = 'No failed backups in this period.';
$string['summary_email_loglink'] = 'View detailed logs: {$a}';

// UI stats and confirmations.
$string['stat_courses'] = 'Courses backed up';
$string['stat_totalsize'] = 'Total disk usage';
$string['stat_since_last'] = 'Backups since last summary ({$a})';
$string['stat_never'] = 'never sent';
$string['stat_versions'] = 'Backup versions: {$a}';
$string['stat_courses_detail'] = 'Courses with backups: {$a}';
$string['confirm_delete_backup'] = 'Delete this backup file? This cannot be undone.';
$string['confirm_delete_course'] = 'Delete ALL backups for this course? This cannot be undone.';
$string['clearsearch'] = 'Clear';
