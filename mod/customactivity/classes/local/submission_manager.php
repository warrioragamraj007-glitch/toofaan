<?php
namespace mod_customactivity\local;
defined('MOODLE_INTERNAL') || die();

class submission_manager {
    public static function get_last_submission($activityid, $userid) {
        global $DB;
        return $DB->get_record_sql("SELECT * FROM {customactivity_submissions} WHERE customactivityid = ? AND userid = ? ORDER BY timecreated DESC LIMIT 1", [$activityid, $userid]);
    }
}
