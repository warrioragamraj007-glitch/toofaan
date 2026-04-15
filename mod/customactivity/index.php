<?php
require_once('../../config.php');
$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id'=>$courseid], '*', MUST_EXIST);
require_course_login($course);
$PAGE->set_url('/mod/customactivity/index.php', ['id'=>$courseid]);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginnameplural', 'mod_customactivity'));
$activities = $DB->get_records('customactivity', ['course'=>$courseid]);
echo html_writer::start_tag('ul');
foreach ($activities as $a) {
    $cm = get_coursemodule_from_instance('customactivity', $a->id);
    $url = new moodle_url('/mod/customactivity/view.php', ['id'=>$cm->id]);
    echo html_writer::tag('li', html_writer::link($url, format_string($a->name)));
}
echo html_writer::end_tag('ul');
echo $OUTPUT->footer();
