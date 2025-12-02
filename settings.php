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
 * Settings for local_timemachine.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // External admin page for managing backups list.
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_timemachine_manage',
        get_string('managebackups', 'local_timemachine'),
        new moodle_url('/local/timemachine/index.php'),
        'local/timemachine:manage'
    ));

    $settings = new admin_settingpage(
        'local_timemachine',
        get_string('pluginname', 'local_timemachine'),
        'local/timemachine:manage'
    );
    $ADMIN->add('localplugins', $settings);

    // Categories to back up (multi-select). Falls back to legacy single 'categoryid' default if present.
    $categories = core_course_category::make_categories_list();
    $legacydefault = (int)get_config('local_timemachine', 'categoryid');
    $defaultcats = $legacydefault ? [$legacydefault] : [];
    $settings->add(new admin_setting_configmultiselect(
        'local_timemachine/categoryids',
        get_string('setting_categoryids', 'local_timemachine'),
        get_string('setting_categoryids_desc', 'local_timemachine'),
        $defaultcats,
        $categories
    ));

    // Storage directory is fixed to MoodleTimeMachine under moodledata (no setting).

    // Verbose logging in scheduled task.
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/verbose',
        get_string('setting_verbose', 'local_timemachine'),
        get_string('setting_verbose_desc', 'local_timemachine'),
        0
    ));

    // FTP settings.
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/ftpenabled',
        get_string('setting_ftpenabled', 'local_timemachine'),
        get_string('setting_ftpenabled_desc', 'local_timemachine'),
        0
    ));
    $settings->add(new admin_setting_configtext(
        'local_timemachine/ftphost',
        get_string('setting_ftphost', 'local_timemachine'),
        '',
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'local_timemachine/ftpport',
        get_string('setting_ftpport', 'local_timemachine'),
        '',
        21,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'local_timemachine/ftpuser',
        get_string('setting_ftpuser', 'local_timemachine'),
        '',
        ''
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'local_timemachine/ftppass',
        get_string('setting_ftppass', 'local_timemachine'),
        '',
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'local_timemachine/ftppath',
        get_string('setting_ftppath', 'local_timemachine'),
        '',
        '/'
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/ftppassive',
        get_string('setting_ftppassive', 'local_timemachine'),
        '',
        1
    ));

    // Retention: number of backup versions per course.
    $sret = new admin_setting_configtext(
        'local_timemachine/retentionversions',
        get_string('setting_retentionversions', 'local_timemachine'),
        get_string('setting_retentionversions_desc', 'local_timemachine'),
        7,
        PARAM_INT
    );
    $sret->set_updatedcallback('local_timemachine_retention_updated');
    $settings->add($sret);

    // Backup content toggles.
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_users',
        get_string('setting_backup_users', 'local_timemachine'),
        '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_role_assignments',
        get_string('setting_backup_role_assignments', 'local_timemachine'),
        '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_activities',
        get_string('setting_backup_activities', 'local_timemachine'),
        '',
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_blocks',
        get_string('setting_backup_blocks', 'local_timemachine'),
        '',
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_filters',
        get_string('setting_backup_filters', 'local_timemachine'),
        '',
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_comments',
        get_string('setting_backup_comments', 'local_timemachine'),
        '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_badges',
        get_string('setting_backup_badges', 'local_timemachine'),
        '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_calendarevents',
        get_string('setting_backup_calendarevents', 'local_timemachine'),
        '',
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/backup_userscompletion',
        get_string('setting_backup_userscompletion', 'local_timemachine'),
        '',
        0
    ));

    // Failure handling + notifications.
    $settings->add(new admin_setting_configcheckbox(
        'local_timemachine/notifyonfail',
        get_string('setting_notifyonfail', 'local_timemachine'),
        '',
        1
    ));
    $settings->add(new admin_setting_configtext(
        'local_timemachine/notifyfailthreshold',
        get_string('setting_notifyfailthreshold', 'local_timemachine'),
        get_string('setting_notifyfailthreshold_desc', 'local_timemachine'),
        3,
        PARAM_INT
    ));
}
