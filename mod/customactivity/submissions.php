<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('customactivity', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$activity = $DB->get_record('customactivity', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);
require_login($course, true, $cm);
require_capability('mod/customactivity:viewsubmissions', $context);

$PAGE->set_url('/mod/customactivity/submissions.php', ['id' => $cm->id]);
$PAGE->set_title($activity->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading('Submissions List');

$sql = "SELECT s.*, u.firstname, u.lastname, u.id AS userid
          FROM {customactivity_submissions} s
          JOIN {user} u ON u.id = s.userid
         WHERE s.customactivityid = ?
           AND s.questionid = 0
      ORDER BY s.timecreated DESC";

$records = $DB->get_records_sql($sql, [$activity->id]);

$table = new html_table();
$table->attributes['class'] = 'generaltable gradingtable';

$table->head = [
    'First name',
    'Last name',
    'Submitted on',
    'Attempts',
    'Grade',
    'AI Evaluation',
    'Evaluator',
    'Graded on'
];

if (!$records) {
    echo $OUTPUT->notification('No submissions yet.', 'info');
} else {
    foreach ($records as $r) {
        $attempts = $DB->count_records('customactivity_submissions', [
            'customactivityid' => $activity->id,
            'userid' => $r->userid,
            'questionid' => 0
        ]);

        $row = new html_table_row();

        $row->cells[] = html_writer::link(
            new moodle_url('/user/view.php', ['id' => $r->userid]),
            $r->firstname
        );
        $row->cells[] = $r->lastname;
        $row->cells[] = $r->timecreated ? userdate($r->timecreated) : '-';

        $attempturl = new moodle_url(
            '/mod/customactivity/views/previoussubmissionslist.php',
            ['id' => $cm->id, 'userid' => $r->userid]
        );
        $row->cells[] = html_writer::link($attempturl, $attempts);

        $grade = number_format($r->grade, 2);
        $gradecell = new html_table_cell($grade . ' / 100');

        if ($r->grade >= 90) {
            $gradecell->style = 'color:green;font-weight:bold;';
        } elseif ($r->grade >= 50) {
            $gradecell->style = 'color:orange;font-weight:bold;';
        } else {
            $gradecell->style = 'color:red;font-weight:bold;';
        }
        $row->cells[] = $gradecell;

        $row->cells[] = !empty($r->feedback)
            ? html_writer::div(
                nl2br(s($r->feedback)),
                [
                    'style' =>
                        'font-size:13px;background:#f8f9fa;
                         padding:10px;border-radius:6px;
                         border:1px solid #ddd;max-width:400px;'
                ]
            )
            : '-';

        $row->cells[] = 'AI Graded';
        $row->cells[] = $r->timecreated ? userdate($r->timecreated) : '-';

        $table->data[] = $row;
    }

    echo html_writer::table($table);
}

$csvurl = new moodle_url('/mod/customactivity/exportcsv.php', ['id' => $cm->id]);
echo html_writer::div($OUTPUT->single_button($csvurl, 'Download CSV'), 'mt-3');

echo $OUTPUT->footer();
