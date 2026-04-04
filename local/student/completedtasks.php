<?php
require_once('../../config.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
// Load the page_requirements_manager
$PAGE->requires->css('/local/student/styles/custom.css');
$PAGE->set_title('Completed Tasks');
$PAGE->set_context(context_system::instance());
global $USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
echo $OUTPUT->header();
$PAGE->requires->js('/local/student/customchanges.js');
// echo "<h3>Completed Tasks</h3>";

echo '<div id="paged-content-container-1" data-region="paged-content-container">';
echo '<div id="page-container-1" data-region="page-container" class="paged-content-page-container" aria-live="polite">';
echo '<div data-region="paged-content-page" data-page="1" class="">';
echo '<ul class="list-group">';

$enrolledcourses = enrol_get_users_courses($USER->id);
// var_dump($enrolledcourses);
foreach ($enrolledcourses as $course) {
    $courseid = $course->id;
    // var_dump($courseid);

    $today = strtotime(date("d-m-Y"));
    $now = strtotime('now');
    //   var_dump($today);
    //   var_dump($now);
    $started_activities = $DB->get_records_sql("SELECT *
        FROM {activity_status_tsl}
        WHERE  activity_start_time BETWEEN ? AND ?",
        array($today, $now)
    );


    foreach ($started_activities as $started_activity) {
        $started_act_id = $started_activity->activityid;

        $pre_result = '';

        $act = $DB->get_record('course_modules', array('id' => $started_act_id, 'course' => $courseid));
        $act_id = $act->id;
        $status = get_activity_status($act_id);
        $module = $act->module;
        $instance = $act->instance;
        $course = $courseid;
        $coursename = get_course($course)->fullname;
        $instance = $act->instance;

        $item_res = $DB->get_record('modules', array('id' => $module));
        $type = $item_res->name;

        switch ($type) {
            case 'quiz':
                // ... (rest of the code)
                $grade=0;
                $grading_info=grade_get_grades($course, 'mod', $type,$instance, $USER->id);

                $item = $grading_info->items[0];
                $gradeI= $item->grades[$USER->id];
                $grade = $gradeI->grade ;
                if(empty($grade)){
                    $grade=0;
                }
                $descact=$DB->get_field_sql("SELECT `intro`
                FROM `mdl_quiz`
                WHERE `id` = '$instance'");

                                $activityname=$DB->get_field_sql("SELECT `name`
                FROM `mdl_quiz`
                WHERE `id` = '$instance'");

                                $descact=strip_tags($descact);

                                $quiz_submission=0;
                                $attempts = $DB->get_record_sql("SELECT *
                FROM `mdl_quiz_attempts`
                WHERE `quiz` ='$instance'
                AND `userid` ='$USER->id'  ");

                $attempt_id=$attempts->id;





                if (!empty($attempts))
                {
                    $quiz_submission=1;
                    $grade=$grade+0;
                    $sublink=$CFG->wwwroot."/local/student/qreview.php?attempt=".$attempt_id;
                }


                printdataact($coursename, 'Quiz', $activityname, $descact, $quiz_submission, $grade, $sublink, $status,$courseid);
                break;

                case 'vpl':
                // ... (rest of the code)
                  $data = "";


                    $descact=$DB->get_field_sql("SELECT `intro`
                    FROM `mdl_vpl`
                    WHERE `id` ='$instance'");
                                        $activityname=$DB->get_field_sql("SELECT `name`
                    FROM `mdl_vpl`
                    WHERE `id` ='$instance'");
                                        $submissions = $DB->get_records_sql("SELECT  *
                    FROM `mdl_vpl_submissions`
                    WHERE `vpl` ='$instance'
                    AND `userid` ='$USER->id'");
                    //  $sub_id=0;
                    foreach($submissions as $submission)
                    {
                        $sub_id=$submission->id;
                    }

                    $grading_info=grade_get_grades($course, 'mod', $type,$instance, $USER->id);

                    $item = $grading_info->items[0];
                    $gradeI= $item->grades[$USER->id];
                    $usergrade  = $gradeI->grade ;
                    if(empty($usergrade)){
                        $usergrade=0;
                    }


                    if (is_array($submissions))
                    {
                        $check_latest_submitted_lab = 0;




                        //var_dump($descact);
                        $sublink=
                        $descact=strip_tags($descact);



                        if (is_array($usergrade))
                        {
                            $usergrade = $usergrade[0];
                        }
                        elseif ($usergrade == false)
                        {
                            $usergrade = "Not graded";

                        }
                        $usergrade=$usergrade+0;
                        $totalsubmissions=count($submissions);

                        $sublink =$CFG->wwwroot.'/local/student/vplreview.php?id='.$act_id.'&submissionid='.$sub_id;


                    }
                    printdataact($coursename, 'Lab', $activityname, $descact, $totalsubmissions, $usergrade, $sublink, $status,$courseid);
                    break;


        }
    }


}
print <<<END






END;
if ($counting == 0) {
    echo '<h1 style="text-align:center;margin:4em;">No completed tasks...!</h1>';
}

function printdataact($cname, $activity_type, $activity_name, $description, $no_of_submission, $act_grade, $sublink, $status,$courseid)
{
    // Apply the same styles as in the "Current Tasks" code
    echo '<li class="list-group-item block course-listitem border-left-0 border-right-0 border-top-0 px-2 rounded-0" data-region="course-content" data-course-id=' . $courseid . '" style="background-color: #f0f0f0; margin: 10px 0;">';
    echo '<div class="col-md-3"><div class="imagecours"><div class="tleft">';
    echo '<div class="coursename"><div>' . ucfirst( $cname) . '</div></div>';
    echo '<div class="topicname"><span>' . strtoupper($activity_type) . '</span></div>';
    echo '</div></div></div>';

    echo '<div class="col-md-9 s2 inner"><header><h2 class="course-date" style="margin-bottom:5px !important;">' . ucfirst($activity_name) . '</h2><span class="pull-right course-joined" style="text-align:right;">'. $status['status'].'<br><div class="pull-right" style="font-size:0.7em;"><i class="fa fa-clock-o" style="color:rgb(197, 197, 197);"></i> <span style="color:rgb(118, 118, 118);">on ' . $status[time] . '<span class="count-descrition">  </span></span><span><span class="count-descriptio"> </span></span></div>
    </span></header><br><hr>';
    echo '<p class="description"><span>' . $description . '</span></p>';
    echo '<div class="pull-right">';
    if($no_of_submission==0){
        $sublink="#";
    }
    echo "<a class='btn btn-primary pull-right sub-".$no_of_submission."' value='submit' >Grade<br><span>";
    echo $act_grade."";
    echo "<a class='btn btn-primary pull-right sub-".$no_of_submission."' value='submit' target='_blank' href='".$sublink."' style='margin-right:5px;'>
 Submissions<br>";
        echo $no_of_submission;
        echo '</span></a>';


    echo '</div>';

    echo '</div>';


    echo '</li>';


    GLOBAL $counting;
    $counting = $counting + 1;
}

function get_activity_status($act_id)
{
    $status = array();
    global $DB;
    $result = $DB->get_records('activity_status_tsl', array('activityid' => $act_id));
    foreach ($result as $res) {
        if ($res->status == "0") {
            $status['status'] = '<span style="color:red;">STOPPED</span>';
            $status['time'] = userdate($res->activity_stop_time);
        } elseif ($res->status == "1") {
            $status['status'] = '<i class="fa fa-check-circle-o" style="margin-right: 5px; color:#ea6645"></i>INPROGRESS';
            $status['time'] = userdate($res->activity_start_time);
        } elseif ($res->status == "2") {
            $status['status'] = '<span style="color:red;">CLOSED</span>';
            $status['time'] = userdate($res->activity_close_time);
        } else {
            $status['status'] = "Undefined";
        }
    }
    return $status;
}
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo $OUTPUT->footer();
?>
