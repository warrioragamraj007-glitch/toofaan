<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_login();

$PAGE->set_context(context_system::instance());
//$context = context_user::instance($USER->id);
$PAGE->set_context($context);

// Course lib for getting activities
require_once($CFG->dirroot . '/course/lib.php');
$PAGE->set_title('Reports');

echo $OUTPUT->header();
echo "<h3>Reports</h3>";

$context = context_user::instance($USER->id);

$userobj = get_complete_user_data(id, $USER->id);

$timg = $OUTPUT->user_picture($userobj, array('size' => 150, 'alttext' => $userobj->firstname . ' ' . $userobj->lastname, 'title' => 'Name:', 'link' => false));
?>
<div class="col-md-12">
    <div class="row">
        <div class="col-md-9">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th style="padding-left:8%;">S No</th>
                        <th style="padding-left:8%;">Course</th>
                        <th style="padding-left:8%;">Overall Course Report</th>
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
                                $link = $CFG->wwwroot . "/report/outline/user.php?id=" . $USER->id . "&course=" . $value->id . "&mode=outline";
                                echo "<tr><td>" . $cou++ . "</td><td> " . $value->fullname . "</td><td><a href=" . $link . " target='_blank'>View Report</a></td></tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-3 " style="padding-top: 2%;
    padding-bottom: 2%;border: 2px solid #dbdada;
    border-radius: 16px;hedight:200px;margin-top:1%;">
        <figure class="course-image">
                <div class="image-wrapper"><?php echo $timg ?></div>
            </figure>
            <br/>
            <p class="course-date"><b>NAME  : <?php echo ucfirst($userobj->firstname ."  ". $userobj->lastname); ?></b></p>

            <p class="course-date"><b>Roll No : <?php echo ucfirst($userobj->username) ?></b></p>
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
<style>
.image-wrapper {
    width: 150px; /* Adjust the width as needed */
    height: 150px; /* Adjust the height as needed */
    border: 2px solid #dbdada;
    border-radius: 10px; /* Adjust the border radius as needed */
    margin: 0 auto;
    overflow: hidden;
}
</style>
