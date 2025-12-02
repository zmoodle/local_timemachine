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
 * Language strings for local_timemachine.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backupdate'] = 'Backup date';
$string['backupfailed'] = 'Backup failed.';
$string['clearsearch'] = 'Clear';
$string['collapseversions'] = 'Hide versions';
$string['confirm_delete_backup'] = 'Delete this backup file? This cannot be undone.';
$string['confirm_delete_course'] = 'Delete ALL backups for this course? This cannot be undone.';
$string['deletecourseall'] = 'Delete all for course';
$string['email_fail_course'] = 'Course: {$a}';
$string['email_fail_failures'] = 'Failures: {$a}';
$string['email_fail_subject'] = 'Moodle Time Machine: backup failed for course ID {$a}';
$string['email_fail_trace'] = 'Trace:';
$string['expandversions'] = 'Show all versions';
$string['log_course'] = 'Course';
$string['log_courseid'] = 'Course ID';
$string['log_course_with_id'] = '{$a->name} (ID {$a->id})';
$string['log_courseid_only'] = 'ID {$a}';
$string['log_empty'] = 'No log entries for the selected filter.';
$string['log_error_prefix'] = 'Error: {$a}';
$string['log_exception'] = 'Exception: {$a}';
$string['log_insert_failed'] = 'Log insert failed ({$a})';
$string['log_since'] = 'Since (UNIX timestamp)';
$string['log_backup_failed_course'] = 'Backup failed for course {$a}';
$string['log_backup_not_writable'] = 'Backup file is not writable and cannot be deleted';
$string['log_current_signature'] = 'Current signature: {$a}';
$string['log_time'] = 'Time';
$string['log_title'] = 'Backup failure logs';
$string['log_previous_signature'] = 'Previous signature: {$a}';
$string['log_signature_none'] = '(none)';
$string['log_signature_query_failed'] = 'Signature query ({$a}) failed';
$string['log_storage_unavailable'] = 'Storage directory unavailable';
$string['log_unable_resolve_storage_delete'] = 'Unable to resolve storage directory for deletion';
$string['log_skip_delete_outside'] = 'Skipping deletion of file outside storage directory';
$string['log_delete_failed'] = 'Failed to delete backup file from disk';
$string['log_no_category_selected'] = 'No category selected; skipping';
$string['log_selected_categories'] = 'Selected category ids: {$a}';
$string['log_found_courses'] = 'Found courses: {$a}';
$string['log_queue_check_course'] = 'Queue check for course id={$a->id} shortname={$a->shortname}';
$string['log_queue_error_course'] = 'Queue error for course {$a}';
$string['log_no_changes_skip'] = 'No changes since last backup; skipping queue';
$string['log_pending_adhoc'] = 'Already present in adhoc queue; skipping';
$string['log_recently_queued'] = 'Already queued recently; skipping';
$string['log_queued_task'] = 'Queued adhoc backup task';
$string['log_executing_backup'] = 'Executing backup plan...';
$string['log_backup_controller_destroy_failed'] = 'Backup controller destroy failed ({$a})';
$string['log_saved_file'] = 'Saved file to {$a->path} size={$a->size}';
$string['log_recorded_backup_entry'] = 'Recorded backup entry in DB';
$string['log_requeued_with_delay'] = 'Requeued adhoc task with delay {$a->delay}s (failcount={$a->failcount})';
$string['log_retention_deleting'] = 'Retention: deleting {$a} old backups';
$string['log_ftp_skip_missing'] = 'FTP upload skipped: backup file missing or outside storage directory';
$string['log_ftp_missing_functions'] = 'FTP functions not available in PHP';
$string['log_ftp_connect_failed'] = 'FTP connect failed';
$string['log_ftp_login_failed'] = 'FTP login failed';
$string['log_ftp_change_dir_failed'] = 'FTP change directory failed';
$string['log_ftp_open_failed'] = 'Unable to open backup file for FTP upload';
$string['log_ftp_upload_ok'] = 'FTP upload ok: {$a}';
$string['log_ftp_upload_failed'] = 'FTP upload failed';
$string['managebackups'] = 'Manage course backups';
$string['pluginname'] = 'Moodle Time Machine';
$string['privacy:metadata'] = 'Moodle Time Machine does not store personal data.';
$string['searchcourses'] = 'Search courses';
$string['stat_backups_generated'] = 'Backups generated';
$string['setting_backup_activities'] = 'Include activities';
$string['setting_backup_badges'] = 'Include badges';
$string['setting_backup_blocks'] = 'Include blocks';
$string['setting_backup_calendarevents'] = 'Include calendar events';
$string['setting_backup_comments'] = 'Include comments';
$string['setting_backup_filters'] = 'Include filters';
$string['setting_backup_role_assignments'] = 'Include role assignments';
$string['setting_backup_users'] = 'Include users';
$string['setting_backup_userscompletion'] = 'Include users completion';
$string['setting_categoryids'] = 'Categories to back up';
$string['setting_categoryids_desc'] = 'When set, all courses in the selected categories are included in automatic backups.';
$string['setting_ftpenabled'] = 'Enable FTP upload';
$string['setting_ftpenabled_desc'] = 'If enabled, each backup is also uploaded to the configured FTP server.';
$string['setting_ftphost'] = 'FTP host';
$string['setting_ftppass'] = 'FTP password';
$string['setting_ftppassive'] = 'Use passive mode';
$string['setting_ftppath'] = 'FTP path';
$string['setting_ftpport'] = 'FTP port';
$string['setting_ftpuser'] = 'FTP username';
$string['setting_notifyfailthreshold'] = 'Failure notification threshold';
$string['setting_notifyfailthreshold_desc'] = 'After this many consecutive backup failures for a course, email the site admin with error details.';
$string['setting_notifyonfail'] = 'Notify on repeated failures';
$string['setting_retentionversions'] = 'Retention (versions per course)';
$string['setting_retentionversions_desc'] = 'Maximum number of backup versions to keep for each course. Older versions are deleted automatically. Default: 7.';
$string['setting_verbose'] = 'Verbose logging';
$string['setting_verbose_desc'] = 'Include detailed progress messages and stack traces in scheduled task logs.';
$string['size'] = 'Size';
$string['stat_courses'] = 'Courses backed up';
$string['stat_courses_detail'] = 'Courses with backups: {$a}';
$string['stat_never'] = 'never sent';
$string['stat_since_last'] = 'Backups since last summary ({$a})';
$string['stat_totalsize'] = 'Total disk usage';
$string['stat_versions'] = 'Backup versions: {$a}';
$string['summary_email_fail_header'] = 'Courses with failed backups:';
$string['summary_email_loglink'] = 'View detailed logs: {$a}';
$string['summary_email_no_fail'] = 'No failed backups in this period.';
$string['summary_email_since'] = 'Period start: {$a}';
$string['summary_email_subject'] = 'Moodle Time Machine: daily backup summary';
$string['summary_email_successes'] = 'Successful backups: {$a}';
$string['summary_email_totalmb'] = 'Total size (MB): {$a}';
$string['task_error_backup_courses'] = 'Fatal error in scheduled backup task: {$a}';
$string['task_error_send_summary'] = 'Summary task error: {$a}';
$string['task_error_single_course'] = 'Adhoc error for course {$a->courseid}: {$a->message}';
$string['task_enforce_retention_error'] = 'Retention error for course {$a->courseid}: {$a->message}';
$string['task_enforce_retention_start'] = 'Enforcing retention for all courses (keep={$a})';
$string['task_backup_courses'] = 'Run Moodle Time Machine backups';
$string['task_send_summary'] = 'Send Moodle Time Machine summary email';
$string['task_missing_courseid'] = 'Adhoc task missing courseid';
$string['timemachine:manage'] = 'Manage Moodle Time Machine';
$string['error_backup_path_outside'] = 'Invalid backup path outside storage directory: {$a}';
$string['error_backup_write'] = 'Unable to write backup to {$a}';
$string['error_create_storage'] = 'Failed to create storage directory: {$a}';
