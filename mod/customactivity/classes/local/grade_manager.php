<?php
namespace mod_customactivity\local;
defined('MOODLE_INTERNAL') || die();

class grade_manager {
    public static function user_grade($activityid, $userid) {
        global $DB;
        $sql = "SELECT MAX(iscorrect) AS gotcorrect FROM {customactivity_submissions} WHERE customactivityid = :cid AND userid = :uid";
        $rec = $DB->get_record_sql($sql, ['cid'=>$activityid, 'uid'=>$userid]);
        return ($rec && $rec->gotcorrect) ? 100 : 0;
    }
}
