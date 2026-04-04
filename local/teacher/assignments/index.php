
<?php

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
$PAGE->requires->css('/local/teacher/teacher.css',true);
// require_once(dirname(__FILE__) ."/../../teacher/myreports_ajax.php");
$PAGE->requires->css("/local/teacher/assignments/assignmentscss.css");
echo '<input id="baseUrl" type="hidden" value="'.$CFG->wwwroot .'"/>';
// $PAGE->set_url('/local/teacher/assignments/index.php');
// $PAGE->set_context(context_system::instance());
// $PAGE->set_pagelayout('standard');
global $OUTPUT, $PAGE, $CFG;
$PAGE->requires->css('/local/teacher/testcenter/css/custom.css');
$PAGE->requires->js('/theme/universo/javascript/jquery-2.1.0.min.js');
// $PAGE->requires->js('/theme/universo/javascript/jquery.tablesorter.min.js');
// $PAGE->requires->js('/local/teacher/xlsx.full.min.js');

// $PAGE->requires->js('/local/teacher/reports/js/reports.js');

require_login();
if(user_has_role_assignment($USER->id,3)) {
    $enrolledcourses =enrol_get_my_courses();
}else{
    redirect($CFG->wwwroot);
}
$PAGE->set_title('Tessellator 5.0 - Assignments');

echo $OUTPUT->header();
    // $category_typeids=getTeacherEnrolledCategories();
    $courseid = $_GET['cid'];
    $instanceid = $_GET['instanceid'];
    $activityid = $_GET['actid'];
    $section = $_GET['secname'];
    $assignname = $_GET['name'];
    // global $DB;
    // $cname = "SELECT fullname FROM mdl_course WHERE id = '$courseid'" ;
    // $result = $DB->get_records_sql($cname);
    // var_dump($result);
    // if($result){

    $coursename = getname($courseid);
    // }
    // echo $coursename;/
    // var_dump($coursename);
    function getname($cid){
        global $DB;
       
        $sql="SELECT fullname FROM mdl_course WHERE id = '$cid'" ;
        // echo $sql;
        $res=$DB->get_record_sql($sql);
        if($res){
            $name=$res->fullname;
        }
        return $name; 
    }
    
?>

<head>
    <link rel="stylesheet" type="text/css"  href="<?php echo $CFG->wwwroot.'/' ?>local/teacher/reports/c3.css">
   
</head>
<body>
<input type="hidden" id="current-category" value="" />
<div class="container container-demo" style="">

    <?php
    echo '<div  class="pagecover-onload">
        <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
            <div>PLEASE WAIT</div><div><img src="'.$CFG->wwwroot.'/pix/loading.gif"></div>
        </div>
        <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
            <div class="loading-msg"></div>
        </div>
    </div>';
    ?>

    <div class="upload-performance" style="margin-left:85%;margin-top:2%;margin-bottom:-1%;display:none;" >
            <a target="_blank" href="http://teleuniv.in/trinetra/pages/templates/wrapper/uploadcpcperformance/upload-cpc-performance.php" class="upload-button" style="background-color: #3c8dbc;" >Upload To Sanjaya</a>
        </div>
    <div class="course-results-div col-md-12" style='margin-top:30px' >

             <div class="row" style='display:none'>
                   <div class="col-md-4">
                       <h3 style="margin-left: 3%;">Assignments</h3>
                     </div>

                  <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-2">
                               <div class="dropdown-lable">Subject</div>
                             </div>
                            <div class="col-md-10">
                               <select id="subjects-dropdown" class="form-control">
                                 <option value="0">Select</option>
                                   <?php
                                    foreach ($enrolledcourses as $course) {
                                   echo "<option value=\"$course->id\">$course->fullname</option>";
                                       }
                                             ?>
                                </select>
                            </div>
                      </div>
                    </div>

                   <div class="col-md-4">
                          <div class="row">
                              <div class="col-md-8">
                                  <input type="text" class="search form-control" placeholder="Search">
                                 </div>
                             <div class="col-md-4">
                                  <span class="togglemsg" style="cursor:pointer" title="Show or hide" data-status="hide">
                                     <i class="fa fa-arrow-circle-up" aria-hidden="true"></i>
                                    </span>
                                </div>
                           </div>
                     </div>
              </div>

        <div style="clear: both"></div>
    <div class="tabbable tabs-left" style="padding-top:10px">

        <div class="tab-pane " id="b" >

                <div class="course-results " id="assignments-div" style='display:none'>
                    <div style="padding-bottom:0px;" class="" id="container">

                        <table class="CSSTableGenerator table table-hover course-list-table " id="studentTable">
                            <thead>
                            <tr>
                                <th class="header" style="width: 6%">Select</th>
                                <th class="header headerSortUp">Activity Name</th>
                                <th class="header">Status</th>
                                <th class="header">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="grade-info">
                            <tr><td class="header" style="width: 6%">--</td><td class="header">--</td><td class="header">--</td><td class="header">--</td></tr>
                            <tr><td class="header" style="width: 6%">--</td><td class="header">--</td><td class="header">--</td><td class="header">--</td></tr>
                            <tr><td class="header" style="width: 6%">--</td><td class="header">--</td><td class="header">--</td><td class="header">--</td></tr>
                            <tr><td class="header" style="width: 6%">--</td><td class="header">--</td><td class="header">--</td><td class="header">--</td></tr>
                            </tbody>
                        </table>

                    </div>
                    <div style="height: 10px;"></div>
                </div>
                <!-- summary div start -->
                <div style="clear: both"></div>

                <div style="padding-bottom:64px;" class="status-counts-div" id="container">


                    <div class="showactivity">
                        <span>Showing Submissions of </span><br/>
                        <span class="curassign"> <?php echo $assignname ?>
                            <!-- course: <?php echo $coursename ?> assignment: <?php echo $assignname ?>  -->
                            <!-- <span style="color: red;">course:</span> <?php echo $coursename ?>
                            <span style="color: red;">assignment:</span> <?php echo $assignname ?> -->
                        </span>
                    </div>


                    <!-- new status divs -->

                    <div class="status-div">



                        <div id="btnredStars" class="crstarCount-div status-divs" title="Not Submitted">

                            <div class="status-numbers crstarCount-status-numbers">
                                <div class="crstarCount-indicator indicator">Not Submitted</div>
                                <div class="statuscounts"><span class="crstarCount" id="crstarCount">0</span> <span class="of-lable">of</span> <span class="loggedinCount">0</span></div>
                            </div>
                        </div>

                        <div class="csubCount-div status-divs " title="Submitted">

                            <div class="status-numbers csubCount-status-numbers">
                                <div class="csubCount-indicator indicator"><span class="status-label">Submitted</span></div>
                                <div class="statuscounts"><span class="csubCount" id="csubCount">0</span> <span class="of-lable">of</span> <span class="loggedinCount">0</span></div>
                            </div>
                        </div>

                        <div class="cgradeCount-div status-divs " title="Graded">

                            <div class="status-numbers cgradeCount-status-numbers">
                                <div class="cgradeCount-indicator indicator"><span class="status-label">Graded</span></div>
                                <div class="statuscounts"><span class="cgradeCount" id="cgradeCount">0</span> <span class="of-lable">of</span> <span class="loggedinCount">0</span></div>
                            </div>
                        </div>

                        <div id="btngreenStars" class="cstarCount-div status-divs " title="GreenStars">

                            <div class="status-numbers cstarCount-status-numbers">
                                <div class="cstarCount-indicator indicator">Green Stars</i></div>
                                <div class="statuscounts"><span class="cstarCount" id="cstarCount">0</span> <span class="of-lable">of</span> <span class="loggedinCount">0</span></div>
                            </div>
                        </div>
                        <!-- <div class="refresh-icons " style="margin-right: 0%;" >
                            <i  id="refresh" class="fa fa-1x fa-refresh" aria-hidden="true" title="REFRESH STUDENT ASSIGNMENTS"></i>
                        </div> -->
                        <div class="refresh-icons " style="margin-right: 0%; float:none">
                            <i id="refresh" class="fa fa-1x fa-refresh" aria-hidden="true" title="REFRESH STUDENT ASSIGNMENTS" style="margin-left: 3%;"></i>
                        </div>
                        <div class="excel" style="float: right;">
                            <i class="fa fa-1x fa-download" style="margin-top:-32%;     margin-left: 50%;   font-size: 125%;margin-right: -45%;" aria-hidden="true" id="sxls">XLSX</i>
                                    </div>
                        <!-- <div class="excel">
                            <i class="fa fa-1x fa-download"  style="margin-top:5%;     margin-left: 3px;   font-size: 15px;" aria-hidden="true" id="sxls">XLS</i>
                                    </div> -->

                    </div><!-- end of status div -->



                    <div class="showfilters" style="margin-left: 3%;">
                        <div class="section-div" style="width:26%;float:left;margin-top: 1%;margin-right: 5%">
                            <select id="sections-dropdown">
                                <option value="All">All</option>
                            </select>
                        </div>
                        <div class="search-div" style="width:68%;float:left;margin-top: 1%;margin-right: 1%">
                            <input type="text" placeholder="search" class="stusearch" />
                        </div>
                    </div>



                </div>

                <!-- more details div start -->

                <div class="course-results " id="detailed-info">
                    <table id="myTable" class="CSSTableGenerator table table-hover course-list-table tablesorter">
                        <thead>
                        <tr>
                            <th class="header" style="text-align:center">Status</th>
                            <th class="header">Roll No</th>
                            <th class="header">Full Name</th>
                            <th class="header" style="text-align:center">Section</th>
                            <th class="header headerSortUp">Last Submission</th>
                            <th class="header" style="text-align:center">Grade(%)</th>
                        
                            <th class="header" style="text-align:center">Comments</th>
                           
                        </tr>
                        </thead>
                        <tbody><tr>
                            <td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td>
                        </tr></tbody>
                    </table>
                </div>
                <!-- more details div start -->

            </div><!-- end of course results-->
        </div>


        <input type="hidden" id="moredetailsFlag" value="0" />
        <input type="hidden" id="currentaction" value="3" /><!-- 3 not started,2-closed,1-started,0-stopped -->
        <input type="hidden" id="currentactivityid" value="0" />
        <input type="hidden" id="instanceid" value="0" />


     </div>
   </div>
</div>


<div class="emptytable" style="display: none">
<table id="courseTable" class="CSSTableGenerator table table-hover course-list-table ">
    <thead>
    <tr>
        <th class="header" style="text-align:center">Status</th>
        <th class="header">Roll No</th>
        <th class="header">Full Name</th>
        <th class="header" style="text-align:center">Section</th>
        <th class="header headerSortUp">Last Submission</th>
        <th class="header" style="text-align:center">Grade(%)</th>
       
        <th class="header" style="text-align:center">Comments</th>

    </tr>
    </thead>
    <tbody><tr>
        <td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td>

    </tr></tbody>
</table>
</div>



<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/xlsx.full.min.js"></script>

<!-- 
<?php
// echo $OUTPUT->footer();
?> -->
<div id="popup1" class="overlay">
    <div class="popup">
        <h2 class="selectedassign"></h2>
        <a class="close" href="javascript:void(0)">&times;</a>
        <div class="assigndescription">

        </div>
    </div>
</div>
</body>
</html>


<!-- <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.table2excel.js"></script> -->
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-2.js"></script>

<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/testcenter/js/bootstrap.min.js"></script>
<!-- <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/xlsx.full.min.js"></script> -->
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>

<script>
console.log('XLSX:', XLSX);
console.log('XLSX.utils:', XLSX?.utils);
console.log('XLSX.utils.table_to_sheet:', XLSX?.utils?.table_to_sheet);

console.log('Table2Excel:', typeof $.fn.table2excel !== 'undefined' ? 'Loaded' : 'Not Loaded');
console.log('table2excel function:', typeof $.fn.table2excel === 'function' ? 'Available' : 'Not Available');

    var currdate='<?php echo date("d-m-y",time()) ?>';
    var hideshowurl='<?php echo $CFG->wwwroot."/course/mod.php?sesskey=".sesskey()."&sr=0&" ?>';
    var $j= jQuery.noConflict();
    var d = new Date();
    var dat = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();
    // srivardhin
    // var cid = "<?php echo $courseid; ?>";
    //     var instanceid = "<?php echo $instanceid; ?>";
    //     var current_activity_id = "<?php echo $activityid; ?>";
    //     var section = "<?php echo $section; ?>";
    //     $j("#currentactivityid").val(current_activity_id);
    //     $j("#instanceid").val(instanceid);
        // cid,instanceid,section,current_activity_id
     var baseUrl='<?php echo $CFG->wwwroot ?>';
       $j(document).ready(function(){
        var cid = "<?php echo $courseid; ?>";
        var instanceid = "<?php echo $instanceid; ?>";
        var current_activity_id = "<?php echo $activityid; ?>";
        var section = "<?php echo $section; ?>";
        // $j("#currentactivityid").val(current_activity_id);
        // $j("#instanceid").val(instanceid);
        getAssignmentResult1(cid,instanceid,section,current_activity_id);
    //$j("#loading").show();
    getCourses(1);
    getSections(cid)
    // Initialize tablesorter
    $j("#myTable").tablesorter({
        // sortList: [[1, 0]] // Sort by the second column (Roll No) in ascending order
    });
    //  download excel sheet
    $j("#sxlst").click(function() {
        var coursename = "<?php echo $coursename; ?>";
        var assignname = "<?php echo $assignname; ?>";
        var rowCount = $j('#myTable tbody tr').length;
if (rowCount > 0) {
    // Clone the original table
    var $clonedTable = $j('#myTable').clone();

    // Define the indices of the columns you want to keep (zero-based)
    var columnsToKeep = [1,2,3,5]; // Example: Keep the 1st and 3rd columns

    // Function to remove unwanted columns
    function removeUnwantedColumns(row, columnsToKeep) {
        var $cells = $j(row).children();
        $cells.each(function(index) {
            if (columnsToKeep.indexOf(index) === -1) {
                $j(this).remove();
            }
        });
    }

    // Remove unwanted columns from the cloned table
    $clonedTable.find('thead tr').each(function() {
        removeUnwantedColumns(this, columnsToKeep);
    });
    $clonedTable.find('.gradetime').remove(); // Remove gradetime elements
    $clonedTable.find('tbody tr').each(function() {
        removeUnwantedColumns(this, columnsToKeep);
    });
 // Remove hidden spans from the cloned table
 $clonedTable.find('span[style*="display:none"]').remove();
    // Create a temporary element to hold the filtered table
    var $tempDiv = $j('<div></div>').append($clonedTable);

    // Use table2excel on the filtered cloned table
    $tempDiv.find("#myTable").table2excel({
        exclude: ".noExl",
        name: "Table2Excel",
        filename: coursename+'-'+assignname+'-report-' + new Date().toISOString().split('T')[0], // Filename with date
        fileext: ".xls" // File extension
    });

    // Remove the temporary element
    $tempDiv.remove();
}
                 else {
                    alert("No records found");
                }
            });

$j("#sxls").click(function() {
    
    var coursename = "<?php echo $coursename; ?>";
    var assignname = "<?php echo $assignname; ?>";
    var rowCount = $j('#myTable tbody tr').length;

    if (rowCount > 0) {
        // Clone the original table
        var $clonedTable = $j('#myTable').clone();

        // Define the indices of the columns you want to keep (zero-based)
        var columnsToKeep = [1, 2, 3, 5]; // Example: Keep specific columns

        // Function to remove unwanted columns
        function removeUnwantedColumns(row, columnsToKeep) {
            var $cells = $j(row).children();
            $cells.each(function(index) {
                if (columnsToKeep.indexOf(index) === -1) {
                    $j(this).remove();
                }
            });
        }

        // Remove unwanted columns from the cloned table
        $clonedTable.find('thead tr').each(function() {
            removeUnwantedColumns(this, columnsToKeep);
        });
        $clonedTable.find('.gradetime').remove(); // Remove gradetime elements
        $clonedTable.find('tbody tr').each(function() {
            removeUnwantedColumns(this, columnsToKeep);
        });

        // Remove hidden spans from the cloned table
        $clonedTable.find('span[style*="display:none"]').remove();
// console.log($clonedTable);
        // Create a temporary element to hold the filtered table
        // var $tempDiv = $j('<div></div>').append($clonedTable);

        // Convert the filtered table to a worksheet (use the cloned table inside $tempDiv)
        var ws = XLSX.utils.table_to_sheet($clonedTable[0]);
console.log(ws);
        // Create a new workbook and add the worksheet
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

        // Generate the filename
        var filename = coursename + '-' + assignname + '-report-' + new Date().toISOString().split('T')[0] + '.xlsx';

        // Export the workbook as an .xlsx file
        XLSX.writeFile(wb, filename);

        // Remove the temporary element
        // $tempDiv.remove();
    } else {
        alert("No records found");
    }
});


    $j("#subjects-dropdown").on("change", function () {
        //$j("#loading").show();
        getAssignments($j(this).val());
        getSections($j(this).val());
        clearSearchFields();
        var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());

        $j("#currentactivityid").val(0);
        $j("#instanceid").val(0);
        $j(".curassign").text('--');
        $j("#detailed-info").html($j(".emptytable").html());
        $j('#csubCount').text(0);
        $j('#cgradeCount').text(0);
        $j('#cstarCount').text(0);
        $j('#crstarCount').text(0);
        $j('#watchCount').text(0);
        $j('.loggedinCount').text(0);

    });





   function getSections(cid){
    $j(".pagecover-onload").show();
    $j.ajax({
        type: "GET",
        dataType: 'html',
        data: {
            "trmid": 22,
            "trcid": cid,
        },
        url: baseUrl + "/local/teacher/myreports_ajax.php",
        success: function (data) {
            $j("#sections-dropdown").html(data);
            $j(".pagecover-onload").hide();
        }
    });
 }//end of getResults

   function getCourses(catid){
    //alert(baseUrl);
    $j(".pagecover-onload").show();
    $j.ajax({
        type: "GET",
        dataType: 'html',
        data: {
            "trmid": 2,
            "trcatid": catid
        },
        url: baseUrl + "/local/teacher/myreports_ajax.php",
        success: function (data) {
            $j("#subjects-dropdown").html(data);
            $j(".pagecover-onload").hide();

        },
        complete: function (xhr, status) {
            if($j('#subjects-dropdown').find('option').eq(1).val()){
                $j('select[id="subjects-dropdown"] option:eq(1)').attr('selected', 'selected');
                getAssignments($j('#subjects-dropdown').find('option').eq(1).val());
            }

        }
    });
   }//end of getResults


   function getAssignments(cid){
    $j(".pagecover-onload").show();
    $j.ajax({
        type: "GET",
        dataType: 'html',
        data: {
            "trmid": 24,
            "trcid": cid,
        },
        url: baseUrl + "/local/teacher/myreports_ajax.php",
        success: function (data) {
            $j(".grade-info").html(data);
            $j(".pagecover-onload").hide();
        },
        complete: function (xhr, status) {
            $j("#studentTable").tablesorter({
                // sort on the fourth column , order asc
                //sortList: [[4,1]]
            });

            $j("#studentTable").trigger("update");
            // set sorting column and direction, this will sort on the first and third column

            $j("#studentTable").trigger([]);

            var $rows = $j('#studentTable tbody tr');

            $j('.search').keyup(function() {
                var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

                $rows.show().filter(function() {
                    var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
                    return !~text.indexOf(val);
                }).hide();
            });//end of search

        }
    });
   }//end of getAssignments

 $j(document).delegate(".onlinetext","click",function(){
        var desc=$j(".online"+$j(this).data("uid")).html();
        $j(".selectedassign").text($j(this).data("uname")+" - Onlinetext");
       // alert(desc);
        if(desc){

            $j(".assigndescription").html(desc);
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }else{
            $j(".assigndescription").html('not available');
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }
    });

    $j("#talink a").on("click",function(){
        location.href=$j(this).attr('href');
    });

    function clearSearchFields(){
        $j('.search').val('');
        $j('.stusearch').val('');

            var val = $j.trim($j('.search').val()).replace(/ +/g, ' ').toLowerCase();
            var $rows = $j('#studentTable tbody tr');
            $rows.show().filter(function() {
                var text = $j('.search').text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();

    }

    $j("#sections-dropdown").on("change", function () {

        // var instanceid=parseInt($j("#instanceid").val());
        // var cid=parseInt($j("#subjects-dropdown").val());
        var section=$j("#sections-dropdown").val();
        // getAssignmentResult(current_activity_id);

        // var current_activity_id=parseInt($j("#currentactivityid").val());
        clearSearchFields();
        // if(instanceid&&current_activity_id){
            getAssignmentResult1(cid,instanceid,section,current_activity_id);

        // }
        // else{
        //     alert("please select one assignment");
        // }

    });


    $j( ".togglemsg" ).click(function() {
        //alert($j(".togglemsg").text().includes('hide')+'-'+$j(".togglemsg").text().includes('show'));
        if($j(".togglemsg").data("status").includes('hide')){
            $j(".togglemsg").data("status",'show');
            $j(".togglemsg").html('<i class="fa fa-arrow-circle-down" aria-hidden="true"></i>');
        }
        else{
            $j(".togglemsg").data("status",'hide');
            $j(".togglemsg").html('<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>');
        }
        $j( "#assignments-div" ).fadeToggle("slow");
    });

    $j(document).delegate("#refresh","click",function(){
        // var current_activity_id=parseInt($j("#currentactivityid").val());
        // var cid=parseInt($j("#subjects-dropdown").val());
        // var section=$j("#sections-dropdown").val();
        // var instanceid=parseInt($j("#instanceid").val());
        clearSearchFields();
        // if(current_activity_id&&instanceid){
            getAssignmentResult1(cid,instanceid,section,current_activity_id);
        // }
        // else{
        //     alert('select assignment');
        // }

    });

    $j(document).delegate(".radio-activity","click",function(){
        var activitytypeid=parseInt($j(this).data("mid"));
        var current_activity_id=parseInt($j(this).data("aid"));
        var cid=parseInt($j("#subjects-dropdown").val());
        var section=$j("#sections-dropdown").val();
        var instanceid=parseInt($j(this).data("insid"));
        $j("#currentactivityid").val(current_activity_id);
        $j("#instanceid").val(instanceid);
        //$j(".curassign").text();
        var curassigntext = $j(".assignname"+current_activity_id).text();
        clearSearchFields();
        if (curassigntext.length > 55) {
            $j(".curassign").text(curassigntext.substr(0, curassigntext.lastIndexOf(' ', 52)) + '...');
        }else{
            $j(".curassign").text(curassigntext);
        }
        // getAssignmentResult(current_activity_id);
        getAssignmentResult1(cid,instanceid,section,current_activity_id);
    });

    $j(document).delegate(".assignname","click",function(){
        var desc=$j(".desc"+$j(this).data("assid")).html();
        $j(".selectedassign").text($j(".assignname"+$j(this).data("assid")).text());
        if(desc){
            //alert(desc);
            $j(".assigndescription").html(desc);
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }else{
            $j(".assigndescription").html('no descritpion available');
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }

    });
    $j(document).delegate(".comments","click",function(){
        var desc=$j(".comment"+$j(this).data("uid")).html();
        $j(".selectedassign").text($j(this).data("uname")+" - Feedback");
       // alert(desc);
        if(desc){

            $j(".assigndescription").html(desc);
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }else{
            $j(".assigndescription").html('no feedback available');
            $j("#popup1").css("visibility","visible");
            $j("#popup1").css("opacity","1");
        }
    });


    $j(document).delegate(".close","click",function(){


        $j("#popup1").css("visibility","hidden");
        $j("#popup1").css("opacity","0");
        $j(".assigndescription").html();
    });


    $j(document).delegate(".lockunlock","click",function(){

        var current_activity_id=parseInt($j(this).data("actid"));
        var sesskey='<?php echo sesskey(); ?>';

        if(parseInt($j(this).data("lockflag"))){
            var action="unlock";
            var actionmsg="Allow Submissions";

        }else{
            var action="lock";
            var actionmsg="Prevent Submissions";

        }
        if (confirm('Do you want to "'+actionmsg+'"  for this user?')) {
            lockunlock(current_activity_id, sesskey, action,$j(this).attr("id"));
        }

    });

    $j(document).delegate(".showhide","click",function(){

        var status;
        if($j(this).attr('id') == 'view'){
            status="View";
        }else
        if($j(this).attr('id') == 'show'){
            status="start";
        }
        else{
            status="stop";
        }

        if (confirm('Do you want to "'+status+'" ?')) {

            var modtypeid = $j(this).data('mid');
            var modid = $j(this).attr('value');
            var id = $j(this).attr('id');
            var value = $j(this).attr('value');
            var hideshowajax = hideshowurl + id + '=' + value;
            $j("#currentactivityid").val(modid);
            clearSearchFields();
            if ($j(this).attr('id') == 'show') {
                record_activity_start_date(modid);
            }
            if ($j(this).attr('id') == 'hide') {
                record_activity_stop_date(modid);
            }
            if ($j(this).attr('id') == 'view') {
                var assignurl=baseUrl+'/mod/assign/view.php?id='+modid+'&action=grading';
                window.open(assignurl);
            }else{

                //ajax call to show or hide the activity to the student
                $j.ajax({
                    url: hideshowajax,
                    type: "GET",
                    dataType: "html",
                    success: function (data) {

                    },
                    error: function (xhr, status) {
                        alert("Sorry, there was a problem!");
                    },
                    complete: function (xhr, status) {
                        //alert(id=='show')
                        if (id == 'show') {
                            $j(".show"+$j("#currentactivityid").val()).attr("disabled",true);
                            $j(".show"+$j("#currentactivityid").val()).css("cursor","not-allowed");
                            $j(".hide"+$j("#currentactivityid").val()).attr("disabled",false);
                            $j(".hide"+$j("#currentactivityid").val()).css("cursor","pointer");

                        }
                        if (id == 'hide') {
                            $j(".show"+$j("#currentactivityid").val()).attr("disabled",false);
                            $j(".show"+$j("#currentactivityid").val()).css("cursor","pointer");
                            $j(".hide"+$j("#currentactivityid").val()).attr("disabled",true);
                            $j(".hide"+$j("#currentactivityid").val()).css("cursor","not-allowed");
                        }
                    }
                });//hide or show ajax call end
            }


        }//end of confirm



    });//end of showhide




    //this will perform storing of current activity id and time in a table
    function record_activity_start_date(modid){
        var statustime='actstatus'+modid;
        $j.ajax({
            url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
            type: "GET",
            data: {
                "aid": modid,
                "mid":2,
            },
            dataType: "html",
            success: function (data) {
                $j("."+statustime).html("<b>STARTED </b><br/>on " + data);
                $j(".actstatus"+modid).removeClass('stopped');
                $j(".actstatus"+modid).addClass('started');
            },
            error: function (xhr, status) {
                //alert("Sorry, there was a problem!");
            },
            complete: function (xhr, status) {
            }
        });//ajax call end
        }//end of the record_activity_start_date() function


    //this will perform storing of current activity id and stop time in a table
    function record_activity_stop_date(modid){
        var statustime='actstatus'+modid;

        $j.ajax({
            url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
            type: "GET",
            data: {
                "aid": modid,
                "mid":16,
            },
            dataType: "html",
            success: function (data) {
                //alert(data);
                $j("."+statustime).html("<b>STOPPED </b><br/>on " + data);
                $j(".actstatus"+modid).removeClass('started');
                $j(".actstatus"+modid).addClass('stopped');
            },
            error: function (xhr, status) {
                //alert("Sorry, there was a problem!");
            },
            complete: function (xhr, status) {
            }
        });//ajax call end
      }//end of the record_activity_stop_date() function








    function getAssignmentResult(current_activity_id){

        $j(".pagecover-onload").show();
        var assignurl=baseUrl+'/mod/assign/view.php';
        $j.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "id": current_activity_id,
                "action": 'grading',
            },
            url: assignurl,
            success: function (data) {
                $j("#detailed-info").html(data);

            },
            complete:function (xhr, status) {
                $j(".pagecover-onload").hide();
            }
            });
         }//end of getAssignmentResult

    function getAssignmentResult1(cid,instanceid,section,actid){

        $j(".pagecover-onload").show();
        var assignurl=baseUrl+'/local/teacher/assignments/enrolledstudents.php';
        $j.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "instanceid": instanceid,
                "cid": cid,
                "secname": section,
                "actid":actid
            },
            url: assignurl,
            success: function (data) {
                $j("#detailed-info").html(data);
                var subCount = $j($j.parseHTML(data)).filter("#subCount").text();
                var gradeCount = $j($j.parseHTML(data)).filter("#gradeCount").text();
                var loggedinusers = $j($j.parseHTML(data)).filter("#loggedinusers").text();
                var statusstopdate=$j($j.parseHTML(data)).filter("#statusstopdate").text();
                var cstarCount=$j($j.parseHTML(data)).filter("#cstarCount").text();
                var crstarCount=$j($j.parseHTML(data)).filter("#crstarCount").text();
                var watchCount=$j($j.parseHTML(data)).filter("#watchCount").text();

                $j('#csubCount').text(subCount);
                $j('#cgradeCount').text(gradeCount);
                $j('#cstarCount').text(cstarCount);
                $j('#crstarCount').text(crstarCount);
                $j('#watchCount').text(watchCount);
                $j('.loggedinCount').text(loggedinusers);
            },
            complete:function (xhr, status) {
                $j(".pagecover-onload").hide();
                var totalRows = ($j("#myTable")[0].tBodies[0] && $j("#myTable")[0].tBodies[0].rows.length) || 0;
                if(totalRows>0) {
                    $j("#myTable").tablesorter({
                        // sort by rollno asc
                       
                        sortList: [[1, 1]],
                        // define a custom text extraction function
                        // textExtraction: function(node) {
                            // extract data from markup and return it
                            // return node.childNodes[0].innerHTML;
                        // }
                    });
                }

                $j("#myTable").trigger("update");
                // set sorting column and direction, this will sort on the first and third column

                $j("#myTable").trigger([]);

                var $rows = $j('#myTable tbody tr');

                $j('.stusearch').keyup(function() {
                    var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

                    $rows.show().filter(function() {
                        var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
                        return !~text.indexOf(val);
                    }).hide();
                });//end of search

            }
        });
    }//end of getAssignmentResult


    function lockunlock(actid,sesskey,action,elementid){
        $j(".pagecover-onload").show();
        $j.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "id": actid,
                "action": action,
                "sesskey":sesskey,
                "userid":elementid
            },
            url: baseUrl + "/mod/assign/view.php",
            success: function (data) {
                //$j("#subjects-dropdown").html(data);
               // $j(".pagecover-onload").hide();

            },
            complete:function (xhr, status) {
                $j(".pagecover-onload").hide();

                if(action=='unlock'){
                    $j(".unlock"+elementid).removeClass('lockhide');
                    $j(".unlock"+elementid).addClass('lockshow');
                    $j(".lock"+elementid).removeClass('lockshow');
                    $j(".lock"+elementid).addClass('lockhide');

                }else{
                    $j(".lock"+elementid).removeClass('lockhide');
                    $j(".lock"+elementid).addClass('lockshow');
                    $j(".unlock"+elementid).removeClass('lockshow');
                    $j(".unlock"+elementid).addClass('lockhide');

                }

            }
        });
    }



});//end of ready function

</script>

<style>
    .container{
        padding-right: 0px;
    padding-left: 0px;
    max-width:100% !important;
    }
    .status-div {
        font-size:11px;
        margin-left: -4%;
    }
   .upload-button{
    
        background-color: #ea6645;
        border: 1px solid transparent;
        color: #fff;
        font-weight: bold;
        min-height: 30px;
        outline: none !important;
        /* padding: 6px 65px; */
        cursor: pointer;
        width:100%;
        height:100%;
        padding:10px 17px;
    }
</style>

<?php
// echo $OUTPUT->footer();
?>
