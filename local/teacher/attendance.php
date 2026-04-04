<?php
require('../../config.php');
require_login();
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();

$teacher_courses = enrol_get_my_courses();
?>
        <div id="sectionContainer">
            <form id="attendance-form" class="d-flex flex-row">
                <div class="col">
                    <label for="course-select-attendance">Select a course:</label>
                    <br>
                    <select name="selected_course" id="course-select-attendance">
                        <option value="">Select a course</option>
                        <?php
                        foreach ($teacher_courses as $course) {
                            echo "<option value=\"$course->id\">$course->fullname</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col">
                    <button id="get-attendance" style="margin-top: 23px;">Get Attendance</button>
                </div>
            </form>

            <section id="attendance-content" aria-label="Attendance Content">
                <!-- Content will be dynamically added here based on the selected course -->
            </section>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="/moodle/local/teacher/styles.css">
<!-- Additional scripts and stylesheets as needed -->

<script>
    $(document).ready(function () {
        $('#get-attendance').click(function (e) {
            e.preventDefault(); // Prevent the default form submission

            var selected_course = $('#course-select').val();
            var attendanceContent = $('#attendance-content');

        console.log(selected_course); 
            if (selected_course) {
                // Perform AJAX request to get attendance data based on the selected course
                $.ajax({
                    url: '/moodle/local/teacher/attendance_data.php', // Replace with your actual URL
                    data: { "selected_course": selected_course },
                    type: "POST",
                    dataType: "html",
                    success: function (data) {
                        // Update the content inside the section container with the received data
                        attendanceContent.html(data);
                    },
                    error: function (xhr, status) {
                        alert("Sorry, there was a problem!");
                    }
                });
            } else {
                // Handle the case when no course is selected
                attendanceContent.html('<p>Please select a course to get attendance.</p>');
            }
        });
    });
</script>


<?php
echo $OUTPUT->footer();
?>
