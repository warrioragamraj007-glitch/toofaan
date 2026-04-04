<?php
require('../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Dashboard');
echo $OUTPUT->header();
$PAGE->requires->js('/local/teacher/html-table-search.js', true);
if (!user_has_role_assignment($USER->id, 3)) {
    redirect($CFG->wwwroot);
}

$PAGE->requires->js('/local/teacher/customchanges.js');

$teacher_courses = enrol_get_my_courses();
echo '<div  class="pagecover-onload">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT </div><div><img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/loading.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';

?>
<style>
    .dashboard-table-container {
        position: relative;
    }

    .select-row {
        margin-bottom: 20px; /* Adjust margin as needed */
        display: flex;
        align-items: center;
    }

    #course-form .col {
        margin-top: 0px;
        padding-top: 10px;
        margin-bottom: 10px;
        margin-left: -15px;
    }

    #course-select {
        width: 200px; /* Adjust width as needed */
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
        margin-right: 20px; /* Adjust margin as needed */
    }

    .dataTables_filter {
        margin-right: 20px; /* Adjust margin as needed */
    }

    .dashboard-table {
        width: 100%;
    }

    .dashboard-table th,
    .dashboard-table td {
        padding: 12px;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }

    .dashboard-table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
        background-color: #ea6645;
        color: #fff;
    }

    .dashboard-table tbody tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    .dashboard-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .dashboard-button {
        display: none;
        margin-top: 20px;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        float: right;
    }

    #page-content {
        width: 1200px;
        height: 600px;
        margin-right: 20px;
        margin-left: -180px;
        margin-top: 0px;
        padding-left: 0px;
        padding-right: 10px;
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
    </div>

    <table id="overview-grade" class="dashboard-table">
        <thead>
            <tr>
                <th scope="col">Select</th>
                <th scope="col">Topic</th>
                <th scope="col">Activities</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <p class="result-text" style='margin-top:10px'></p>
    <button id="go-to-test-center" class="dashboard-button" style='margin-top:10px;margin-left: 960px;'>Go to Test Center</button>

</div>

<link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/local/teacher/datatable.css">
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-3.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.datatable.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/local/teacher/tablesorter.css">
<script>
    var baseUrl = '<?php echo $CFG->wwwroot . '/' ?>';
    $(document).ready(function () {
        var dataTable = $('#overview-grade').DataTable({
            "paging": false,
            "ordering": true,
            "searching": true,
            "info": false,
            "lengthChange": false,
            "columnDefs": [
                { "orderable": false, "targets": [0, 2] }
            ]
        });
        // $('#overview-grade').tablesorter();
        var goToTestCenterButton = $('#go-to-test-center');
        goToTestCenterButton.hide();

        $('#course-select').change(function () {
            var selected_course = $('#course-select').val();
            $(".pagecover-onload").css("display", "block");
            if (selected_course) {
                $.ajax({
                    url: baseUrl + "local/teacher/teacherlib.php",
                    data: { "selected_course": selected_course, 'mid': 3 },
                    type: "POST",
                    dataType: "json",
                    success: function (data) {

                        dataTable.clear().draw();

                        var nonNullTopics = data.filter(topic => topic.section !== null).length;
                        var resultText = '(' + nonNullTopics + ') results found';
                        $('.result-text').text(resultText);
                        $(".pagecover-onload").hide();
                        if (nonNullTopics > 0) {
                            $.each(data, function (index, sectionData) {
                                if (sectionData.section !== null) {
                                    var activityHtml = '';
                                    $.each(sectionData.activities, function (i, activity) {
                                        var activityImage = '';
                                        if (activity.type === 'quiz') {
                                            activityImage = '<img src="<?php echo $CFG->wwwroot;?>/pix/Quiz.jpeg" alt="Quiz" title="Quiz">';
                                        } else if (activity.type === 'vpl') {
                                            activityImage =  '<img src="<?php echo $CFG->wwwroot;?>/pix/Labs.jpeg" alt="VPL" title="Virtual Programming Lab">';
                                        }
                                        else if (activity.type === 'feedback') {
                                            activityImage =  '<img src="<?php echo $CFG->wwwroot;?>/pix/feedback.svg" alt="feedback" title="feedback" style="height:16px; width:16px;">';
                                        }
                                        else if (activity.type === 'url') {

                                            activityImage = '<img src="<?php echo $CFG->wwwroot;?>/pix/url.svg" alt="url" title="url" style="height:16px; width:16px;">';
                                        }
                                        else if (activity.type === 'file') {
                                            activityImage = '<img src="<?php echo $CFG->wwwroot;?>/pix/file.svg" alt="file" title="file" style="height:16px; width:16px;">';
                                        }
                                        else if (activity.type === 'assign') {
                                            activityImage = '<img src="<?php echo $CFG->wwwroot;?>/pix/assignment.svg" alt="assignment" title="assignment" style="height:16px; width:16px;">';
                                        }
                                        else if (activity.type === 'teleconnect') {
                                            activityImage = '<img src="<?php echo $CFG->wwwroot;?>/mod/teleconnect/pix/icon.gif" alt="teleconnect" title="teleconnect" style="height:16px; width:16px;">';
                                        }
                                        activityHtml += activityImage + '&nbsp;' + '&nbsp;' + activity.name + '<br>';
                                    });

                                    var row = [
                                        '<input type="radio" sectionid="' + sectionData.section_id + '" name="select-radio" class="rdo">',
                                        sectionData.section,
                                        activityHtml
                                    ];
                                    dataTable.row.add(row);
                                }
                            });

                            dataTable.draw();
                            goToTestCenterButton.show();
                        } else {
                            goToTestCenterButton.hide();
                        }
                    },
                    error: function (xhr, status) {
                        alert("Sorry, there was a problem!");
                    }
                });
            } else {
                dataTable.clear().draw();
                $('.result-text').text('');
                goToTestCenterButton.hide();
            }
        });

        goToTestCenterButton.click(function () {
            var selectedRow = $('input[name=select-radio]:checked').closest('tr');
            if (selectedRow.length === 0) {
        alert('Please select a topic');
        return;
    }
            var selectedTopic = selectedRow.find('td:nth-child(2)').text();
            var selectedTopicId = $('input[name=select-radio]:checked').attr('sectionid');
            var selectedcid = $('#course-select').val();
            window.open('<?php echo $CFG->wwwroot;?>/local/teacher/testcenter/index.php?cid=' + encodeURIComponent(selectedcid) + '&secid=' + encodeURIComponent(selectedTopicId), '_blank');
        });
    });
</script>

<?php
echo $OUTPUT->footer();
?>

