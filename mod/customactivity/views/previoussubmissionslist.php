<?php
require_once('../../../config.php');

$id     = required_param('id', PARAM_INT);      // Course module ID
$userid = required_param('userid', PARAM_INT);  // Student ID

$cm       = get_coursemodule_from_id('customactivity', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$activity = $DB->get_record('customactivity', ['id' => $cm->instance], '*', MUST_EXIST);
$context  = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/customactivity:viewsubmissions', $context); 

$PAGE->set_url('/mod/customactivity/views/previoussubmissionslist.php', ['id' => $id, 'userid' => $userid]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(fullname($DB->get_record('user', ['id' => $userid])) . ' - Submission');

echo $OUTPUT->header();

// Get student
$student = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Get final submission (questionid = 0) — contains grade and feedback
$finalsub = $DB->get_record('customactivity_submissions', [
    'customactivityid' => $activity->id,
    'userid'           => $userid,
    'questionid'       => 0
]);

if (!$finalsub) {
    echo $OUTPUT->notification('This student has not completed and submitted the activity yet.', 'info');
    echo $OUTPUT->single_button(new moodle_url('/mod/customactivity/submissions.php', ['id' => $cm->id]), 'Back to Submissions List');
    echo $OUTPUT->footer();
    exit;
}

// Get all questions in order
$questions = $DB->get_records('customactivity_questions', ['customactivityid' => $activity->id], 'qno ASC');

// Get student's answers (tempsave from per-question rows)
$answers = [];
$perq = $DB->get_records_select('customactivity_submissions', 
    "customactivityid = ? AND userid = ? AND questionid > 0", 
    [$activity->id, $userid],
    '',
    'questionid, tempsave, timespent'
);

foreach ($perq as $row) {
    $answers[$row->questionid] = [
        'answer'    => $row->tempsave ?? '(No answer provided)',
        'timespent' => $row->timespent ?? 0
    ];
}

// Display header info
echo '<div style="text-align:center; margin-bottom:30px;">';
echo '<h2>' . fullname($student) . ' - ' . format_string($activity->name) . '</h2>';
echo '<p><strong>Submitted on:</strong> ' . userdate($finalsub->timecreated) . '</p>';
echo '<p><strong>Final Grade:</strong> 
      <span style="font-size:2em; font-weight:bold; color:' . ($finalsub->grade >= 70 ? '#27ae60' : '#e74c3c') . ';">
          ' . number_format($finalsub->grade, 2) . ' / 100
      </span>
      </p>';
echo '</div>';

// AI Feedback
if (!empty($finalsub->feedback)) {
    echo '<div style="margin:30px 0; padding:20px; background:#f8f9fa; border-left:5px solid #3498db; border-radius:8px;">';
    echo '<h3>AI Feedback</h3>';
    echo '<p>' . nl2br(format_text($finalsub->feedback)) . '</p>';
    echo '</div>';
}

// Questions and Answers Table
$table = new html_table();
$table->head = ['S.no', 'Question', "Student's Answer", 'Time Spent'];
$table->align = ['center', 'left', 'left', 'center'];
$table->attributes['class'] = 'generaltable';

$qno = 1;
foreach ($questions as $q) {
    $studentdata = $answers[$q->id] ?? ['answer' => '(No answer)', 'timespent' => 0];

    $row = new html_table_row([
        $qno++,
        '<strong>Q' . $q->qno . ':</strong><br>' . format_text($q->questiontext),
        '<div style="white-space:pre-wrap; max-width:500px; padding:10px; background:#f4f4f4; border-radius:6px;">' 
            . s($studentdata['answer']) . 
        '</div>',
        $studentdata['timespent'] . ' seconds'
    ]);

    $table->data[] = $row;
}

echo html_writer::table($table);

// Back button
echo '<div style="text-align:center; margin-top:40px;">';
echo $OUTPUT->single_button(
    new moodle_url('/mod/customactivity/submissions.php', ['id' => $cm->id]),
    'Back to Submissions List',
    'get'
);
echo '</div>';

echo $OUTPUT->footer();