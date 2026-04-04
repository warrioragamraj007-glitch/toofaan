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
$PAGE->set_title('Today Reports');
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
<head>
    <style>
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
            height:55px;
        }
        .table-responsive {
            min-height: 0.01%;
            overflow-x: hidden;
        }
        .latest-performance-table td,.grade-info td,.studnets-info td {
            font-size: 12px;
        }
        #myTable,#courseTable,#studentTable {
            border: 1px solid #e4e1da;
        }
        #myTable thead tr th,#courseTable thead tr th,#studentTable thead tr th {
            background-color: #ea6645;
            background-image: linear-gradient(to bottom, #ea6645, #ea6645);
            background-repeat: repeat-x;
            color: #fff !important;
            font-weight: bold !important;
            padding: 3px !important;
            text-align: center;
            border: 1px solid #e4e1da;
        }
        #myTable tbody tr td,#courseTable tbody tr td,#studentTable tbody tr td {
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
        .table-responsive {
            height: 450px;
            overflow-y: auto;
        }
        .container .container-demo {
            margin-top: 50px;
            min-height: 520px; 
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
        .pagecover-onload{
            position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 85%; top: 60px;margin-left: -10px;
        }
        .table {
            margin-bottom: 10px;
        }
        #detailed-info,#cxls,.filter-report,.filter-report1{display: none;}
        .filter-report{padding: 5px;float: right;}
        .fa{
            cursor: pointer;
        }
        .stdname{
            text-align: left !important;
        }
        .xls-reports {
            float: left;
            width: 50%;
        }
        .export-label{
            font-weight: bold;
        }
        .xls-reports.export-actions {
            margin-top: 2px;
        }
        .records-count {
            float: left !important;
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
div#container {
    margin-top: -45px;
}
.pagecover-onload{
    display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 90%; top: 60px;margin-left: -10px;
    }
</style>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-2.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/local/teacher/tablesorter.css">
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.table2excel.js"></script> 
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/xlsx.full.min.js"></script>

<script>
    console.log('XLSX:', XLSX);
console.log('XLSX.utils:', XLSX?.utils);
console.log('XLSX.utils.table_to_sheet:', XLSX?.utils?.table_to_sheet);
        var $j= jQuery.noConflict();
        var baseUrl='<?php echo $CFG->wwwroot.'/' ?>';
        $j(document).ready(function(){
            var d = new Date();
            var dat = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();
            $j("#cxls").click(function(){
                var topic=($j('#topics option[value="'+$j('#topics').val()+'"]').text());
                var section=($j('#sections-dropdown option[value="'+$j("#sections-dropdown").val()+'"]').text());
                // $j("#studentTable").table2excel({
                //     exclude: ".noExl",
                //     name: "Table2Excel",
                //     filename: 'FS-Perf-'+topic+'-'+section+"-Detailed-Report" //do not include extension
                // });

                var table = document.getElementById("studentTable");
    
    // Convert the table to a worksheet
    var ws = XLSX.utils.table_to_sheet(table);
    
    // Create a new workbook
    var wb = XLSX.utils.book_new();
    
    // Append the worksheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    
    // Write the workbook to a file
    var filename = 'FS-Perf-'+topic+'-'+section+"-Detailed-Report.xlsx";
    XLSX.writeFile(wb, filename);
            });
            $j("#sxls").click(function(){
                var topic=($j('#topics option[value="'+$j('#topics').val()+'"]').text());
                var section=($j('#sections-dropdown option[value="'+$j("#sections-dropdown").val()+'"]').text());
                $j("#myTable").table2excel({
                    exclude: ".noExl",
                    name: "Table2Excel",
                    filename: 'FS-Perf-'+topic+'-'+section+"-Summary-Report" //do not include extension
                });
            });
            //getCourses(1);
                $j("#courses-status a").click();
                $j("#current-category").val($j(this).text());
            $j("#subjects-dropdown").on("change", function () {
                getTopics($j(this).val());
                //alert("hi");
                var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());
            });
            $j("#topics").on("change", function () {
                var topic=($j('#topics option[value="'+$j(this).val()+'"]').text());
                var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());
                getSections($j("#subjects-dropdown").val());
            });
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
                    var section=($j('#sections-dropdown option[value="'+$j("#sections-dropdown").val()+'"]').text());
                    $j(".pagecover-onload").hide();
                }
            });
        }
        function getTopics(cid){
            $j(".pagecover-onload").show();
            $j.ajax({
                type: "POST",
                dataType: 'html',
                data: {
                    "mid": 4,
                    "selected_course": cid
                },
                url: baseUrl + "local/teacher/teacherlib.php",
                success: function (data) {
                    $j("#topics").html(data);
                    $j(".pagecover-onload").hide();
                }
            });
        }
    </script>
</head>
<body>
<?php
if (!(user_has_role_assignment($USER->id,3) ) ) {

            redirect($CFG->wwwroot);
}
?>
<input type="hidden" id="current-category" value="" />
<div class="container container-demo" style='width:auto' >
        <br/>
       <center> <h3>Daily Student Performance Report</h3></center>
            <div class="tab-pane " id="b">
                <div class="course-results-div">
                    <div class="course-results-header col-md-12">
                        <div class="course-filters course-results-header-top col-md-12" style='display:flex'>
                        <div class="col-md-3" style='margin-bottom:5px'>
    <div class="dropdown-lable">Subject</div>
    <select  name="selected_course", id="subjects-dropdown">
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
                            <div class="col-md-3" style='margin-bottom:5px'>
                                <div class="dropdown-lable">Topic</div>
                                <select class="topic" id="topics">
                                    <option value="0">Select a topic</option>
                                </select>
                            </div>
                            <div class="col-md-3" style='margin-bottom:5px'>
                                <div class="dropdown-lable">Section</div>
                                <select id="sections-dropdown">
                                    <option value="0">Select</option>
                                </select>
                            </div>
                            <div class="col-md-3" style='margin-bottom:15px'>
                                <div class="dropdown-lable" style="height: 30px"></div>
                                <a id="courses-search" class="search-button">Summary</a>                                
                            </div>
                        </div>
                        </div>
                    <div class="col-md-12 course-results-header-bottom" >
                        <div class="col-md-4" style='margin-bottom:5px' >
                        </div>
                        <div class="col-md-4" style='margin-bottom:5px' >
                        </div>
                        <div class="col-md-4" style='margin-bottom:5px'>
                            <div class="dropdown-lable"></div>
                        </div>
                    </div>
                    <div class="course-results col-md-12">
                        <div style="padding-bottom:40px;" class="" id="container">
                            <table class="CSSTableGenerator table table-hover course-list-table tablesorter" id="myTable">
                                <thead>
                                <tr>
                                    <th class="header">Topic</th>
                                    <th class="header">Section</th>
                                    <th class="header">5 of 5</th>
                                    <th class="header">4 of 5</th>
                                    <th class="header">3 of 5</th>
                                    <th class="header">2 of 5</th>
                                    <th class="header">1 of 5</th>
                                    <th class="header">0 of 5</th>
                                </tr>
                                </thead>
                                <tbody class="grade-info">
                                <tr>
                                    <td class="header">Topic</td>
                                    <td class="header">--</td>
                                    <td class="header">--</td>
                                    <td class="header">--</td>
                                    <td class="header">--</td>
                                    <td class="header">--</td>
                                    <td class="header">--</td>
                                </tr>
                                </tbody>
                            </table>
                            <a id="results-switch" class="switch-button filter-report1">More Details</a>                           
                        </div>
                    </div>                     
                    <div class="col-md-12 course-results-header-bottom" style="border-bottom: 2px solid #ddd;
    margin-bottom: 10px;">
                        <div class="col-md-3" style='margin-bottom:5px' >
                        </div>
                        <div class="col-md-3" style='margin-bottom:5px' >
                        </div>
                        <div class="col-md-3" style='margin-bottom:5px' >
                            <p class="records-count filter-report"></p>
                        </div>
                        <div class="col-md-4 filter-report" style='margin-bottom:5px;border: 1px solid #ddd;'>
                            <div class="xls-reports export-label"><span class="filter-report">EXPORT EXCEL</span></div>
                            <div class="xls-reports export-actions">
                                <a id="top-students" class="filter-report" title="TOP-25">
                                    <i class="fa fa-arrow-up" aria-hidden="true"> 25</i>
                                    </a>
                                <a id="both-absentees" class="filter-report"  title="BOTTOM-25">
                                    <i class="fa fa-arrow-down" aria-hidden="true"> 25</i>
                                    </a>
                                <a class="filter-report" id="cxls" title="All">
                                    <i class="fa fa-file-excel-o" aria-hidden="true">ALL</i>
                                </a>
                            </div>
                        </div>
                    </div> 
                    <div class="course-results col-md-12" id="detailed-info">
                        <div style="padding-bottom:30px;" class="table-responsive" id="container">
                            <table class="CSSTableGenerator table table-hover course-list-table tablesorter" id="studentTable">
                                <thead>
                                <tr>
                                    <th class="header">Roll No</th>
                                    <th class="header">Name</th>
                                    <th class="header">Section</th>
                                    <th class="header">Graded Count</th>
                                    <th class="header">Lab1</th>
                                    <th class="header">Lab2</th>
                                    <th class="header">Lab3</th>
                                    <th class="header">...</th>
                                </tr>
                                </thead>
                                <tbody class="grade-info">
                                </tbody>
                            </table>
                            <table id="resTable" style="display: none">
                            </table>
                            <table id="topStudents" style="display: none">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
<input type="hidden" id="moredetailsFlag" value="0" />
<input type="hidden" id="showFlag" value="0" />
        </div>
</div>
        <section id="footer-bottom">
            <div class="container" style='width:1000px'>
                <div class="footer-inner">
                </div>
            </div>
        </section>
        <?php
        ?>
</body>
</html>
<script>
$j("#talink a").on("click",function(){
location.href=$j(this).attr('href');
});
    function getStudentsByCourseBySection(cid,topicid,section){
        if(parseInt($j("#moredetailsFlag").val())){
            if(parseInt($j("#showFlag").val())) {
                $j("#detailed-info").slideUp();
                $j(".filter-report").slideUp();
                $j("#results-switch").text("More Details");
                $j("#showFlag").val(0);
            }else{
                $j("#detailed-info").slideDown();
                $j(".filter-report").slideDown();
                $j("#results-switch").text("Less Details");
                $j("#showFlag").val(1);
            }
        }else{
            $j(".pagecover-onload").show();
            $j.ajax({
                type: "POST",
                dataType: 'html',
                data: {
                    "mid": 11,
                    "selected_course": cid,
                    "selected_topic": topicid,
                    "selected_section": section,
                },
                url: baseUrl + "local/teacher/teacherlib.php",
                success: function (data) {
                    $j("#studentTable").html(data);
                    $j("#resTable").html(data);
                    $j("#topStudents").html(data);
                },
                complete: function (xhr, status) {
                    $j("#studentTable").trigger("update");
                    $j("#studentTable").trigger([]);
                    $j("#studentTable").tablesorter({});
                    $j("#detailed-info").slideDown();
                    $j(".filter-report").slideDown();
                    $j("#moredetailsFlag").val(1);
                    $j("#showFlag").val(1);
                    $j("#results-switch").text("Less Details");
                    var rowCount1 = parseInt($j('#resTable tr').length);
                    $j(".records-count").text("Showing "+(rowCount1-1)+" Records");
                }
            });
        }
    }
function getReportxls(cid,topicid,section){
    $j.ajax({
        type: "POST",
        dataType: 'html',
        data: {
            "trmid": 13,
            "trcid": cid,
            "selected_topic": topicid,
            "selected_section": section,
        },
        url: baseUrl + "local/teacher/teacherlib.php",
        success: function (data) {
            $j("#resTable").html(data);
            $j("#topStudents").html(data);
        },
        complete: function (xhr, status) {
            var rowCount1 = parseInt($j('#resTable tr').length);
            var rowCount2 = parseInt($j('#topStudents tr').length);
            var rowCount3 = parseInt($j('#studentTable tr').length);
            if(rowCount1&&rowCount2&&rowCount3){
                $j(".pagecover-onload").hide();
            }
        }
    });
}
    function getStudentsByCourseBySectionSummary(cid,topicid,section){
        $j(".pagecover-onload").show();
        $j.ajax({
            type: "POST",
            dataType: 'html',
            data: {
                "mid": 12,
                "selected_course": cid,
                "selected_topic":topicid,
                "selected_section":section,
                "ttopicname":$j('#topics option[value="' + $j("#topics").val() + '"]').text(),
            },
            url: baseUrl + "local/teacher/teacherlib.php",
            success: function (data) {
                $j("#myTable").html(data);
            },
            complete: function (xhr, status) {
                $j(".pagecover-onload").hide();
                $j("#myTable").trigger("update");
                $j("#myTable").trigger([]);
                $j("#myTable").tablesorter({});
                $j("#detailed-info").hide();
                $j(".filter-report").hide();
                $j(".filter-report1").show();
                $j("#moredetailsFlag").val(0);
                $j("#showFlag").val(0);
                $j("#results-switch").text("More Details");
            }
        });
    }
var currdate='<?php echo date("d-m-y",time()) ?>';
$j("#both-absentees").on("click",function(){
    var j = 0;
    var ncount=0;
    var rt = document.getElementById('resTable');
    var removeElement=[];
    var parentnums='';
    $j("#resTable tr").each(function() {
        var val1 = $j(rt.rows[j].cells[4]).text();
        var val2 = $j(rt.rows[j].cells[6]).text();
        if((val1=='ABSENT')&&(val2==0)){
            ncount++;
            if(ncount>25){
                removeElement.push(rt.rows[j]);
            }
        }else{
            removeElement.push(rt.rows[j]);
        }
        j++;
    });
    for(var j=1;j<removeElement.length;j++){
        removeElement[j].remove();
    }
	var rowCount = parseInt(ncount);
        if(rowCount>2) {
	    $j("#resTable").table2excel({
		exclude: ".noExl",
		name: "Table2Excel",
		filename: currdate+"-FS-Bottom-25-Report",
		fileext: ".xls",
	    });

        }else{
            alert("No bottom 25 records, Because no online session attendance today");
        }
});
$j("#top-students").on("click",function(){
    var j = 0;
    var rt = document.getElementById('topStudents');
    var removeElement=[];
    var parentnums='';
    var ncount=0;
    $j("#topStudents tr").each(function() {
        var val1 = $j(rt.rows[j].cells[4]).text();
        var val2 = $j(rt.rows[j].cells[5]).text();
        var val4 = $j(rt.rows[j].cells[6]).text();
        if((val1=='PRESENT')&&(val2==val4)){
            ncount++;
            if(ncount>25){
                removeElement.push(rt.rows[j]);
            }
        }else{
            removeElement.push(rt.rows[j]);
        }
        j++;
    });
    for(var j=1;j<removeElement.length;j++){
        removeElement[j].remove();
    }
    var rowCount = parseInt(ncount);
    if(rowCount>2) {
	    $j("#topStudents").table2excel({
		exclude: ".noExl",
		name: "Table2Excel",
		filename: currdate+"-FS-Top-25-Report",
		fileext: ".xls",
	    });
    }else{
        alert("No Top 25 records, Because no online session attendance today");
    }
});
$j("#courses-search").on("click",function(){
            if(parseInt($j("#subjects-dropdown").val())){
                if(parseInt($j("#topics").val())) {
                    var section = $j("#sections-dropdown").val();
                    var topic = $j("#topics").val();
                    var subject = $j("#subjects-dropdown").val();
                    getStudentsByCourseBySectionSummary(subject, topic, section);
                }else{
                    alert("please select one topic");
                }
            }else{
                alert("please select one subject");
            }
    });
    $j("#results-switch").on("click",function(){
        if(parseInt($j("#subjects-dropdown").val())){
            if(parseInt($j("#topics").val())) {
                var section = $j("#sections-dropdown").val();
                var topic = $j("#topics").val();
                var subject = $j("#subjects-dropdown").val();
                getStudentsByCourseBySection(subject, topic, section);
                getReportxls(subject,topic,section);
                $j(".pagecover-onload").hide();
            }else{
                alert("please select one topic");
            }
        }else{
            alert("please select one subject");
        }
    });
    var $rows = $j('.course-list-table tbody tr');
    $j('.search').keyup(function() {
        var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

        $rows.show().filter(function() {
            var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        })
        // $j(".pagecover-onload").hide();
        .hide();
    });
</script>
<?php
echo $OUTPUT->footer();
?>
