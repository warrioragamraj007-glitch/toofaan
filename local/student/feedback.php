<?php
require_once('../../config.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
// Load the page_requirements_manager
$PAGE->requires->css('/local/student/styles/custom.css');

$PAGE->set_title('Feedback');
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();
$PAGE->requires->js('/local/student/customchanges.js');


// echo "Feedback Activities:";

global $DB,$USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
$now = strtotime('now');
echo '<div style="text-align: right; margin-right: 4%; position:relative; top:-6px; text-transform: capitalize;">';
echo '<a href="javascript:document.location.reload()" class="btn btn-primary"><i class="icon-refresh"></i> Refresh</a>';
echo '</div>';
echo "<div id='paged-content-container-1' data-region='paged-content-container'>";
echo "<div id='page-container-1' data-region='page-container' class='paged-content-page-container' aria-live='polite'>";
echo "<div data-region='paged-content-page' data-page='1' class=''>";
echo "   <ul class='list-group'>";
$enrolledcourses = enrol_get_users_courses($USER->id);

$activitiesFound = false; // Flag to check if there are any activities

if (!empty($enrolledcourses)) {

    foreach ($enrolledcourses as $course) {
        $coursename = $course->fullname;
        $feedbackActivities = $DB->get_records('feedback', array('course' => $course->id));

        if (!empty($feedbackActivities)) {
            foreach ($feedbackActivities as $feedbackActivity) {
                // Get the course module ID for the feedback activity
                $actname = $feedbackActivity->fullname;
                $cm = get_coursemodule_from_instance('feedback', $feedbackActivity->id, $course->id);
                $cmid = $cm->id;

                if (!is_activity_deletion_in_progress($cmid) && $cm->visible &&  is_activity_started_and_has_status($cmid, $now, 1)) {
                    $activitiesFound = true; // At least one activity found
                     // Fetch the start date for the activity
                     $startdate = userdate($DB->get_field('activity_status_tsl', 'activity_start_time', array('activityid' => $cmid)));

                    echo "<li class='list-group-item block course-listitem border-left-0 border-right-0 border-top-0 px-2 rounded-0' data-region='course-content' data-course-id='$course->id' style='background-color: #f0f0f0; margin: 10px 0;'>";
                    echo '<div class="col-md-3"><div class="imagecours"><div class="tleft">';
                    echo '<div class="coursename"><div>' . ucfirst($coursename) . '</div></div><div class="topicname"><span>';
                    echo 'Feedback</span></div></div></div></div>';
                    // echo '<div class="col-md-9 s2 inner"><header><h2 class="course-date" style="margin-bottom:5px !important;">' . $feedbackActivity->name  . '</h2><span class="pull-right course-joined" style="text-align:right;"><br><div class="pull-right" style="font-size:0.7em;"><span><span class="count-descriptio"> </span></span></div>
                    // </span></header><br><hr>';

                    echo '<div class="col-md-9 s2 inner"><header><h2 class="course-date" style="margin-bottom:5px !important;">' . ucfirst($feedbackActivity->name)  . '</h2><span class="pull-right course-joined" style="text-align:right;"><i class="fa fa-check-circle-o" style="margin-right: 5px;color:#ea6645"></i>Started<br><div class="pull-right" style="font-size:0.7em;"><i class="fa fa-clock-o" style="color:rgb(197, 197, 197);"></i> <span style="color:rgb(118, 118, 118);">on ' . $startdate . '<span class="count-descrition">  </span></span><span><span class="count-descriptio"> </span></span></div>
                    </span></header><br><hr>';


                    // echo "<h2>$feedbackActivity->name</h2>";
                    echo $feedbackActivity->intro;
                    // echo '<a href="' . $CFG->wwwroot . '/mod/feedback/view.php?id=' . $cmid . '">Attempt</a>';
                    $reflink = $CFG->wwwroot . '/mod/feedback/view.php?id=' . $cmid;
                    echo '<a class="btn btn-primary btn-lg pull-right" target="_blank" href=' . $reflink . ' value="submit" ><span>Give Feedback</span></a>';
                    echo '</div>';
                    echo '</li>';
                }
            }
        }
    }

    if (!$activitiesFound) {
        // echo "<p>No activities found for the user.</p>";
        echo "<h4 style='text-align:center;margin:4em;'>No activities found for the user </h4>";
    }

} else {
    echo "<p>No enrolled courses found for the user.</p>";
}
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo $OUTPUT->footer();

function is_activity_deletion_in_progress($activity_id)
{
    global $DB;
    // Check if deletioninprogress is 1
    return $DB->record_exists('course_modules', ['id' => $activity_id, 'deletioninprogress' => 1]);
}

function is_activity_started_and_has_status($activity_id, $current_time, $status_value)
{
    global $DB;
    $activity_start_time = $DB->get_field('activity_status_tsl', 'activity_start_time', array('activityid' => $activity_id));
    $status = $DB->get_field('activity_status_tsl', 'status', array('activityid' => $activity_id));

    // Check if the activity has status = $status_value and the start time is before or equal to the current time
    return ($status == $status_value && $activity_start_time && $activity_start_time <= $current_time);
}
?>
