<?php

require_once($CFG->libdir . '/db/upgradelib.php');
require_once($CFG->dirroot . '/admin/tool/xmldb/actions/XMLDBAction.class.php');

/**
 * Upgrade code for the student local.
 *
 * @param int $oldversion
 */
function xmldb_local_student_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022041912) {

        $table = new xmldb_table('webinar_attendance');

        // Adding fields to table webinar_attendance.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('aid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attendance', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('updatedon', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table webinar_attendance.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for webinar_attendance.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022041912, 'local', 'student');
    }

    return true;
}
