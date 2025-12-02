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
 * Upgrade script for local_timemachine.
 *
 * @param int $oldversion
 * @return bool
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_timemachine_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025102801) {
        // Create table local_timemachine_course if missing.
        $table = new xmldb_table('local_timemachine_course');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('lastsignature', XMLDB_TYPE_CHAR, '64', null, null, null, null);
            $table->add_field('lastbackup', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('courseid_uq', XMLDB_KEY_UNIQUE, ['courseid']);

            $dbman->create_table($table);
        }

        // Create table local_timemachine_backup if missing.
        $table2 = new xmldb_table('local_timemachine_backup');
        if (!$dbman->table_exists($table2)) {
            $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table2->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table2->add_field('filepath', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
            $table2->add_field('filesize', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table2->add_field('signature', XMLDB_TYPE_CHAR, '64', null, null, null, null);
            $table2->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            $table2->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table2->add_index('courseid_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

            $dbman->create_table($table2);
        }

        upgrade_plugin_savepoint(true, 2025102801, 'local', 'timemachine');
    }

    if ($oldversion < 2025102802) {
        // Add indexes for performance.
        $table = new xmldb_table('local_timemachine_course');
        $idx1 = new xmldb_index('categoryid_ix', XMLDB_INDEX_NOTUNIQUE, ['categoryid']);
        if (!$dbman->index_exists($table, $idx1)) {
            $dbman->add_index($table, $idx1);
        }

        $table2 = new xmldb_table('local_timemachine_backup');
        $idx2 = new xmldb_index('courseid_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        if (!$dbman->index_exists($table2, $idx2)) {
            $dbman->add_index($table2, $idx2);
        }
        $idx3 = new xmldb_index('course_time_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'timecreated']);
        if (!$dbman->index_exists($table2, $idx3)) {
            $dbman->add_index($table2, $idx3);
        }
        $idx4 = new xmldb_index('timecreated_ix', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->index_exists($table2, $idx4)) {
            $dbman->add_index($table2, $idx4);
        }

        upgrade_plugin_savepoint(true, 2025102802, 'local', 'timemachine');
    }

    if ($oldversion < 2025102803) {
        // Add queuedat field to track pending adhoc tasks.
        $table = new xmldb_table('local_timemachine_course');
        $field = new xmldb_field('queuedat', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'lastbackup');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025102803, 'local', 'timemachine');
    }

    if ($oldversion < 2025102804) {
        // Remove deprecated setting for storagedirname; storage folder is fixed.
        unset_config('storagedirname', 'local_timemachine');
        upgrade_plugin_savepoint(true, 2025102804, 'local', 'timemachine');
    }

    if ($oldversion < 2025102805) {
        // Add failure tracking fields for retries and notifications.
        $table = new xmldb_table('local_timemachine_course');
        $f1 = new xmldb_field('failcount', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'queuedat');
        if (!$dbman->field_exists($table, $f1)) {
            $dbman->add_field($table, $f1);
        }
        $f2 = new xmldb_field('lastfail', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'failcount');
        if (!$dbman->field_exists($table, $f2)) {
            $dbman->add_field($table, $f2);
        }
        $f3 = new xmldb_field('lastnotified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'lastfail');
        if (!$dbman->field_exists($table, $f3)) {
            $dbman->add_field($table, $f3);
        }
        upgrade_plugin_savepoint(true, 2025102805, 'local', 'timemachine');
    }

    if ($oldversion < 2025102806) {
        // Clear any delayed adhoc tasks created during previous failures, so they run immediately.
        // Also reset fail counters (previous errors were due to code changes).
        $classname = '\\local_timemachine\\task\\backup_single_course';
        $DB->execute(
            "UPDATE {task_adhoc} SET nextruntime = 0 WHERE classname = :cn OR component = :comp",
            ['cn' => $classname, 'comp' => 'local_timemachine']
        );
        $DB->execute("UPDATE {local_timemachine_course} SET failcount = 0, lastnotified = NULL", []);
        upgrade_plugin_savepoint(true, 2025102806, 'local', 'timemachine');
    }

    if ($oldversion < 2025102807) {
        // Create local_timemachine_log table for detailed failure logs.
        $table = new xmldb_table('local_timemachine_log');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('level', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('message', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
            $table->add_field('details', XMLDB_TYPE_TEXT, 'big', null, null, null, null);
            $table->add_field('runid', XMLDB_TYPE_CHAR, '64', null, null, null, null);
            $table->add_field('context', XMLDB_TYPE_TEXT, 'small', null, null, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $dbman->create_table($table);

            // Indexes.
            $ix1 = new xmldb_index('courseid_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
            $dbman->add_index($table, $ix1);
            $ix2 = new xmldb_index('timecreated_ix', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $dbman->add_index($table, $ix2);
            $ix3 = new xmldb_index('runid_ix', XMLDB_INDEX_NOTUNIQUE, ['runid']);
            $dbman->add_index($table, $ix3);
        }

        // Initialize lastsummarysent marker.
        if (get_config('local_timemachine', 'lastsummarysent') === false) {
            set_config('lastsummarysent', 0, 'local_timemachine');
        }

        upgrade_plugin_savepoint(true, 2025102807, 'local', 'timemachine');
    }

    if ($oldversion < 2025111200) {
        // General hardening and metadata refresh.
        upgrade_plugin_savepoint(true, 2025111200, 'local', 'timemachine');
    }

    if ($oldversion < 2025111300) {
        // Coding standard fixes release.
        upgrade_plugin_savepoint(true, 2025111300, 'local', 'timemachine');
    }

    return true;
}
