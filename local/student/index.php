<?php
require_once('../../config.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$PAGE->set_title('Tessellator 5.0 - Dashboard');

$PAGE->set_heading(get_string('pluginname', 'local_student'));

echo $OUTPUT->header();

// Content to display on the page
echo '<p>This is a student.</p>';
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
else{
    redirect($CFG->wwwroot.'/local/student/dashboard.php');
}
echo $OUTPUT->footer();
?>