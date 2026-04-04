<?php
require_once('../../config.php');
require_login();
$PAGE->set_title('Study Material');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Course Material');
echo $OUTPUT->header();
global $DB;
if(user_has_role_assignment($USER->id,3)) {
    $context = context_user::instance($USER->id);
    $enrolledcourses = enrol_get_my_courses();
    $teacher_courses = enrol_get_my_courses();
}else{
    redirect($CFG->wwwroot);
}
// $teacher_courses = enrol_get_my_courses();
$PAGE->requires->js('/local/teacher/customchanges.js');
echo '<div  class="pagecover-onload">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT </div><div><img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/loading.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';

?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
<link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/fonts/all.min.css">
<style>
    .dashboard-table-container {
        position: relative;
    }

    .select-row {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    #course-form .col {
        margin-top: 0px;
        padding-top: 10px;
        margin-bottom: 10px;
        margin-left: -15px;
    }

    #course-select,
    #topic-select {
        width: 200px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
        margin-right: 20px;
    }

    #resource-activities-container {
        margin-top: 20px;
    }

    #resource-activities {
        margin-top: 10px;
    }

    #upload-link {
        margin-top: 20px;
        display: block;
        font-size: 14px;
    }

    .dashboard-table {
        width: 100%;
        margin: 0 auto;
        border-collapse: collapse;
        margin-top: 20px;
        text-align: center;
    }

    .dashboard-table th,
    .dashboard-table td {
        padding: 12px;
        vertical-align: top;
        border: 1px solid #ddd;
        text-align: center;
    }

    .dashboard-table tbody tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    .dashboard-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .toggle-icon {
        cursor: pointer;
    }
    #summary-button,#upload-button {
    background-color: #ea6645;
    border: none;
    color: #f4ecec;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 18px;
    cursor: pointer;
    border-radius: 5px;
    height: 45px;
    width: 200px;
    margin-bottom: 20px;
}
.dashboard-table th {
    background-color: #ea6645;
    color: #f4ecec;
    padding: 12px;
    vertical-align: top;
    border: 1px solid #ddd;
    text-align: center;
}
.pagecover-onload{
    display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 90%; top: 60px;margin-left: -10px;
    }
</style>
<div class="dashboard-table-container">
    <div class="select-row">
        <div class="col">
            <label for="course-select">Select a course:</label>
            <br>
            <select name="selected_course" id="course-select">
                <option value="">Select a course</option>
                <?php
                foreach ($teacher_courses as $course) {
                    echo "<option value=\"$course->id\">$course->fullname</option>";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <label for="topic-select">Select a topic:</label>
            <br>
            <select name="selected_topic" id="topic-select">
                <!-- Topics will be dynamically added here based on the selected course -->
            </select>
        </div>
        <div id="resource-activities-container" class="col">
            <button id="summary-button" class="btn btn-primary" style='background-color: #ea6645';>Get Material</button>
            <div id="resource-activities"></div>
        </div>
        <!-- <a id="upload-link" href="#"target="_blank">Upload Resource</a> -->
        <button id="upload-button" class="btn btn-primary" style='background-color: #ea6645';>
        Upload Material 
            </button>
    </div>
    <!-- <img src="<php echo $CFG->wwwroot;?>/pix/arrow-up.svg" alt="Arrow" title="Arrow" style="height:20px; width:16px;"> -->
    <table class="dashboard-table" id="your-table-id">
        <thead>
            <tr>
                <th>Files</th>
                <th>Download</th>
                <th>Edit</th>
                <th>Show/Hide</th>
            </tr>
        </thead>
        <tbody>
            <!-- File data will be populated here dynamically -->
        </tbody>
    </table>
</div>

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-3.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
<script>
    var baseUrl = '<?php echo $CFG->wwwroot . '/' ?>';

    $(document).ready(function () {
        $('#your-table-id').tablesorter();

        $('#course-select').change(function () {
            var selected_course = $('#course-select').val();
            var topicSelect = $('#topic-select');
            $(".pagecover-onload").css("display", "block");
            if (selected_course) {
                $.ajax({
                    url: baseUrl + "local/teacher/materiallib.php",
                    data: { "selected_course": selected_course },
                    type: "POST",
                    success: function (data) {
                        $('#topic-select').html(data);
                        $(".pagecover-onload").hide();
                    },
                    error: function (xhr, status) {
                        alert("Sorry, there was a problem!");
                    }
                });
            } else {
                topicSelect.empty();
            }
        });

        $('#summary-button').click(function () {
            var selectedCourse = $('#course-select').val();
            var selectedTopic = $('#topic-select').val();
            var selectedTopicOption = $('#topic-select option:selected');
            var selectedSection = selectedTopicOption.data('section');
            $(".pagecover-onload").css("display", "block");
            if (selectedCourse && selectedTopic) {
                $.ajax({
                    url: baseUrl + "local/teacher/materiallib.php",
                    data: { "selected_course": selectedCourse, "selected_topic": selectedTopic },
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        console.log("Received data:", data);
                        var tableBody = $('#your-table-id tbody');
                        tableBody.empty();
                        $(".pagecover-onload").hide();
                        if (data.length > 0) {
                            $.each(data, function (index, fileData) {
                                console.log("Processing fileData:", fileData);
                                // var initialVisibilityIcon = (fileData.isVisible == 1) ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                                var initialVisibilityIcon = (fileData.isVisible == 1) ? '<img src="<?php echo $CFG->wwwroot;?>/pix/view.png" alt="Hide" title="Hide" style="height:20px; width:20px;color: lightblue;">' : '<img src="<?php echo $CFG->wwwroot;?>/pix/eye-slash.svg" alt="Show" title="Show" style="height:20px; width:20px;">';

                                var row = [
                                    fileData.fileName,
                                    '<a href="' + fileData.downloadLink + '" target="_blank">Download</a>',
                                    '<a href="' + fileData.editLink + '" target="_blank">Edit</a>',
                                    '<span class="toggle-icon" data-file-id="' + fileData.fileId + '" data-status="' + fileData.isVisible + '">' +
                                    initialVisibilityIcon +
                                    '</span>'
                                ];

                                tableBody.append('<tr>' + row.map(cell => '<td>' + cell + '</td>').join('') + '</tr>');
                            });
                        } else {
                            tableBody.append('<tr><td colspan="4">No files found for the selected course and topic.</td></tr>');
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("Sorry, there was a problem!");
                        console.log("AJAX Error:", status, error);
                    }
                });
            } else {
                alert("Please select a course and topic before generating a summary.");
            }
        });
        $('#upload-button').click(function () {
    var selectedCourse = $('#course-select').val();
    var selectedTopic = $('#topic-select').val();
    var selectedTopicOption = $('#topic-select option:selected');
    var selectedSection = selectedTopicOption.data('section');

    // Construct the upload URL based on the selected course and section
    var uploadUrl = '<?php echo $CFG->wwwroot; ?>/course/modedit.php?add=resource&type=&return=0&sr=0&course=' +
        encodeURIComponent(selectedCourse) + '&section=' + encodeURIComponent(selectedSection);

    // Open the upload URL in a new tab
    window.open(uploadUrl, '_blank');
});
        $(document).on('click', '.toggle-icon', function () {
            var $toggleIcon = $(this);
            var activityId = $toggleIcon.data('file-id');
            var sesskey = '<?php echo sesskey(); ?>';
            var visible = $toggleIcon.data('status');

            var url = '<?php echo $CFG->wwwroot; ?>/course/mod.php';
            url += '?sesskey=' + encodeURIComponent(sesskey);

            if (visible == 0) {
                url += '&show=' + encodeURIComponent(activityId);
            } else {
                url += '&hide=' + encodeURIComponent(activityId);
            }

            $.ajax({
                url: url,
                type: "POST",
                success: function (responseData) {
                    visible = (visible == 1) ? 0 : 1;

                    if (visible == 0) {
                        // $toggleIcon.html('<i class="fas fa-eye-slash"></i>');
                        $toggleIcon.html('<img src="<?php echo $CFG->wwwroot;?>/pix/eye-slash.svg" alt="Show" title="Show" style="height:20px; width:20px;">');

                    } else {
                        // $toggleIcon.html('<i class="fas fa-eye"></i>');
                        $toggleIcon.html('<img src="<?php echo $CFG->wwwroot;?>/pix/view.png" alt="Hide" title="Hide" style="height:20px; width:20px;color: lightblue;">');

                    }
                    $toggleIcon.data('status', visible);
                },
                error: function (xhr, status, error) {
                    alert("Sorry, there was a problem!");
                    console.log("AJAX Error:", status, error);
                    console.log("XHR Object:", xhr);
                }
            });
        });
    });
</script>

<?php
echo $OUTPUT->footer();
?>
