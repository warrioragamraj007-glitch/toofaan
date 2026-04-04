<?php
require_once('../../config.php');

require_login();
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$PAGE->set_title('Tessellator 5.0 - Dashboard');

// $PAGE->set_heading(get_string('pluginname', 'local_student'));

echo $OUTPUT->header();

// redirect($CFG->wwwroot);
echo $OUTPUT->footer();
?>