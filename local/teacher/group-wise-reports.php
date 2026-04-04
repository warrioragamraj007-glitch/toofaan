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
       <center> <h3>Group Wise Report</h3></center>
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
                    
               <div class="col-md-12 course-results-header-bottom"
     id="groupExportWrapper"
     style="border-bottom: 2px solid #ddd; margin-bottom: 10px; position: relative; display:none;">

    <div style="float: right; margin-top: 8px;">
        <a onclick="exportGroupStudents()" 
           style="background:#e74c3c; color:white; padding:8px 16px; border-radius:4px; cursor:pointer; 
                  font-weight:bold; font-size:14px; text-decoration:none; display:inline-block; 
                  box-shadow:0 3px 8px rgba(0,0,0,0.25); transition:all 0.25s; border: none;"
           onmouseover="this.style.background='#c0392b'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.background='#e74c3c'; this.style.transform=''; this.style.boxShadow='0 3px 8px rgba(0,0,0,0.25)';"
           title="Export only group students with summary">
            <i class="fa fa-users" style="margin-right:8px;"></i>
            GROUP Export
        </a>
    </div>
    <div style="clear: both;"></div>
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
                    "mid": 30,
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
                    $j("#groupExportWrapper").fadeIn();
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


<script>
function exportGroupStudents() {
    var table = document.getElementById("studentTable");
    if (!table) {
        alert("Table not loaded! Please click 'More Details' first.");
        return;
    }

    // Use SheetJS (XLSX) to create real Excel file
    if (typeof XLSX === 'undefined') {
        alert("XLSX library not loaded!");
        return;
    }

    var wb = XLSX.utils.book_new();
    var ws_data = [];

    // === ADD HEADER ===
    var headerRow = table.querySelector("thead tr");
    var header = [];
    headerRow.querySelectorAll("th").forEach(th => {
        header.push(th.textContent.trim());
    });
    ws_data.push(header);

    // === ADD ALL ROWS (Students + Summary Rows) ===
    var rows = table.querySelectorAll("tbody tr");
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var cells = row.querySelectorAll("td");
        if (cells.length === 0) continue;

        var rowData = [];
        var isSummary = row.textContent.toLowerCase().includes("summary") ||
                        row.style.backgroundColor === "rgb(44, 62, 80)";

        cells.forEach(cell => {
            var text = cell.textContent.trim().replace(/\s+/g, ' ');
            text = text.replace(/<[^>]*>/g, ''); // Remove HTML
            rowData.push(text || "");
        });

        // Include student rows with group OR summary rows
        if (isSummary || 
            (cells.length >= 3 && 
             cells[2].textContent.trim() && 
             cells[2].textContent.trim() !== "--")) {
            ws_data.push(rowData);
        }
    }

    var ws = XLSX.utils.aoa_to_sheet(ws_data);

    // === STYLE SUMMARY ROWS (Like Your Screenshot) ===
    var range = XLSX.utils.decode_range(ws['!ref']);
    for (var r = range.s.r; r <= range.e.r; r++) {
        var row = ws_data[r];
        if (row && row.join("").toLowerCase().includes("summary")) {
            for (var c = range.s.c; c <= range.e.c; c++) {
                var cell = ws[XLSX.utils.encode_cell({r: r, c: c})];
                if (!cell) {
                    cell = ws[XLSX.utils.encode_cell({r: r, c: c})] = {v: ws_data[r][c] || ""};
                }
                cell.s = {
                    fill: {fgColor: {rgb: "2C3E50"}},
                    font: {bold: true, color: {rgb: "FFFFFF"}},
                    alignment: {horizontal: "center"}
                };
            }
        }
    }

    XLSX.utils.book_append_sheet(wb, ws, "Group Report");
    XLSX.writeFile(wb, "Group_Report_With_Summary_" + 
                   new Date().toISOString().slice(0,10) + ".xlsx");
}
</script>
<?php
echo $OUTPUT->footer();
?>
