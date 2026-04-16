<?php
// tempsave.php
require_once('../../config.php');

$id        = required_param('id', PARAM_INT);
$qid       = required_param('q', PARAM_INT);
$timespent = optional_param('timespent', 0, PARAM_INT);
$answer    = optional_param('answer', null, PARAM_RAW); // null if not sent

$cm = get_coursemodule_from_id('customactivity', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$activity = $DB->get_record('customactivity', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
require_sesskey();

$record = $DB->get_record('customactivity_submissions', [
    'customactivityid' => $activity->id,
    'questionid'       => $qid,
    'userid'           => $USER->id
]);

$record = $record ?: new stdClass();
$record->customactivityid = $activity->id;
$record->questionid       = $qid;
$record->userid           = $USER->id;

// only update answer when actually sent
if ($answer !== null) {
    $record->tempsave = trim($answer);
}

$record->timespent = $timespent;
$record->ipaddress = getremoteaddr();

if (empty($record->id)) {
    $record->timecreated = time();
}
$record->timemodified = time();

if (!empty($record->id)) {
    $DB->update_record('customactivity_submissions', $record);
} else {
    $DB->insert_record('customactivity_submissions', $record);
}

// If final submit → go to grading
if (optional_param('finalsubmit', 0, PARAM_BOOL)) {
    redirect(new moodle_url('/mod/customactivity/finalgrade.php', [
        'id' => $cm->id,
        'sesskey' => sesskey()
    ]));
}

echo "saved";