<?php

require_once('../../config.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
$PAGE->requires->css('/theme/boost/scss/bootstrap/_tables.scss');
$PAGE->set_title('Study Material');
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();
$PAGE->requires->js('/local/student/customchanges.js');

global $DB;
global $USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}

// Check if a specific course is selected
$selectedCourseId = optional_param('courseid', 0, PARAM_INT);

$enrolledcourses = enrol_get_users_courses($USER->id);
if ($enrolledcourses) {
    // Display the course selection dropdown
    echo '<form method="get"  style="margin-bottom: 20px;">';
    echo '<label for="course">Select Course: </label><br>';
    echo '<select name="courseid" id="course" onchange="this.form.submit()">';
    echo '<option value="0">Select Course</option>';

    foreach ($enrolledcourses as $course) {
        $course_id = $course->id;
        $course_name = $course->fullname;

        // Output each course as an option in the dropdown
        echo '<option value="' . $course_id . '" ' . ($selectedCourseId == $course_id ? 'selected' : '') . '>' . ucfirst($course_name) . '</option>';
    }

    echo '</select>';
    echo '</form>';

    // Display the table only if a course is selected
    if ($selectedCourseId > 0) {
        $selectedCourse = get_course($selectedCourseId);

        echo '<h3>' . ucfirst($selectedCourse->fullname) . '</h3>';

        // Rest of the code to display the table for the selected course
        $courseid = $selectedCourse->id;

        // Get topics for the current course
        $topics = $DB->get_records('course_sections', array('course' => $courseid), 'section');

        $materialsFound = false; // Flag to check if there are any materials

        echo '<div class="table-responsive" style="margin-top: 20px;">';
        echo '<table class="table table-striped table-hover">';
        echo '<thead style="background-color: #ea6645; color: #fff;"><tr><th style="width: 40%;">Topic</th><th style="width: 40%;">Material</th><th style="width: 40%;">Action</th></tr></thead>';
        echo '<tbody>';

        foreach ($topics as $topic) {
            // Access the topic information
            $topicid = $topic->id;
            $topicname = $topic->name;

            // Get file activities for the current topic
            $file_activities = get_array_of_file_activities_in_topic($courseid, $topicid);

            foreach ($file_activities as $activity) {
                $actname = $activity->name;
                $actid = $activity->id;

                if (!is_activity_deletion_in_progress($actid)) {
                    // Fetch the resource name from the 'resource' table
                    $resource_name = get_resource_name($activity->instance);

                    // Generate the download link
                    $download_url = generate_download_url($actid);

                    // Check if the activity is visible
                    $is_visible = $DB->get_field('course_modules', 'visible', array('id' => $actid));

                    // Set the flag to true as there is at least one visible material
                    $materialsFound = $materialsFound || $is_visible;

                    if ($is_visible) {
                        echo '<tr class="loc">';
                        echo "<td style='padding-left:2%;' class='text-left'>$topicname</td>";
                        echo "<td style='padding-left:2%;' class='text-left'>$resource_name</td>";
                        echo "<td style='padding-left:2%;' class='text-left'><a href='$download_url' download target='_blank'><i class='fa fa-download' aria-hidden='true'></i> Download</a></td>";
                        echo '</tr>';
                    }
                }
            }

        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        // If no materials are found, display a message
        if (!$materialsFound) {
            echo '<div class="table-responsive" style="margin-top: 20px;">';
            echo '<table class="table table-striped table-hover">';
            // echo '<thead style="background-color: #ea6645; color: #fff;"><tr><th style="width: 40%;">Topic</th><th style="width: 40%;">Material</th><th style="width: 40%;">Action</th></tr></thead>';
            echo '</table>';
            echo '</div>';
            echo "<h5 style='text-align:center;'><p>No study material available for this course.</p></h5>";
        }
    }
    else{
        echo '<div class="table-responsive" style="margin-top: 20px;">';
        echo '<table class="table table-striped table-hover">';
        echo '<thead style="background-color: #ea6645; color: #fff;"><tr><th style="width: 40%;">Topic</th><th style="width: 40%;">Material</th><th style="width: 40%;">Action</th></tr></thead>';
        echo '<tbody>';
        echo '</table>';
        echo '</div>';
        echo "<h5 style='text-align:center;'><p>Select a course to view material.</p></h5>";
    }
} else {
    echo "No courses found.";
}
echo $OUTPUT->footer();


function get_array_of_file_activities_in_topic($courseid, $topicid)
{
    global $DB;

    // Assuming 'resource' is the module ID for the file activity in Moodle
    $file_module_id = $DB->get_field('modules', 'id', array('name' => 'resource'));

    $sql = "SELECT cm.id, m.name, cm.instance
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            JOIN {course_sections} cs ON cm.section = cs.id
            JOIN {course} c ON cm.course = c.id
            WHERE cm.course = ? AND m.id = ? AND cs.course = ? AND cs.id = ?
                  AND (c.visible = 1 OR cm.visible = 1)";

    return $DB->get_records_sql($sql, array($courseid, $file_module_id, $courseid, $topicid));
}


function get_resource_name($resourceid)
{
    global $DB;

    $sql = "SELECT name
            FROM {resource}
            WHERE id = ?";

    return $DB->get_field_sql($sql, array($resourceid));
}

function is_activity_deletion_in_progress($activity_id)
{
    global $DB;
    // Check if deletioninprogress is 1
    return $DB->record_exists('course_modules', ['id' => $activity_id, 'deletioninprogress' => 1]);
}

function generate_download_url($activity_id)
{
    global $CFG;
    return $CFG->wwwroot . '/mod/resource/view.php?id=' . $activity_id; // Modify this based on your specific Moodle setup
}

?>


<style>
    #course{
        width : 200px;
        padding : 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
        margin-right: 20px;
    }
</style>