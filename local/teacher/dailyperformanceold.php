<?php
require('../../config.php');
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
global $OUTPUT, $PAGE, $CFG;
if(user_has_role_assignment($USER->id,3)) {
    $context = context_user::instance($USER->id);
    $teacher_courses = enrol_get_my_courses();
}else{
    redirect($CFG->wwwroot);
}
$PAGE->set_title('Daily Performance');
echo $OUTPUT->header();
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
<style>
    .page-context-header.container{
    display:none;
}
    .progress-bar{
    background-image:none !important;
    }
    table{
    margin-top:20px !important;
    }
    #cbody tr[visible='false'],
    .no-result{
    display:none;
}
    #cbody tr[visible='true']{
        display:table-row;
    }
    .counter{
    padding:8px;
        color:#ccc;
    }
    #selectlist2 {
        margin-left: 5%;
    }
    table.flexible, .generaltable {
    font-size: 14px;
    }
    .pagecover-onload{
    display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 90%; top: 60px;margin-left: -10px;
    }
</style>
<?php
$curdate=date('d-m-y');
?>
<style>
    #subjects-dropdown,
    #sections-dropdown {
        width: 200px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
        margin-right: 20px;
    }
    #myTable thead th {
        background-color: #ea6645; /* Red background color */
        color: #fff; /* White text color */
    }
    .pika-single {
        z-index: 9999;
        display: block;
        position: relative;
        color: #333;
        background: #fff;
        border: 1px solid #ccc;
        border-bottom-color: #bbb;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    #datepicker {
        padding: 0px 0px 0px 20px;
        font-size: 15px;
        background: rgb(255, 255, 255) url(<?php  echo $CFG->wwwroot.'/pix/i/calendar.png';?>) no-repeat scroll left center;
    }
    .switch-button,.search-button{
        -moz-border-radius: 0px;
        -webkit-border-radius: 0px;
        border-radius: 0px;
        -moz-transition: 0.3s;
        -o-transition: 0.3s;
        -webkit-transition: 0.3s;
        transition: 0.3s;
        background-color: #ea6645;
        border: 1px solid #ddd;
        color: #fff;
        font-weight: bold;
        min-height: 30px;
        outline: none !important;
        padding: 6px 65px;
        cursor: pointer;
        float: left;
    }
    .switch-button:hover,.search-button:hover{
        -moz-border-radius: 0px;
        -webkit-border-radius: 0px;
        border-radius: 0px;
        -moz-transition: 0.3s;
        -o-transition: 0.3s;
        -webkit-transition: 0.3s;
        transition: 0.3s;
        background-color: #ea6645;
        border: 1px solid transparent;
        color: #fff;
        font-weight: bold;
        min-height: 30px;
        outline: none !important;
        padding: 6px 65px;
        cursor: pointer;
    }
    .download,.download2{
        cursor:pointer;
        background-color: #574743;
        float: right;
        padding: 8px 6px;
        color: white;
    }
    .col-md-12.status-msg {
        color: #fff;
        font-weight: bold;
        margin: 0 2%;
        padding: 0;
        width: 93%;
    }
    .selection,.selection1{
        background-color: #a1a1a1;
        font-weight: bold;
        padding-bottom: 4px;
        padding-top: 4px;
        color:#fff;
    }
    .col-md-12.status-lable {
        padding-left: 0;
        padding-right: 0;
        width: 100%;
    }
    table {
        margin-top: 0 !important;
    }
    .col-md-6.action-box {
        border: 2px solid #ddd;
        padding: 0 0 5px;
    }
    .col-md-6.action-button {
        margin-bottom: 8px;
    }
    .stdname{
        text-align: left !important;
    }
    .selection {
        border: 2px solid #ddd;
    }
    .daily-wise-hits {
        margin-top: 20px;
    }
    #page-content{
    width: 1200px;
    height:800px;
    margin-right:0;
    margin-left:-180px;
    margin-top:-60px;
    padding-left:0;
    padding-right:0;
} 
.switch-button {}
</style>
<div class="container" >
        <h1 class="text-center" style="font-size: 1.640625rem;padding-top: 4px;">Daily Performance Report </h1>
<!-- <center><h3>Daily Performance Report</h3></center> -->
</div>
<div id="container" style='margin-top:50px'>
<!-- <center><h3>Daily Performance Report</h3></center> -->

    <div class="abs-summary row">
        <div class="col-md-3">
            <select id="subjects-dropdown">
                <option value="0">Select a course</option>
                <?php
                $teacher_courses = enrol_get_my_courses();
                foreach ($teacher_courses as $course) {
                    echo "<option value=\"$course->id\">$course->fullname</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <select id="sections-dropdown">
                <option value="0">Select</option>
            </select>
        </div>
        <div class="col-md-4">
            <!-- <center><h3>Daily Performance Report</h3></center> -->
            <a id="getresults" class="switch-button" style="margin-left: 25%;">Get Results</a>

        </div>
        <div class="col-md-3">
            <!-- <a id="getresults" class="switch-button">Get Results</a> -->
            <a target="_blank" href="http://teleuniv.in/sams/upload-cpc-performance.php" class="switch-button" style="background-color: #3c8dbc;" id="yui_3_17_2_2_1716460692695_24">Upload To Sanjaya</a>
        </div>
        
    </div>
</div>
                <div class="daily-wise-hits" >
                    <div class="col-md-12"><span class="download2" id="sxls">XLS</span>
                        <table class="CSSTableGenerator table table-hover course-list-table tablesorter" id="myTable">
                            <thead>
                            <tr>
                                <!-- <th class="header" style="text-align:center">Course</th>-->
                                <th class="header">SNo</th>
                                <th class="header">RollNo</th>
                                <th class="header">Name</th>
                                <th title="total labs" class="header">Tlabs</th>
                                <th title="total quizs"class="header">Tquizs</th>
                                <th title="attempted labs" class="header">Alabs</th>
                                <th title="attempted quizs" class="header">Aquizs</th>
                                <th class="header">Lab(Total)</th>
                                <th class="header">Quiz(Total)</th>
                                <th class="header">Section</th>
                            </tr>
                            </thead>
                            <tbody class="grade-info">
                            <tr>
                                <td class="header">--</td>
                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
             <?php
            echo "</div>";
            ?> 
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-2.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.datatable.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.table2excel.js"></script> 
            <script>
                // Render Sections Based On Course Selection
                var $j = jQuery.noConflict();
                var baseUrl='<?php echo $CFG->wwwroot.'/' ?>';
                var curdate='<?php echo $curdate ?>';
                $j("#sxls").click(function(){
                    var rowCount = parseInt($('#myTable tr').length);
                    var subject=($('#subjects-dropdown option[value="'+$("#subjects-dropdown").val()+'"]').text());
                    if(parseInt($("#subjects-dropdown").val())){
                        if(rowCount>2){
                            $j("#myTable").table2excel({
                                // exclude CSS class
                                exclude: ".noExl",
                                name: "Table2Excel",
                                filename: subject+'-PERF-'+"-REPORT-" +curdate//do not include extension
                            });
                        }else{
                            alert("No records found");
                        }
                    }else{
                        alert("please select course");
                    }
                });
                $j("#subjects-dropdown").on("change", function () {
            if(parseInt($("#subjects-dropdown").val()))
                    getSections($("#subjects-dropdown").val());
                });
                function getSections(cid){
                    $j(".pagecover-onload").show();
                    $j.ajax({
                        type: "POST",
                        dataType: 'html',
                        data: {
                            "mid": 10,
                            "selected_course": cid,
                        },
                        url: baseUrl + "local/teacher/teacherlib.php",
                        success: function (data) {
                            $j("#sections-dropdown").html(data);
                            var section=($('#sections-dropdown option[value="'+$("#sections-dropdown").val()+'"]').text());
                            $j(".pagecover-onload").hide();
                        }
                    });
                }//end of getResults
                $j("#getresults").on("click",function(event){
if(parseInt($("#subjects-dropdown").val())){
    var section = $j("#sections-dropdown").val();
    $j(".pagecover-onload").css("display", "block");
    $j.ajax({
        type: "POST",
        dataType: 'html',
        data: {
            "mid": 21,
            "selected_course": $("#subjects-dropdown").val(),
            "selected_section": section,
        },
        url: baseUrl + "local/teacher/teacherlib.php",
        success: function (data) {
            $j(".grade-info").html(data);
        },
        complete: function (xhr, status) {
            $j(".pagecover-onload").hide();
            $j("#myTable").trigger("update");
            // set sorting column and direction, this will sort on the first and third column
            $j("#myTable").trigger([]);
            $j("#myTable").tablesorter({});
        }
    });
}else{
    alert("please select course");
}
});//end of getresults
            </script>
<?php
echo $OUTPUT->footer();
?>
