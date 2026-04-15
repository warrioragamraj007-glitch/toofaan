<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('customactivity', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$custom = $DB->get_record('customactivity', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/customactivity:viewsubmissions', $context);

// Fetch all submissions
$subs = $DB->get_records('customactivity_submissions',
    ['customactivityid' => $custom->id],
    'timecreated DESC'
);

// Clean CSV output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="customactivity_export_' . $custom->id . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Start CSV
$out = fopen('php://output', 'w');

// CSV Header row
fputcsv($out, [
    'First name',
    'Last name',
    'Submitted on',
    'Attempts',
    'Grade',
    'Answer',
    'AI Evaluation',
    'Evaluator',
    'Graded on',
    'IP Address',
    
]);

// Loop through submissions
foreach ($subs as $s) {

    $user = $DB->get_record('user', ['id' => $s->userid], 'firstname, lastname');

    $submitted = userdate($s->timecreated, '%A, %d %B %Y, %I:%M %p');
    $gradedon = $s->submissiontime ? userdate($s->submissiontime, '%A, %d %B %Y, %I:%M %p') : '-';

    $evaluator = $s->iscorrect ? 'AI' : 'Graded';

    $grade = ($s->grade !== null) ? $s->grade . '/100' : '-';

    fputcsv($out, [
        $user->firstname,
        $user->lastname,
        $submitted,
        $s->attemptno,
        $grade,
         trim($s->answer ?? ''),
        trim($s->feedback ?? ''),
        $evaluator,
        $gradedon,
        $s->ipaddress ?? '',
       
    ]);
}

fclose($out);
exit;
