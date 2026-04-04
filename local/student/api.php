<?php
require_once('../../config.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
// Load the page_requirements_manager
$PAGE->requires->css('/theme/boost/scss/bootstrap/_tables.scss');
$PAGE->set_title('API Reference');
$PAGE->set_context(context_system::instance());
global $USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}

echo $OUTPUT->header();
$PAGE->requires->js('/local/student/customchanges.js');
// echo "<h3>API Reference</h3>";

echo '<div class="table-responsive">'; // Added a responsive container
echo '<table class="table table-striped table-hover">'; // Added Bootstrap table classes

// echo '<thead class="thead-dark">'; // Added dark header style
echo '<thead style="background-color: #ea6645; color: #fff;">';
echo '<tr>';
echo '<th style="padding-left:2%;">S No</th>';
echo '<th style="padding-left:2%;">Language</th>';
echo '<th style="padding-left:2%;">Version</th>';
echo '<th style="padding-left:2%;">API Link</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
echo '<tr class="loc">';
echo '<td style="padding-left:2%;">1</td>';
echo '<td style="padding-left:2%;">Java</td>';
echo '<td style="padding-left:2%;">17</td>';
echo '<td style="padding-left:2%;"><a href="'.$CFG->wwwroot.'/local/student/api/java/index.html" target="_blank" ><i class="fa fa-external-link" aria-hidden="true"></i></a></td>';
echo '</tr>';
echo '<tr class="loc">';
echo '<td style="padding-left:2%;">2</td>';
echo '<td style="padding-left:2%;">Python</td>';
echo '<td style="padding-left:2%;">3.14</td>';
echo '<td style="padding-left:2%;"><a href="'.$CFG->wwwroot.'/local/student/api/python/contents.html" target="_blank" ><i class="fa fa-external-link" aria-hidden="true"></i></a></td>';
echo '</tr>';
echo '<tr class="loc">';
echo '<td style="padding-left:2%;">3</td>';
echo '<td style="padding-left:2%;">PHP</td>';
echo '<td style="padding-left:2%;">4.X to 7.X</td>';
echo '<td style="padding-left:2%;"><a href="'.$CFG->wwwroot.'/local/student/api/php/index.html" target="_blank" ><i class="fa fa-external-link" aria-hidden="true"></i></a></td>';
echo '</tr>';
echo '<tr class="loc">';
echo '<td style="padding-left:2%;">4</td>';
echo '<td style="padding-left:2%;">C & CPP</td>';
echo '<td style="padding-left:2%;">C++98, C++03, C++11, C++14, C++17, C++20, C++23 </td>';
echo '<td style="padding-left:2%;"><a href="'.$CFG->wwwroot.'/local/student/api/gcc/index.html" target="_blank" ><i class="fa fa-external-link" aria-hidden="true"></i></a></td>';
echo '</tr>';
echo '</tbody>';
echo '</table>';
echo '</div>'; // Close the responsive container
echo $OUTPUT->footer();
?>
