<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_customactivity_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // ===================================================================
    // FINAL UPGRADE TO CLEAN MULTI-QUESTION + ALWAYS AI-GRADED (20251211)
    // ===================================================================
    if ($oldversion < 2025121101) {

        // ---------------------------------------------------------------
        // 1. Clean up / remove old single-question columns from main table
        // ---------------------------------------------------------------
        $table = new xmldb_table('customactivity');

        // Remove these old columns if they exist (they belong to questions table now)
        $oldfields = ['question', 'correctanswer', 'matchtype'];
        foreach ($oldfields as $name) {
    $field = new xmldb_field($name);
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }
}

        // Make sure maxattempts and ai_eval_limit still exist (they stay in main table)
        $field = new xmldb_field('maxattempts', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '3');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('ai_eval_limit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '15');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ---------------------------------------------------------------
        // 2 Create / fix the questions table (clean version)
        // ---------------------------------------------------------------
        $table = new xmldb_table('customactivity_questions');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('customactivityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('qno', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('questiontext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->add_field('modelanswer', XMLDB_TYPE_TEXT, null, null, null); // optional reference answer
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('activity_fk', XMLDB_KEY_FOREIGN, ['customactivityid'], 'customactivity', ['id']);
            $table->add_index('order', XMLDB_INDEX_NOTUNIQUE, ['customactivityid', 'qno']);

            $dbman->create_table($table);
        } else {
            // Table exists → make sure columns are correct
            $field = new xmldb_field('modelanswer', XMLDB_TYPE_TEXT, null, null, null, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Rename old 'correctanswer' → 'modelanswer' if it still exists
            $old = new xmldb_field('correctanswer');
            $new = new xmldb_field('modelanswer', XMLDB_TYPE_TEXT);
            if ($dbman->field_exists($table, $old) && !$dbman->field_exists($table, $new)) {
                $dbman->rename_field($table, $old, 'modelanswer');
            }
        }

        // ---------------------------------------------------------------
        // 3 Fix submissions table – ADD questionid (THE MOST IMPORTANT PART)
        // ---------------------------------------------------------------
        $table = new xmldb_table('customactivity_submissions');

        // Add questionid column
        $field = new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'customactivityid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Back-fill questionid using old qno (only if you have existing submissions)
            $sql = "
                UPDATE {customactivity_submissions} s
                JOIN {customactivity_questions} q 
                  ON q.customactivityid = s.customactivityid 
                 AND q.qno = s.qno
                SET s.questionid = q.id
                WHERE s.questionid = 0 OR s.questionid IS NULL
            ";
            $DB->execute($sql);
        }

        // Add foreign key
        $key = new xmldb_key('fk_question', XMLDB_KEY_FOREIGN, ['questionid'], 'customactivity_questions', ['id']);
        if (!$dbman->find_key_name($table, $key)) {
            $dbman->add_key($table, $key);
        }

        // Clean up old/unused columns if they exist
        $oldcols = ['qno', 'submissiontime', 'ipaddress'];
        foreach ($oldcols as $col) {
            $field = new xmldb_field($col);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Make sure these core columns exist and are correct
        $required = [
            'answer'     => [XMLDB_TYPE_TEXT, null, null, null],
            'grade'      => [XMLDB_TYPE_NUMBER, '10,5', XMLDB_NOTNULL, '0.00000'],
            'feedback'   => [XMLDB_TYPE_TEXT, null, null, null],
            'iscorrect'  => [XMLDB_TYPE_INTEGER, '1', XMLDB_NOTNULL, '0'],
            'attemptno'  => [XMLDB_TYPE_INTEGER, '3', XMLDB_NOTNULL, '1'],
            'timecreated'=> [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, '0'],
        ];

        foreach ($required as $name => $def) {
            $field = new xmldb_field($name, $def[0], $def[1] ?? null, null, $def[2] ?? null, null, $def[3] ?? null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            } else {
                // Fix precision/type if wrong
                $dbman->change_field_type($table, $field);
                $dbman->change_field_precision($table, $field);
                $dbman->change_field_default($table, $field);
                $dbman->change_field_notnull($table, $field);
            }
        }

        // Useful index
        $index = new xmldb_index('userquestion', XMLDB_INDEX_NOTUNIQUE, ['userid', 'questionid', 'attemptno']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_mod_savepoint(true, 2025121101, 'customactivity');
    }

    return true;
}