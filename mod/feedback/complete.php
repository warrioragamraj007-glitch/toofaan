<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * prints the form so the user can fill out the feedback
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */

require_once("../../config.php");
require_once("lib.php");

feedback_init_feedback_session();

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$gopage = optional_param('gopage', 0, PARAM_INT);
$gopreviouspage = optional_param('gopreviouspage', null, PARAM_RAW);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
$feedback = $DB->get_record("feedback", array("id" => $cm->instance), '*', MUST_EXIST);

$urlparams = array('id' => $cm->id, 'gopage' => $gopage, 'courseid' => $courseid);
$PAGE->set_url('/mod/feedback/complete.php', $urlparams);

require_course_login($course, true, $cm);
$PAGE->set_activity_record($feedback);

$context = context_module::instance($cm->id);
$feedbackcompletion = new mod_feedback_completion($feedback, $cm, $courseid);

$courseid = $feedbackcompletion->get_courseid();

// Check whether the feedback is mapped to the given courseid.
if (!has_capability('mod/feedback:edititems', $context) &&
        !$feedbackcompletion->check_course_is_mapped()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('cannotaccess', 'mod_feedback'));
    echo $OUTPUT->footer();
    exit;
}

//check whether the given courseid exists
if ($courseid AND $courseid != SITEID) {
    require_course_login(get_course($courseid)); // This overwrites the object $COURSE .
}

if (!$feedbackcompletion->can_complete()) {
    print_error('error');
}

$PAGE->navbar->add(get_string('feedback:complete', 'feedback'));
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
$PAGE->set_pagelayout('incourse');
$PAGE->set_secondary_active_tab('modulepage');
$PAGE->add_body_class('limitedwidth');

// Check if the feedback is open (timeopen, timeclose).
if (!$feedbackcompletion->is_open()) {
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo $OUTPUT->notification(get_string('feedback_is_not_open', 'feedback'));
    echo $OUTPUT->continue_button(course_get_url($courseid ?: $feedback->course));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

// Mark activity viewed for completion-tracking.
if (isloggedin() && !isguestuser()) {
    $feedbackcompletion->set_module_viewed();
}

// Check if user is prevented from re-submission.
$cansubmit = $feedbackcompletion->can_submit();

// Initialise the form processing feedback completion.
if (!$feedbackcompletion->is_empty() && $cansubmit) {
    // Process the page via the form.
    $urltogo = $feedbackcompletion->process_page($gopage, $gopreviouspage);

    if ($urltogo !== null) {
        redirect($urltogo);
    }
}

// Print the page header.
$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

echo $OUTPUT->header();

$PAGE->requires->js('/local/student/customchanges.js');

if ($feedbackcompletion->is_empty()) {
    \core\notification::error(get_string('no_items_available_yet', 'feedback'));
} else if ($cansubmit) {
    if ($feedbackcompletion->just_completed()) {
        // Display information after the submit.
        if ($feedback->page_after_submit) {
            echo $OUTPUT->box($feedbackcompletion->page_after_submit(),
                    'generalbox boxaligncenter');
        }
        if (!$PAGE->has_secondary_navigation() && $feedbackcompletion->can_view_analysis()) {
            echo '<p class="text-center">';
            $analysisurl = new moodle_url('/mod/feedback/analysis.php', array('id' => $cm->id, 'courseid' => $courseid));
            echo html_writer::link($analysisurl, get_string('completed_feedbacks', 'feedback'));
            echo '</p>';
        }

        if ($feedback->site_after_submit) {
            $url = feedback_encode_target_url($feedback->site_after_submit);
        } else {
            $url = course_get_url($courseid ?: $course->id);
        }
        global $CFG,$USER;
        if(!user_has_role_assignment($USER->id,5)){
             echo $OUTPUT->continue_button($url); //removing continue button
        }
    } else {
        // Display the form with the questions.
        echo $feedbackcompletion->render_items();
    }
} else {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo $OUTPUT->notification(get_string('this_feedback_is_already_submitted', 'feedback'));
    echo $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();

if(user_has_role_assignment($USER->id,5)){
?>

<script>
    /** to remove links of courses and added dashboard links/activity name start */
    var baseUrl = '<?php echo $CFG->wwwroot ?>';
    var url = baseUrl + '/local/student/dashboard.php';

    // Remove the element with class 'page-context-header'.
    document.querySelector('.page-context-header').remove();

    // Get the activity name based on the course module ID
    var activityName = '<?php echo ucfirst(get_activity_name($id)); ?>';

    // Set the HTML content for the element with id 'page-navbar'.
    document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: center;'> <span style='margin: 0 5px;'></span> <div style='white-space: nowrap; overflow: hidden; width: 400px;'><h4>" + activityName + "</h4></div> </div>";
    // Set the 'href' attribute for the element with id 'dlink'.
    document.getElementById('dlink').href = url;
    /** to remove links of courses and added dashboard links / activity name end */



</script>
<?php
}
?>
<?php
// Function to get the activity name based on the course module ID
function get_activity_name($id) {
    $coursemodule = get_coursemodule_from_id('', $id, 0, false, MUST_EXIST);
    return $coursemodule->name;
}
?>
