<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');

function customactivity_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE: return true;
        case FEATURE_GRADE_OUTCOMES: return false;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}

function customactivity_add_instance($data) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Insert activity
    $id = $DB->insert_record('customactivity', $data);

    // Save questions
    save_customactivity_questions($id, $data);

    // Grade item
    $data->id = $id;
    customactivity_grade_item_update($data);

    return $id;
}

function customactivity_update_instance($data) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('customactivity', $data);

    // Save questions
    save_customactivity_questions($data->id, $data);

    // Update grade item
    customactivity_update_grades($data);

    return true;
}


function save_customactivity_questions($activityid, $data) {
    global $DB;

    if (empty($data->questiontext)) {
        return;
    }

    $newids = [];

    foreach ($data->questiontext as $i => $text) {

        $text = trim($text);
        if ($text === '') continue;

        $q = new stdClass();
        $q->customactivityid = $activityid;
        $q->questiontext = $text;
        $q->modelanswer  = trim($data->modelanswer[$i] ?? '');
        $q->qno = $i + 1;

        if (!empty($data->questionid[$i])) {
            $q->id = $data->questionid[$i];
            $DB->update_record('customactivity_questions', $q);
        } else {
            $q->id = $DB->insert_record('customactivity_questions', $q);
        }

        $newids[] = $q->id;
    }

    // Delete removed questions
    $existing = $DB->get_records('customactivity_questions', ['customactivityid' => $activityid]);

    foreach ($existing as $old) {
        if (!in_array($old->id, $newids)) {
            $DB->delete_records('customactivity_questions', ['id' => $old->id]);
        }
    }
}

function customactivity_delete_instance($id) {
    global $DB;
    if (!$activity = $DB->get_record('customactivity', ['id' => $id])) {
        return false;
    }
    $DB->delete_records('customactivity_submissions', ['customactivityid' => $id]);
    $DB->delete_records('customactivity', ['id' => $id]);
    customactivity_grade_item_update((object)['id'=>$id,'course'=>$activity->course,'name'=>$activity->name], null);
    return true;
}

function customactivity_grade_item_update(stdClass $customactivity, $grades = null) {
    $params = [
        'itemname'  => $customactivity->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax'  => 100,
        'grademin'  => 0
    ];
    grade_update(
        'mod/customactivity',
        $customactivity->course,
        'mod',
        'customactivity',
        $customactivity->id,
        0,
        $grades,
        $params
    );
}

function customactivity_update_grades(stdClass $customactivity, $userid = 0) {
    global $DB;
    if ($userid) {
        $sql = "SELECT MAX(iscorrect) AS gotcorrect
                FROM {customactivity_submissions}
                WHERE customactivityid = :cid AND userid = :uid";
        $rec = $DB->get_record_sql($sql, ['cid' => $customactivity->id, 'uid' => $userid]);
        $grade = ($rec && $rec->gotcorrect) ? 100 : 0;
        $grades = (object) ['userid' => $userid, 'rawgrade' => $grade];
        customactivity_grade_item_update($customactivity, $grades);
    } else {
        $subs = $DB->get_records('customactivity_submissions', ['customactivityid' => $customactivity->id]);
        $grades = [];
        foreach ($subs as $s) {
            if (!isset($grades[$s->userid])) {
                $grades[$s->userid] = 0;
            }
            if ($s->iscorrect) {
                $grades[$s->userid] = 100;
            }
        }
        $final = [];
        foreach ($grades as $uid => $g) {
            $final[] = (object)['userid' => $uid, 'rawgrade' => $g];
        }
        if ($final) {
            customactivity_grade_item_update($customactivity, $final);
        }
    }
}

function customactivity_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    send_file_not_found();
}

function customactivity_get_coursemodule_info($cm) {
    global $DB;
    $info = new cached_cm_info();
    $record = $DB->get_record('customactivity', ['id' => $cm->instance]);
    if (!$record) {
        return null;
    }
    if ($record->intro) {
        $info->content = format_module_intro('customactivity', $record, $cm->id, false);
    }
    return $info;
}


function customactivity_log($message) {
    // You can integrate with Moodle events here later
}





