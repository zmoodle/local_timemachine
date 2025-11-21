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

namespace local_timemachine\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/helper/backup_cron_helper.class.php');
require_once($CFG->libdir . '/filelib.php');

use core_course_category;
use moodle_exception;

/**
 * Backup coordinator for the local_timemachine plugin.
 *
 * Handles change detection, adhoc task queuing, execution and retention.
 *
 * @package   local_timemachine
 * @copyright 2025 zMoodle (https://app.zmoodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backupper {
    const MAX_VERSIONS = 7; // Fallback default if config is missing.
    const BACKOFF_BASE = 600; // 10 minutes.
    const BACKOFF_MAX = 86400; // 24 hours.

    protected static function vlog(string $message): void {
        if ((int)get_config('local_timemachine', 'verbose')) {
            mtrace('local_timemachine: ' . $message);
        }
    }

    protected static function elog(string $message, ?\Throwable $e = null, ?int $courseid = null, ?string $runid = null, ?array $ctx = null): void {
        mtrace('local_timemachine: ' . $message);
        $details = '';
        if ($e) {
            $details = 'Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
        }
        self::write_log($courseid ?? 0, 'error', $message, $details ?: null, $runid, $ctx ? json_encode($ctx) : null);
        if ($e && (int)get_config('local_timemachine', 'verbose')) {
            mtrace('Exception: ' . $e->getMessage());
            mtrace($e->getTraceAsString());
        }
    }

    protected static function write_log(int $courseid, string $level, string $message, ?string $details = null, ?string $runid = null, ?string $context = null): void {
        global $DB;
        $rec = (object) [
            'timecreated' => time(),
            'courseid' => $courseid,
            'level' => $level,
            'message' => $message,
            'details' => $details,
            'runid' => $runid,
            'context' => $context,
        ];
        try {
            $DB->insert_record('local_timemachine_log', $rec);
        } catch (\Throwable $ignore) {
            // Avoid breaking on logging itself.
        }
    }

    public static function get_storage_dir(): string {
        global $CFG;
        $dirname = 'MoodleTimeMachine';
        $dir = rtrim($CFG->dataroot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $dirname;
        if (!check_dir_exists($dir, true, true)) {
            $msg = 'Failed to create storage directory: ' . $dir;
            throw new moodle_exception('generalexceptionmessage', 'error', '', $msg);
        }
        return $dir;
    }

    public static function is_within_storage(string $path): bool {
        try {
            $basedir = self::get_storage_dir();
        } catch (\Throwable $e) {
            self::elog('Storage directory unavailable', $e, null, null, ['path' => $path]);
            return false;
        }
        $base = realpath($basedir);
        if ($base === false) {
            return false;
        }

        // Resolve target even if file does not yet exist.
        if (file_exists($path)) {
            $target = realpath($path);
        } else {
            $dir = realpath(dirname($path));
            if ($dir === false) {
                return false;
            }
            $target = $dir . DIRECTORY_SEPARATOR . basename($path);
        }

        if ($target === false) {
            return false;
        }

        // Normalize case on Windows for comparison.
        if (PHP_OS_FAMILY === 'Windows') {
            $basecmp = strtolower($base);
            $targetcmp = strtolower($target);
        } else {
            $basecmp = $base;
            $targetcmp = $target;
        }

        $prefix = rtrim($basecmp, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return $targetcmp === rtrim($basecmp, DIRECTORY_SEPARATOR) || str_starts_with($targetcmp, $prefix);
    }

    public static function delete_backup_file(?string $path): void {
        if (empty($path)) {
            return;
        }
        if (!file_exists($path)) {
            return;
        }
        try {
            $within = self::is_within_storage($path);
        } catch (\Throwable $e) {
            self::elog('Unable to resolve storage directory for deletion', $e, null, null, ['path' => $path]);
            return;
        }
        if (!$within) {
            self::elog('Skipping deletion of file outside storage directory', null, null, null, ['path' => $path]);
            return;
        }
        if (!is_writable($path)) {
            self::elog('Backup file is not writable and cannot be deleted', null, null, null, ['path' => $path]);
            return;
        }
        if (!unlink($path)) {
            self::elog('Failed to delete backup file from disk', null, null, null, ['path' => $path]);
        }
    }

    /**
     * Safe unserialize wrapper that suppresses warnings while disallowing classes.
     *
     * @param string $data
     * @return mixed
     */
    protected static function safe_unserialize(string $data) {
        $handler = static function () {
            return true;
        };
        set_error_handler($handler);
        try {
            return unserialize($data, ['allowed_classes' => false]);
        } finally {
            restore_error_handler();
        }
    }

    public static function run_scheduled_backup(?string $runid = null): void {
        global $DB;
        $runid = $runid ?: uniqid('tmb_', true);

        // Read multi-category config (CSV) with fallback to legacy single value.
        $raw = (string)get_config('local_timemachine', 'categoryids');
        $categoryids = [];
        if ($raw !== '') {
            foreach (explode(',', $raw) as $id) {
                $id = (int)trim($id);
                if ($id > 0) { $categoryids[] = $id; }
            }
        }
        if (empty($categoryids)) {
            $legacy = (int)get_config('local_timemachine', 'categoryid');
            if ($legacy > 0) { $categoryids = [$legacy]; }
        }

        if (empty($categoryids)) {
            mtrace('local_timemachine: no category selected, skipping');
            return;
        }

        self::vlog('selected category ids: ' . implode(',', $categoryids));

        // Fetch courses in any of the selected categories.
        list($in, $params) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'cat');
        $courses = $DB->get_records_select('course', "category $in", $params);
        self::vlog('found courses: ' . count($courses));
        foreach ($courses as $course) {
            try {
                self::vlog('queue check for course id=' . $course->id . ' shortname=' . $course->shortname);
                self::queue_if_changed($course->id, $runid);
            } catch (\Throwable $e) {
                self::elog('queue error for course ' . $course->id, $e, (int)$course->id, $runid);
            }
        }
    }

    public static function queue_if_changed(int $courseid, ?string $runid = null): void {
        global $DB;
        $signature = self::compute_signature($courseid);
        $rec = $DB->get_record('local_timemachine_course', ['courseid' => $courseid]);
        $now = time();
        $staleseconds = 15 * 60; // 15 minutes throttle.
        if ($rec) {
            if (!empty($rec->lastsignature) && $rec->lastsignature === $signature) {
                self::vlog('no changes since last backup; skip queue');
                return;
            }
        }

        // If already present in adhoc queue for this course, skip re-queue regardless of queuedat.
        if (self::has_pending_adhoc($courseid)) {
            self::vlog('already present in adhoc queue; skip');
            return;
        }

        if ($rec && !empty($rec->queuedat) && ($now - (int)$rec->queuedat) < $staleseconds) {
            self::vlog('already queued recently; skip');
            return;
        }

        $task = new \local_timemachine\task\backup_single_course();
        $data = (object)['courseid' => $courseid];
        if ($runid) { $data->runid = $runid; }
        $task->set_custom_data($data);
        $task->set_component('local_timemachine');
        \core\task\manager::queue_adhoc_task($task, true);
        self::vlog('queued adhoc backup task');

        if ($rec) {
            $rec->queuedat = $now;
            $rec->timemodified = $now;
            $DB->update_record('local_timemachine_course', $rec);
        } else {
            $course = $DB->get_record('course', ['id' => $courseid], 'id, category');
            $DB->insert_record('local_timemachine_course', (object)[
                'courseid' => $courseid,
                'categoryid' => $course ? $course->category : 0,
                'lastsignature' => null,
                'lastbackup' => null,
                'queuedat' => $now,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }
    }

    protected static function has_pending_adhoc(int $courseid): bool {
        global $DB;
        $classname = '\\local_timemachine\\task\\backup_single_course';
        $tasks = $DB->get_records('task_adhoc', ['component' => 'local_timemachine', 'classname' => $classname], '', 'id, customdata');
        foreach ($tasks as $t) {
            $data = $t->customdata ?? '';
            if ($data === '' || $data === null) { continue; }
            // Try JSON first.
            $obj = json_decode($data);
            if (is_object($obj) && isset($obj->courseid) && (int)$obj->courseid === $courseid) {
                return true;
            }
            // Fallback to base64-serialized.
            $decoded = base64_decode($data, true);
            if ($decoded !== false) {
                $obj2 = self::safe_unserialize($decoded);
                if (is_object($obj2) && isset($obj2->courseid) && (int)$obj2->courseid === $courseid) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function compute_signature(int $courseid): string {
        global $DB;
        $course = $DB->get_record('course', ['id' => $courseid], 'id, timemodified, category', MUST_EXIST);

        // Use safe queries; some fields may not exist across versions. Wrap in try/catch.
        $maxmod = 0;
        try {
            $maxmod = (int)$DB->get_field_sql('SELECT COALESCE(MAX(cm.added), 0)
                                                 FROM {course_modules} cm
                                                WHERE cm.course = :cid', ['cid' => $courseid]);
        } catch (\Throwable $e) {
            self::elog('signature query (course_modules) failed', $e);
        }

        $maxsec = 0;
        try {
            $maxsec = (int)$DB->get_field_sql('SELECT COALESCE(MAX(cs.timemodified), 0)
                                                FROM {course_sections} cs
                                               WHERE cs.course = :cid', ['cid' => $courseid]);
        } catch (\Throwable $e) {
            self::elog('signature query (course_sections) failed', $e);
        }

        $modcount = 0;
        try {
            $modcount = (int)$DB->get_field_sql('SELECT COUNT(1) FROM {course_modules} WHERE course = :cid', ['cid' => $courseid]);
        } catch (\Throwable $e) {
            self::elog('signature query (modules count) failed', $e);
        }

        $sectioncount = 0;
        try {
            $sectioncount = (int)$DB->get_field_sql('SELECT COUNT(1) FROM {course_sections} WHERE course = :cid', ['cid' => $courseid]);
        } catch (\Throwable $e) {
            self::elog('signature query (sections count) failed', $e);
        }

        $maxblock = 0;
        try {
            $maxblock = (int)$DB->get_field_sql('SELECT COALESCE(MAX(b.timemodified), 0)
                                                   FROM {block_instances} b
                                                   JOIN {context} ctx ON ctx.id = b.parentcontextid
                                                  WHERE ctx.contextlevel = :lvl AND ctx.instanceid = :cid',
                                                  ['lvl' => CONTEXT_COURSE, 'cid' => $courseid]);
        } catch (\Throwable $e) {
            self::elog('signature query (blocks) failed', $e);
        }

        $data = implode('|', [
            (int)$course->timemodified,
            $maxmod,
            $maxsec,
            $modcount,
            $sectioncount,
            $maxblock,
        ]);
        return sha1($data);
    }

    public static function maybe_backup_course(int $courseid): ?string {
        global $DB, $USER;

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        if ((int)$course->id === SITEID) {
            return null; // Skip front page.
        }

        $signature = self::compute_signature($courseid);

        $rec = $DB->get_record('local_timemachine_course', ['courseid' => $courseid]);
        if ($rec) {
            self::vlog('previous signature: ' . ($rec->lastsignature ?? '(none)'));
        }
        self::vlog('current signature: ' . $signature);
        if ($rec && $rec->lastsignature === $signature) {
            return null; // No changes; skip backup.
        }

        // Ensure storage directory.
        $storedir = self::get_storage_dir();

        // Prepare backup.
        $admin = get_admin();
        $userid = $admin ? $admin->id : $USER->id;

        $controller = new \backup_controller(
            \backup::TYPE_1COURSE,
            $courseid,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_AUTOMATED,
            $userid
        );

        // Keep backups lean (no users) by default.
        $plan = $controller->get_plan();
        $settings = [
            'users' => (int)get_config('local_timemachine', 'backup_users'),
            'role_assignments' => (int)get_config('local_timemachine', 'backup_role_assignments'),
            'activities' => (int)get_config('local_timemachine', 'backup_activities') ?: 1,
            'blocks' => (int)get_config('local_timemachine', 'backup_blocks') ?: 1,
            'filters' => (int)get_config('local_timemachine', 'backup_filters') ?: 1,
            'comments' => (int)get_config('local_timemachine', 'backup_comments'),
            'badges' => (int)get_config('local_timemachine', 'backup_badges'),
            'calendarevents' => (int)get_config('local_timemachine', 'backup_calendarevents') ?: 1,
            'userscompletion' => (int)get_config('local_timemachine', 'backup_userscompletion'),
        ];
        foreach ($settings as $name => $value) {
            if ($plan->setting_exists($name)) {
                $plan->get_setting($name)->set_value($value);
            }
        }

        self::vlog('executing backup plan...');
        try {
            $controller->execute_plan();
        } catch (\Throwable $e) {
            // Ensure controller is cleaned up and rethrow.
            try { $controller->destroy(); } catch (\Throwable $ie) {}
            throw $e;
        }
        $results = $controller->get_results();
        $file = $results['backup_destination'] ?? null; // stored_file
        $controller->destroy();

        if (!$file) {
            throw new moodle_exception('backupfailed', 'local_timemachine');
        }

        // Compose target filename.
        $cat = core_course_category::get($course->category, IGNORE_MISSING, true);
        $catname = $cat ? preg_replace('/[^A-Za-z0-9_-]+/', '_', $cat->get_formatted_name()) : 'cat' . $course->category;
        $short = preg_replace('/[^A-Za-z0-9_-]+/', '_', $course->shortname);
        $ts = userdate(time(), '%Y%m%d-%H%M%S');
        $filename = $catname . '__' . $short . '__id' . $courseid . '__' . $ts . '.mbz';
        $fullpath = $storedir . DIRECTORY_SEPARATOR . $filename;
        if (!self::is_within_storage($fullpath)) {
            $msg = 'Invalid backup path outside storage directory: ' . $fullpath;
            throw new moodle_exception('generalexceptionmessage', 'error', '', $msg);
        }

        // Write file to storage directory.
        if (!$file->copy_content_to($fullpath)) {
            $msg = 'Unable to write backup to ' . $fullpath;
            throw new moodle_exception('generalexceptionmessage', 'error', '', $msg);
        }
        clearstatcache(true, $fullpath);
        $size = is_readable($fullpath) ? filesize($fullpath) : false;
        $filesize = ($size === false) ? null : (int)$size;
        self::vlog('saved file to ' . $fullpath . ' size=' . ($filesize ?? -1));

        // Record in DB.
        $now = time();
        if ($rec) {
            $rec->categoryid = $course->category;
            $rec->lastsignature = $signature;
            $rec->lastbackup = $now;
            $rec->queuedat = null;
            $rec->failcount = 0;
            $rec->lastfail = null;
            $rec->timemodified = $now;
            $DB->update_record('local_timemachine_course', $rec);
        } else {
            $rec = (object) [
                'courseid' => $courseid,
                'categoryid' => $course->category,
                'lastsignature' => $signature,
                'lastbackup' => $now,
                'queuedat' => null,
                'failcount' => 0,
                'lastfail' => null,
                'lastnotified' => null,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $rec->id = $DB->insert_record('local_timemachine_course', $rec);
        }

        $brecord = (object) [
            'courseid' => $courseid,
            'filepath' => $fullpath,
            'filesize' => $filesize,
            'signature' => $signature,
            'timecreated' => $now,
        ];
        $DB->insert_record('local_timemachine_backup', $brecord);
        self::vlog('recorded backup entry in DB');

        // Enforce retention: keep last 7 per course.
        self::enforce_retention($courseid);

        // Optional FTP upload.
        self::maybe_upload_ftp($fullpath, $filename);

        return $fullpath;
    }

    public static function handle_failure(int $courseid, \Throwable $e, ?string $runid = null): void {
        global $DB;
        $now = time();
        // Persist detailed log for diagnostics.
        $msg = 'Backup failed for course ' . $courseid;
        self::write_log($courseid, 'error', $msg, 'Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), $runid);
        $rec = $DB->get_record('local_timemachine_course', ['courseid' => $courseid]);
        if ($rec) {
            $rec->failcount = (int)($rec->failcount ?? 0) + 1;
            $rec->lastfail = $now;
            $rec->timemodified = $now;
            $DB->update_record('local_timemachine_course', $rec);
        } else {
            $course = $DB->get_record('course', ['id' => $courseid], 'id, category');
            $rec = (object) [
                'courseid' => $courseid,
                'categoryid' => $course ? $course->category : 0,
                'failcount' => 1,
                'lastfail' => $now,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $rec->id = $DB->insert_record('local_timemachine_course', $rec);
        }

        // Notify admin if threshold reached and enabled.
        if ((int)get_config('local_timemachine', 'notifyonfail')) {
            $threshold = (int)get_config('local_timemachine', 'notifyfailthreshold');
            $threshold = $threshold > 0 ? $threshold : 3;
            if ($rec->failcount >= $threshold) {
                $shouldnotify = empty($rec->lastnotified) || ($rec->failcount % $threshold) === 0;
                if ($shouldnotify) {
                    self::notify_admin_failure($courseid, $rec->failcount, $e);
                    $rec->lastnotified = $now;
                    $DB->update_record('local_timemachine_course', $rec);
                }
            }
        }

        // Requeue with exponential backoff.
        $delay = (int)min(self::BACKOFF_MAX, self::BACKOFF_BASE * (2 ** max(0, (int)$rec->failcount - 1)));
        $task = new \local_timemachine\task\backup_single_course();
        $task->set_custom_data((object)['courseid' => $courseid]);
        $task->set_component('local_timemachine');
        $task->set_next_run_time($now + $delay);
        \core\task\manager::queue_adhoc_task($task, true);
        self::vlog('requeued adhoc task with delay ' . $delay . 's (failcount=' . $rec->failcount . ')');
    }

    public static function send_daily_summary(): void {
        global $DB;
        $admin = get_admin();
        if (!$admin || empty($admin->email)) { return; }

        $now = time();
        $since = (int)get_config('local_timemachine', 'lastsummarysent');
        if ($since <= 0) { $since = $now - 24 * 3600; }

        // Successes in window.
        $successcount = (int)$DB->get_field_sql('SELECT COUNT(1) FROM {local_timemachine_backup} WHERE timecreated > :t', ['t' => $since]);
        $totalsize = (int)$DB->get_field_sql('SELECT COALESCE(SUM(filesize),0) FROM {local_timemachine_backup} WHERE timecreated > :t', ['t' => $since]);
        $mb = $totalsize > 0 ? number_format($totalsize / 1048576, 2) : '0.00';

        // Failed courses in window (unique list by course).
        $faillogs = $DB->get_records_sql('SELECT MIN(id) AS id, courseid FROM {local_timemachine_log} WHERE level = :lvl AND timecreated > :t GROUP BY courseid', ['lvl' => 'error', 't' => $since]);
        $failed = [];
        foreach ($faillogs as $fl) {
            if (!$fl->courseid) { continue; }
            $c = $DB->get_record('course', ['id' => $fl->courseid], 'id, fullname');
            $failed[] = $c ? $c->fullname : ('ID ' . $fl->courseid);
        }
        sort($failed, SORT_NATURAL | SORT_FLAG_CASE);

        $logurl = new \moodle_url('/local/timemachine/log.php', ['since' => $since]);

        $subject = get_string('summary_email_subject', 'local_timemachine');
        $lines = [];
        $lines[] = get_string('summary_email_since', 'local_timemachine', userdate($since));
        $lines[] = get_string('summary_email_successes', 'local_timemachine', $successcount);
        $lines[] = get_string('summary_email_totalmb', 'local_timemachine', $mb);
        $lines[] = '';
        if ($failed) {
            $lines[] = get_string('summary_email_fail_header', 'local_timemachine');
            foreach ($failed as $fname) {
                $lines[] = ' - ' . $fname;
            }
            $lines[] = '';
            $lines[] = get_string('summary_email_loglink', 'local_timemachine', $logurl->out(false));
        } else {
            $lines[] = get_string('summary_email_no_fail', 'local_timemachine');
        }

        $body = implode("\n", $lines) . "\n";
        email_to_user($admin, $admin, $subject, $body);
        set_config('lastsummarysent', $now, 'local_timemachine');
    }

    protected static function notify_admin_failure(int $courseid, int $failcount, \Throwable $e): void {
        global $DB;
        $admin = get_admin();
        if (!$admin || empty($admin->email)) { return; }
        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, shortname');
        $subject = 'Moodle Time Machine: backup failed for course ID ' . $courseid;
        $body = 'Course: ' . ($course ? $course->fullname . ' (' . $course->shortname . ')' : ('ID ' . $courseid)) . "\n" .
                'Failures: ' . $failcount . "\n" .
                'Error: ' . $e->getMessage() . "\n\n" .
                'Trace:' . "\n" . $e->getTraceAsString() . "\n";
        email_to_user($admin, $admin, $subject, $body);
    }

    public static function enforce_retention(int $courseid): void {
        global $DB;
        $records = $DB->get_records('local_timemachine_backup', ['courseid' => $courseid], 'timecreated DESC', 'id, filepath');
        $keep = self::get_max_versions();
        $toDelete = array_slice(array_values($records), $keep);
        if ($toDelete) {
            self::vlog('retention: deleting ' . count($toDelete) . ' old backups');
        }
        foreach ($toDelete as $rec) {
            self::delete_backup_file($rec->filepath ?? '');
            $DB->delete_records('local_timemachine_backup', ['id' => $rec->id]);
        }
    }

    protected static function get_max_versions(): int {
        $n = (int)get_config('local_timemachine', 'retentionversions');
        return $n > 0 ? $n : self::MAX_VERSIONS;
    }

    protected static function maybe_upload_ftp(string $fullpath, string $filename): void {
        if (!get_config('local_timemachine', 'ftpenabled')) {
            return;
        }
        if (!self::is_within_storage($fullpath) || !is_readable($fullpath)) {
            self::elog('FTP upload skipped: backup file missing or outside storage directory', null, null, null, ['path' => $fullpath]);
            return;
        }
        $host = (string)get_config('local_timemachine', 'ftphost');
        $port = (int)get_config('local_timemachine', 'ftpport');
        $user = (string)get_config('local_timemachine', 'ftpuser');
        $pass = (string)get_config('local_timemachine', 'ftppass');
        $path = (string)get_config('local_timemachine', 'ftppath');
        $passive = (int)get_config('local_timemachine', 'ftppassive');
        if (empty($host) || empty($user) || empty($pass)) {
            return;
        }
        if (!function_exists('ftp_connect')) {
            self::vlog('FTP functions not available in PHP');
            return; // FTP not available.
        }
        $port = $port ?: 21;
        $conn = ftp_connect($host, $port, 20);
        if ($conn === false) {
            self::elog('FTP connect failed', null, null, null, ['host' => $host, 'port' => $port]);
            return;
        }

        $loggedin = ftp_login($conn, $user, $pass);
        if (!$loggedin) {
            ftp_close($conn);
            self::elog('FTP login failed', null, null, null, ['host' => $host, 'user' => $user]);
            return;
        }
        if ($passive) {
            ftp_pasv($conn, true);
        }
        if (!empty($path) && !ftp_chdir($conn, $path)) {
            self::elog('FTP change directory failed', null, null, null, ['path' => $path]);
        }

        $stream = fopen($fullpath, 'rb');
        if ($stream === false) {
            ftp_close($conn);
            self::elog('Unable to open backup file for FTP upload', null, null, null, ['path' => $fullpath]);
            return;
        }
        $uploaded = ftp_fput($conn, $filename, $stream, FTP_BINARY);
        fclose($stream);
        ftp_close($conn);

        if ($uploaded) {
            self::vlog('FTP upload ok: ' . $filename);
        } else {
            self::elog('FTP upload failed', null, null, null, ['filename' => $filename]);
        }
    }
}
