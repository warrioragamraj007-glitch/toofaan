<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
$PAGE->set_context(context_system::instance());
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

// Course lib for getting activities
require_once($CFG->dirroot . '/course/lib.php');
global $USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
$PAGE->set_title('Reports');

echo $OUTPUT->header();
$PAGE->requires->js('/local/student/customchanges.js');
// echo "<h3>Reports</h3>";

$context = context_user::instance($USER->id);

$userobj = get_complete_user_data(id, $USER->id);

$timg = $OUTPUT->user_picture($userobj, array('size' => 150, 'alttext' => $userobj->firstname . ' ' . $userobj->lastname, 'title' => 'Name:', 'link' => false));
?>
<div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
            <table cellspacing="0" id="overview-grade" class="flexible table table-striped table-hover boxaligncenter generaltable">
            <thead style="background-color: #ea6645; color: #fff;">
                    <tr>
                        <th class="header c0" scope="col">S No</th>
                        <th class="header c1" scope="col">Course</th>
                        <th class="header c2" scope="col">Overall Course Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cou = 1;

                    if (user_has_role_assignment($USER->id, 5)) {
                        $enrolledcourses = enrol_get_users_courses($USER->id);
                        $no_courses = count($enrolledcourses);
                        foreach ($enrolledcourses as $key => $value) {
                            if (is_object($value)) {
                                $studentenrolledcourses[$value->id] = $value->fullname;
                                $link = $CFG->wwwroot . "/report/outline/studentreport.php?course=" . $value->id . "";
                                echo "<tr>
                                        <td class='cell c0'>" . $cou++ . "</td>
                                        <td class='cell c1'>" . ucfirst($value->fullname) . "</td>
                                        <td class='cell c2'><a href=" . $link . " target='_blank'>View Report</a></td>
                                      </tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<?php
echo $OUTPUT->footer();
?>

<?php
if ((user_has_role_assignment($USER->id, 5)) && ( strtotime("now") < strtotime("18-Dec-2016"))&& (!isset($_SESSION['userprofileupdate']))){
 $_SESSION['userprofileupdate']='set';
 echo '
             $j( document ).ready(function() {

                     $j(".iframe").fancybox({
                         type: \'iframe\',
                         preload   : true,
                         helpers     : {
                             overlay : {
                                 closeClick: false
                             }
                         },
                     }).trigger(\'click\');

             });';
 }
 ?>
