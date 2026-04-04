<?php
require('../../config.php');
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
global $OUTPUT, $PAGE, $CFG;
if(user_has_role_assignment($USER->id,3)) {
    $context = context_user::instance($USER->id);
    $enrolledcourses = enrol_get_my_courses();
}else{
    redirect($CFG->wwwroot);
}
$PAGE->set_title('Course Performance');
echo $OUTPUT->header();
$PAGE->requires->js('/local/teacher/customchanges.js');
global $OUTPUT, $PAGE, $CFG;
echo '<div  class="pagecover-onload">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT </div><div><img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/loading.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';

?>
<head>
    <!-- <link rel="stylesheet" type="text/css"  href="<?php echo $CFG->wwwroot.'/' ?>local/teacher/c3.css"> -->
    <style>
        #teleconnect-subjects-dropdown {
        width: 200px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 14px;
        margin-right: 20px;
        margin-bottom:10px;
    }
        .latest-performance{
            display:none;
        }
        .graph,.latesttable{
            background-color: #ea6645;float:right;
        }
        .download,.download2{
            cursor:pointer;
            background-color: #574743;
            float: right;
            padding: 8px 6px;
            color: white;
        }
        .graph,.latestsearch,.download{
            display: none;
        }
        .status-icons{
            width: 100%;
            margin-bottom: 13px;
            text-align: center;
        }
        .status-icons span {
            color: #fff;
            cursor: pointer;
            margin: 2px;
            padding: 6px;
            font-size: 12px;
        }
        .course-results-header-bottom{
            1display: none;
        }
       
        .latest-performance-table td,.topic-info td,.studnets-info td {
            font-size: 12px;
        }
        #myTable,#courseTable,#studentTable,#teleattTable {
            border: 1px solid #e4e1da;
        }
        #myTable thead tr th,#courseTable thead tr th,#studentTable thead tr th,#teleattTable thead tr th {
            background-color: #ea6645;
            background-image: linear-gradient(to bottom, #ea6645, #ea6645);
            background-repeat: repeat-x;
            color: #fff !important;
            font-weight: bold !important;
            padding: 3px !important;
            text-align: center;
            border: 1px solid #e4e1da;
        }
        #myTable tbody tr td,#courseTable tbody tr td,#studentTable tbody tr td,#teleattTable tbody tr td {
            border: 1px solid #e4e1da;
            vertical-align: middle;
            text-align: center;
        }
        .tabs-left > .nav-tabs{
            border: none; padding: 5px;margin-right: 5px;
        }
        .tab-content {
            border-left: 1px solid #ddd;
        }
        .tabs-left > .nav-tabs a{
            border-color: #c3c3c3 !important;
            border-style: solid !important;
            border-width: 1px !important;
            background-color: #FFF;
        }
        .tabs-left > .nav-tabs > li > a {
            border-radius: 0;
            color: #01366a !important;
            padding-left: 30px;
        }
        .tabs-left > .nav-tabs .active > a, .tabs-left > .nav-tabs .active > a:hover, .tabs-left > .nav-tabs .active > a:focus {
            border-left: 4px solid rgb(234, 102, 69) !important;
            color: #ea6645 !important;
        }
        .tabs-left > .nav-tabs > li > a:hover{
            background-color: #FFF !important;
            color:#ea6645 !important;
        }
        .tabs-left > .nav-tabs > li > a > i,.tabs-left > .nav-tabs > li > span > i{
            padding-left: 5px;
        }
        .nav.nav-tabs.tabs-left li.active {
            border-left: 4px solid rgb(234, 102, 69) !important;
        }
        .nav.nav-tabs.tabs-left li.active a {
            color: #ea6645 !important;
        }
        select {
            border: 1px solid #ddd;
            background: transparent;
            padding: 5px;
            font-size: 12px;
            border: 1px solid #ddd;
            height: 34px;
        }
        .nav > li.tab-parents {
            display: block;
            padding: 10px 8px;
            position: relative;
            background-color: #fff;
            border-color: #c3c3c3 !important;
            border-style: solid !important;
            border-width: 1px !important;
        }
        #navbar{
            margin-left: 5%;
            margin-top: 1%;
        }
        .col-md-4 > input {
            width: 100% !important;min-height: 35px;
            background-color: #fff;
            border: 1px solid #c5c5c5;
            margin-left: 0;
        }
        input[type="button"]:hover{
            background-position: 0
        }
        .table-notations {
            padding-top: 15px;
            display:none;
        }
        .table-notations > table {
            border: 1px solid #e4e1da;
            font-size: 12px;
            width: 100%;
        }
        .table-notations td {
            border: 1px solid #e4e1da;
            text-align: center;
            width: 75%;
            color:#6e6e6e;
            padding: 4px;
        }
        .tab-pane {
            min-height: 520px;
        }
        .tabs-left > .nav-tabs > li > a {
            margin-right: 0px;
        }
        .course-results-header.col-md-12 {
            border-bottom: 2px solid #ddd;
            margin-bottom: 10px;
        }
        .student-results-header.col-md-12 {
            border-bottom: 2px solid #ddd;
            margin-bottom: 10px;
        }
   
        .container .container-demo {
            margin-top: 50px;
            min-height: 520px;
        }
        .search-button{
            -moz-border-radius: 0px;
            -webkit-border-radius: 0px;
            border-radius: 0px;
            -moz-transition: 0.3s;
            -o-transition: 0.3s;
            -webkit-transition: 0.3s;
            transition: 0.3s;
            background-color: #ea6645;
            border: 2px solid transparent;
            color: #fff;
            font-weight: bold;
            min-height: 40px;
            outline: none !important;
            padding: 8px 100px;
            cursor: pointer;     
        }
        .search-button:hover{
            -moz-border-radius: 0px;
            -webkit-border-radius: 0px;
            border-radius: 0px;
            -moz-transition: 0.3s;
            -o-transition: 0.3s;
            -webkit-transition: 0.3s;
            transition: 0.3s;
            background-color: #ea6645;
            border: 2px solid transparent;
            color: #fff;
            font-weight: bold;
            min-height: 40px;
            outline: none !important;
            padding: 8px 100px;
            cursor: pointer;
        }
        .pagecover-onload{
            display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 85%; top: 60px;margin-left: -10px;
        }
        .tabbable.tabs-left {
            border: 1px solid #ddd;
        }
        .tab-pane {
            border-top: 1px solid #ddd;

            padding-top: 5px;
        }
        #page-content{
    width: 1200px;
    height:600px;
    margin-right:0;
    margin-left:-180px;
    margin-top:-60px;
    padding-left:0;
    padding-right:0;
} 
.pagecover-onload{
    display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 90%; top: 60px;margin-left: -10px;
    }
    </style>
    <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-3.js"></script>
    <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
    <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.table2excel.js"></script> 
    <script>
        var $j= jQuery.noConflict();
        var baseUrl='<?php echo $CFG->wwwroot.'/' ?>';
        $j(document).ready(function(){
            $j("#teleattTable").tablesorter();
            var d = new Date();
            var dat = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();
   function getCourseOverallReport(subject){
        $j(".pagecover-onload").show();
        $j.ajax({
            type: "POST",
            dataType: 'html',
            data: {
                "mid": 28,
                "selected_course": subject
            },
            url: baseUrl + "local/teacher/teacherlib.php",
            success: function (data) {
                $j(".tele-att-info").html(data);
            },
            complete: function (xhr, status) {
                $j("#teleattTable").trigger("update");
                // set sorting column and direction, this will sort on the first and third column
                $j("#teleattTable").trigger([]);
                // $j("#studentTable").tablesorter({});
                var $rows = $j('.course-list-table tbody tr');
                $j('.search').keyup(function() {
                    var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();
                    $rows.show().filter(function() {
                        var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
                        return !~text.indexOf(val);
                    }).hide();
                });
                $j(".pagecover-onload").hide();
            }
        });
    }
    $j("#teleconnect-search").on("click",function(){
            if(parseInt($j("#teleconnect-subjects-dropdown").val())){
                subject=$j("#teleconnect-subjects-dropdown").val();
                subjectname=$j("#teleconnect-subjects-dropdown option:selected").text();
                    getCourseOverallReport(subject);
                }else{
                alert("please select one subject");
            }
    });
$j("#txls").click(function(){
                $j("#teleattTable").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Table2Excel",
                    filename: subjectname+"_"+new Date().toLocaleDateString('en-GB').replace(/\//g, '-')+"_TodaysTitans_Report" //do not include extension
                });
            });
    var $rows = $j('.course-list-table tbody tr');
    $j('.search').keyup(function() {
        var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();
        $rows.show().filter(function() {
            var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
    });
                    });//end of getResults
    </script>
</head>
<body>
<?php
if (!(user_has_role_assignment($USER->id,3) ) ) {
            redirect($CFG->wwwroot);
}
$curdate=date("d-m-y",time());
?>
                <div class="course-results-div">
                    <div class="course-results-header col-md-12">
                        <br/>
                        <h3>Todays Titans</h3>
                    <div class="course-filters course-results-header-top col-md-12" style='display:flex;align-items:center;margin-top:20px;'>
    <div class="col-md-3" style='margin-bottom:5px'>
        <div class="dropdown-lable">Subject</div>
        <select name="selected_course" id="teleconnect-subjects-dropdown">
            <option value="0">Select a course</option>
            <?php
            $teacher_courses = enrol_get_my_courses();
            foreach ($teacher_courses as $course) {
                echo "<option value=\"$course->id\">$course->fullname</option>";
            }
            // echo $OUTPUT->footer();
            ?>
        </select>
    </div>
    <!-- <div class="col-md-3" style='margin-bottom:5px'>
        <div class="dropdown-lable" style="min-height: 38px;"></div>
    </div> -->
    <div class="col-md-3" style='margin-bottom:05px'>
        <a id="teleconnect-search" class="search-button">SEARCH</a>
    </div>
</div>
                    </div><!-- course-results-header end -->
                    <div class="col-md-12 ">
                        <div class="col-md-4" style='margin-bottom:5px '>
</div>
                        <div class="col-md-4" style='margin-bottom:5px' ></div>
                        <div class="col-md-4" style='margin-bottom:15px;margin-left:730px;margin-top:15px;'>
                            <div class="dropdown-lable"></div>
                            <input  placeholder="search" style="width: 85% !important;" class="search" type="text" value="" />
                        <span class="download2" id="txls">XLS
                        </span>
                        </div>
                    </div>
                    <div class="course-results col-md-12">
                        <div style="padding-bottom:30px;" class="table-responsive" id="container">
                        <center><i><b style='color:green;'>Note: The ranking is determined by highest total score, followed by lowest total time spent, and finally by lowest submissions in case of further ties.</i></b></center><br/>
                            <table class="CSSTableGenerator table table-hover course-list-table tablesorter" id="teleattTable">
                                <thead>
                                <tr>
                                    <!-- <th class="header" style="text-align:center">Course</th>-->
                                    <th class="header">Rank</th>
                                    <th class="header">Ht No</th>
                                    <th class="header">Name</th>
                                    <th class="header">Section</th>
                                    
                                    <th class="header">Submissions</th>
                                    <th class="header">Time Spent (minutes)</th>
                                    <th class="header">Attempted</th>
                                    <th class="header" style='width:135px;'>Score</th>
                                    <th class="header">Percentile</th>

                                </tr>
                                </thead>
                                <tbody class="tele-att-info">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- end of course results-->
</body>
</html>
<?php
echo $OUTPUT->footer();
?>
