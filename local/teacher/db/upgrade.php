<?php

// defined('MOODLE_INTERNAL') || die();
// require_login();
// require_once("{$CFG->dirroot}/my/lib.php");
require_once("{$CFG->libdir}/db/upgradelib.php");
// var_dump(" xmldb_local_student_upgrade 22222",$CFG->dirroot);

require_once($CFG->dirroot . '/admin/tool/xmldb/actions/XMLDBAction.class.php');

/**
 * Upgrade code for the student local.
 *
 * @param int $oldversion
 */
function xmldb_local_teacher_upgrade($oldversion) {
    global $DB, $CFG, $OUTPUT;
    $dbman = $DB->get_manager();

    if ($oldversion < 2022111803) {
        // Define changes to the table here
        $table = new xmldb_table('vpl');

        // Define the fields you want to alter
        $fields = array(
            'run' => new xmldb_field('run', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1'),
            'evaluate' => new xmldb_field('evaluate', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1'),
            'automaticgrading' => new xmldb_field('automaticgrading', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1')
        );

        // Alter the fields
        foreach ($fields as $field) {
            $dbman->change_field_type($table, $field);
        }

        // Execute the table alterations
        upgrade_plugin_savepoint(true, 2022111803, 'local','teacher');
    }

    return true;
}
