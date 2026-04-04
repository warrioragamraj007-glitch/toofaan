<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 16/5/16
 * Time: 11:41 AM
 */

require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
//require_once("$CFG->libdir/formslib.php");
$PAGE->requires->js('/student/jquery-latest.min.js', true);
$PAGE->requires->css('/teacher/teacher.css',true);
$PAGE->requires->css('/supervisor/css/supervisor.css', true);
require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');
require_once($CFG->dirroot."/teacher/myreports_ajax.php");


/************* FOR REPORTS ***************/
//require_once($CFG->dirroot.'/teacher/reports/reports_db.php');
echo '<input id="baseUrl" type="hidden" value="'.$CFG->wwwroot .'"/>';
$PAGE->set_url('/teacher/student_profile.php');

global $OUTPUT, $PAGE, $CFG;

$PAGE->requires->js('/theme/universo/javascript/jquery-2.1.0.min.js');


$PAGE->requires->js('/theme/universo/javascript/jquery.tablesorter.min.js');

$PAGE->requires->js('/teacher/reports/js/reports.js');

//$PAGE->requires->js('/teacher/datatable.js',true);
//$PAGE->set_url('/teacher/reports.php');
//require_login();
/*if(user_has_role_assignment($USER->id,4)) {
    $context = context_user::instance($USER->id);
    $PAGE->set_context($context);
    // $enrolledcourses = block_course_overview_get_sorted_courses();
}else{
    redirect($CFG->baseUrl);
}
*/
//$PAGE->blocks->add_region('content');
$PAGE->set_title('Tessellator 4.0 - Reports');

echo $OUTPUT->header();

$userid=$_GET['sid'];

$userobj = get_complete_user_data(id, $userid);

$categories=getStudentEnrolledCategories($userid);


$multiflag=0;//this indicates that the user is enrolled for more than one course or not
if(count($categories)>1){
    $multiflag=1;
}

if($_GET){
    $currentProgramId=$_GET['cid'];
}
if(!$currentProgramId){
    $currentProgramId=$categories[0]['catid'];
}


$subjectGrades=getSubjectsGrades($userid,$currentProgramId);
$totalLabsandQuizes=getTotalCourseQuizes($currentProgramId,$userid)+getTotalCourseLabs($currentProgramId,$userid);
$totalAttemptedLabsandQuizes=getAttemptedCourseQuizes($currentProgramId,$userid)+getAttemptedCourseLabs($currentProgramId,$userid);

$totalCourseMeanGrade=getTotalCourseMeanGrade($userid,$currentProgramId);
$totallabPerformance=getAttemptedCourseLabs($currentProgramId,$userid).'/'.getTotalCourseLabs($currentProgramId,$userid);
$totalquizPerformance=getAttemptedCourseQuizes($currentProgramId,$userid).'/'.getTotalCourseQuizes($currentProgramId,$userid);

$latestPerformance=getLastTwoWeekPerformance($userid,$currentProgramId);

$courseidArray=getCoursesByCateogoryByUser($userid,$currentProgramId);//array(18,19,21,22,23);

$result=TotalCompletedActivities($courseidArray[0]['id'],$userid);
//var_dump($latestPerformance);
$ccactivities= $result['activities'];

$profilepic=$CFG->portal.'registration/userpics/'.$userobj->profile['portalregid'].'.png';
if(is_array(getimagesize($profilepic))){
    $userpic=$CFG->portal.'registration/userpics/'.$userobj->profile['portalregid'].'.png?'.time();;
}else{
    $userpic=$CFG->baseUrl.'user/pix.php/2/f1.jpg';
}

//var_dump(getAttemptedCourseQuizes($categories[0]['catid'],$userid));
//var_dump($totalAttemptedLabsandQuizes);
//echo (getCoursesByCateogory(7));
//echo getGradeByTopicAndCourse(40,308,'Finishing School');
/**************************** MENU *******************************************/
?>

<head>
    <link rel="stylesheet" type="text/css"  href="<?php echo $CFG->wwwroot ?>/teacher/reports/c3.css">
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
            1display: none;
        }
        .table-responsive {
            min-height: 0.01%;
            overflow-x: hidden;
        }
        .latest-performance-table td,.topic-info td,.studnets-info td {
            font-size: 12px;
        }
        #myTable,#courseTable,#subjectTable {
            border: 1px solid #e4e1da;
        }
        #myTable thead tr th,#courseTable thead tr th,#subjectTable thead tr th {
            background-color: #ea6645;
            background-image: linear-gradient(to bottom, #ea6645, #ea6645);
            background-repeat: repeat-x;
            color: #fff !important;
            font-weight: bold !important;
            padding: 3px !important;
            text-align: center;
            border: 1px solid #e4e1da;
        }

        #myTable tbody tr td,#courseTable tbody tr td,#subjectTable tbody tr td {
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
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
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
            /*width: 100% !important;min-height: 35px;*/
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
            padding: 8px 18px;
            cursor: pointer;
            float: right;
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
            padding: 8px 18px;
            cursor: pointer;
        }

        .status-count{
            margin: 10px 0px;
            border:1px solid #c3c3c3;
            margin: 2px;
            width: 24%;
            float: left;
            height: 50px;
            font-size: 13px;
        }
        .status-count div{
            float: left;
            padding-top: 15px;
            text-align: center;
        }
        .status-box{

            width: 100%; margin-bottom: 10px;
        }
        .status-icons{
            width: 100%;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 10px;

        }
        .status-icons span {
            padding: 5px;
            cursor: pointer;
            margin: 2px;
        }
        .status-count-label {
            width: 60%;
            height: 100%;
            color:white;
        }

        .latest-performance{
            display:none;
        }
        .meangrade-label{
            background-color: #0070C0;
        }
        .attandance-label{
            background-color: #00B050;
        }
        .labs-label{
            background-color:#00B0F0;
        }
        .quiz-lable{
            background-color:#2E75B6
        }
        .status-values {
            background-color: #BDD7EE;
            height: 100%;
            width: 40%;
        }
        .my-profile-table td {
            padding: 10px;
        }
        #sendmail, #sendsms {
            width: 90%;
        }
        .popup{
            top: 5%;
        }
        .overlay1{
            height:100%;
            top: 10%;
        }
        .scourse{
            border-radius: 5px;
            font-size: 15px;
            height: 35px;
            padding-right: 5px;
            padding-top: 5px;
            text-align: center;
            border: 1px solid #ddd;
        }
    </style>


</head>
<body>

<?php
if (!(user_has_role_assignment($USER->id,2) ) ) {

    redirect($CFG->baseUrl);
}
?>

<input type="hidden" id="current-category" value="" />
<div id="navbar" class="col-md-12">
    <a id='dlink' href='<?php echo $CFG->portal.'registration/login.php'?>'>Dashboard</a> <span>/</span>
<a id='dlink' href='<?php echo $CFG->wwwroot?>'>Reports</a> <span>/</span> <b>Student Profile  (<?php echo $userobj->firstname." ",$userobj->lastname ?>)</b>
</div>


<?php if($multiflag): ?>
    <div class="col-md-3 pull-right">
        <select class="course-dropdown">
            <?php for($pi=0;$pi<count($categories);$pi++):?>

                <option value="<?php echo $categories[$pi]['catid'] ?>"
                    <?php if($currentProgramId==$categories[$pi]['catid']){ echo 'selected';} ?>
                    ><?php echo $categories[$pi]['catname'] ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-1 pull-right scourse" >Course</div>
    <div style="clear: both;"></div>
<?php endif; ?>




<div class="container container-demo" >

    <div class="tabbable tabs-left">

        <ul class="teacherreports-tabs nav nav-tabs ">
            <li id="activities-info" class="tab-parents">Performance
            </li>
            <li id="activities-info" class="active"><a href="#a" data-toggle="tab" >Last 2-weeks
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                </a></li>

            <li id="agent-status" ><a href="#c" data-toggle="tab">Overall
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                </a></li>
            <li  ><a href="#d" data-toggle="tab">View Profile
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                </a></li>

            <li id="courses-status" class="tab-parents">Courses


            </li>
            <li><a href="#b" data-toggle="tab">Subjects
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>

            <div class="col-md-12" style="text-align:center; margin-top: 25px;">
                <input id="sendmail" type="button" name="search" class="btn" value="Send Mail">
            </div>
            <div class="col-md-12" style="text-align:center">
                <input id="sendsms" type="button" name="search" class="btn" value="Send SMS">
            </div>
        </ul>



        <div class="tab-content">
            <div class="tab-pane active" id="a">


                <!-- status icons start -->
                <div class="col-md-12" >


                    <div class="col-md-12 status-box">

                        <div class="status-count">
                            <div class="meangrade-label status-count-label">Mean Grade</div>
                            <div class="status-values"><?php echo  $totalCourseMeanGrade; ?></div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label attandance-label">Attendance</div>
                            <div class="status-values"><?php echo $totalAttemptedLabsandQuizes; ?>/<?php echo $totalLabsandQuizes; ?></div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label labs-label">Labs</div>
                            <div class="status-values"><?php echo $totallabPerformance; ?></div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label quiz-lable">Quiz</div>
                            <div class="status-values"><?php echo $totalquizPerformance; ?></div>
                        </div>

                    </div><!-- end of status box -->


                    <div style="width: 100%;text-align: center;font-size: 16px;display:none"><span>Course-wise Class Performance</span></div>
                    <div class="col-md-12" style="padding-right: 0;">


                        <div class="status-icons">
                            <span style="text-align: center;font-size: 16px;color:#000">Last 2 weeks Performance</span>
                                 <span class="graph"><i class="fa fa-signal" style="font-size: 16px;" aria-hidden="true"></i>
</span>

                            <span class="latesttable"><i class="fa fa-table" style="font-size: 16px;" aria-hidden="true"></i>
</span>
                                <span class="download" id="twoxls">XLS
</span>
                        </div>
                        <div style="padding-right: 0;" class="latestsearch col-md-12">
                            <!--<span style="float:left;padding-top: 10px;"><?php //echo '('.$latestPerformance['rowcount'].')'; ?> rows found</span>-->
                            <input style="float:right;width:200px; margin-bottom: 5px;" class="search " type="text" value="" />
                        </div>
                    </div>
                </div>

                <div class="col-md-12 latest-performance-div">

                    <div class="latest-performance">


                        <section id="course-list">
                            <div class="table-responsive">
                                <table class="table table-hover course-list-table " id="myTable">
                                    <thead>
                                    <tr>
                                        <th>SNO</th>
                                        <th>TYPE</th>
                                        <th>ACTIVITY NAME</th>
                                        <th>GRADE</th>
                                        <th>ATTENDANCE</th>
                                    </tr>
                                    </thead>

                                    <tbody class="latest-performance-table">
                                    <?php if(count($latestPerformance)): ?>
                                        <?php for($i=0;$i<count($latestPerformance);$i++): ?>
                                            <tr>
                                                <td ><?php echo ($i+1); ?></td>
                                                <td ><?php echo strtoupper($latestPerformance[$i]['moduletype']); ?></td>
                                                <td ><?php echo ucwords($latestPerformance[$i]['actname']); ?></td>
                                                <td><?php echo round($latestPerformance[$i]['grade'],2); ?></td>
                                                <td><?php

														if (is_numeric($latestPerformance [$i]['grade'])||(strcasecmp($latestPerformance [$i]['grade'],'PRESENT')==0)) {
															echo 'PRESENT';
														} else {
															echo 'ABSENT';
														}
														?></td>
                                            </tr>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <style>
                                            .latest-performance{
                                                height: 285px;
                                            }
                                        </style>
                                        <tr>
                                            <th class="no-data"  colspan="5" rowspan="5">No Report is generated yet.</th>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>


                    <div class=" latest-performance-graph">

                        <div id="latestperformance" >
                            <div class="no-data">No Graph is generated yet.</div>
                        </div>
                    </div>

                </div>


            </div>
            <div class="tab-pane " id="b">

                <div class="course-results-div">

                    <div  class="col-md-12">

                        <div class="col-md-4" style='padding-left: 10px;margin-bottom:15px'>
                            <select id="subjects-dropdown">
                                <?php for($i=0;$i<count($courseidArray);$i++): ?>
                                    <option value="<?php echo $courseidArray[$i]['id'] ?>"><?php echo $courseidArray[$i]['name'] ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4" style='margin-bottom:15px;float: right'>
                            <span class="download2" id="sxls">XLS</span>
                            <input style="float:left;width:185px; margin-bottom: 5px;" class="search " type="text" value="" />
                        </div>
                        <div  class="activities col-md-12" style=" padding-left: 10px;">

                            <section id="course-list">
                                <div class="table-responsive">
                                    <table class="table table-hover course-list-table " id="subjectTable">
                                        <thead>
                                        <tr>
                                            <th>SNO</th>
                                            <th>ACTIVITY TYPE</th>
                                            <th>ACTIVITY NAME</th>
                                            <th>GRADE</th>
                                        </tr>
                                        </thead>
                                        <tbody id="activities-table">
                                        <?php if(count($ccactivities)): ?>
                                            <?php for($i=0;$i<count($ccactivities);$i++): ?>
                                                <?php// print_r($activities[$i]); ?>
                                                <tr>
                                                    <td ><?php echo ($i+1); ?></td>
                                                    <td ><?php echo strtoupper($ccactivities[$i]['moduletype']); ?></td>
                                                    <td ><?php echo ucwords($ccactivities[$i]['actname']); ?></td>
                                                    <td><?php echo $ccactivities[$i]['grade']; ?></td>
                                                </tr>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <tr>
                                                <th class="no-data" colspan="4" rowspan="3">No Report is generated yet.</th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div><!-- end of b div col md 10 -->


                </div><!-- end of course results-->
            </div><!-- end of b div -->

            <div class="tab-pane " id="c">



                <div class="col-md-12 status-box">

                    <div class="status-count">
                        <div class="meangrade-label status-count-label">Mean Grade</div>
                        <div class="status-values"><?php echo  $totalCourseMeanGrade; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label attandance-label">Attendance</div>
                        <div class="status-values"><?php echo $totalAttemptedLabsandQuizes; ?>/<?php echo $totalLabsandQuizes; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label labs-label">Labs</div>
                        <div class="status-values"><?php echo $totallabPerformance; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label quiz-lable">Quiz</div>
                        <div class="status-values"><?php echo $totalquizPerformance; ?></div>
                    </div>

                </div><!-- end of status box -->


                <div class="student-results-div">


                    <div class="col-md-12" style=" margin-bottom: 25px;">
                        <div id="chart" class="col-md-9"><div class="no-data">No Graph is generated yet.</div> </div>
                        <div class="col-md-1"></div>
                    </div>


                </div><!-- end of course results-->



            </div><!-- end of c -->



            <div class="tab-pane " id="d">




                <div class="row prof" style="background: whitesmoke none repeat scroll 0% 0%;
margin: 9px 20px;">
                    <div class="col-md-2">
                        <figure class="course-image">
                            <div class="image-wrapper"><img style="margin:15px;" src="<?php echo $userpic; ?>"></div>
                        </figure>
                    </div>
                    <div class="col-md-10">
                        <header>
                            <h2 class="course-date"><?php echo ucfirst($userobj->firstname ."  ". $userobj->lastname); ?></h2>
                            <div class="course-category">
                                <div style="font-size: 16px; margin-top: -5px;" class="course-category pull-right">Email:<a href="#"> <?php
                                        if(empty($userobj->email))
                                            echo "--";
                                        else
                                            echo $userobj->email;?> </a></div></div>


                        </header><hr style="margin-top: 5px;
margin-bottom: 13px;">
                        <div class="course-count-down pull-left" >
                            <figure class="course-start">Overall Grade:</figure>
                            <!-- /.course-start -->
                            <div class="count-down-wrapper" style=""><?php
                                if(empty($totalCourseMeanGrade))
                                    echo "0";
                                else
                                    echo $totalCourseMeanGrade ?></div><!-- /.count-down-wrapper -->

                        </div>



                    </div>

                </div>
                <!-- <div class="col-md-12 status-box">

                 <div class="status-count">
                        <div class="meangrade-label status-count-label">Mean Grade</div>
                        <div class="status-values"><?php //echo  $totalCourseMeanGrade; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label attandance-label">Attandance</div>
                        <div class="status-values"><?php //echo $totalAttemptedLabsandQuizes; ?>/<?php //echo $totalLabsandQuizes; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label labs-label">Labs</div>
                        <div class="status-values"><?php //echo $totallabPerformance; ?></div>
                    </div>
                    <div class="status-count">
                        <div class="status-count-label quiz-lable">Quiz</div>
                        <div class="status-values"><?php //echo $totalquizPerformance; ?></div>
                    </div>

                </div><!-- end of status box -->

                    <div style="border-bottom: 1px solid #e3e3e3; margin-bottom: 15px; padding-bottom: 8px; padding-right: 0;
    text-align: center;" class="col-md-12">

                            <span style="text-align: center;font-size: 16px;color:#000">Profile Information</span>

                    </div>



                    <div class="student-profile-div col-md-12" style=" margin-bottom: 25px;">
                        <table class="my-profile-table" style="width: 100%;">
                            <tbody>

                            <tr>
                                <td class="title" style="font-family: &quot;Montserrat&quot;;">First Name</td>
                                <td>
                                    <div class="input-group">
                                        <?php echo $userobj->firstname ?>
                                    </div><!-- /input-group -->
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Last Name</td>
                                <td>

                                    <div class="input-group">

                                        <?php echo $userobj->lastname ?>
                                    </div><!-- /input-group -->
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Email</td>
                                <td>
                                    <div class="input-group">
                                        <?php echo $userobj->email ?>
                                    </div><!-- /input-group -->
                                </td>
                            </tr>


                            </tbody>
                        </table>
                    </div>






            </div><!-- end of d -->

        </div>
    </div>
</div>
<section id="footer-bottom">
    <div class="container">
        <div class="footer-inner">
            <div class="copyright">Copyright © 2016 TeleUniv Solutions Pvt. Ltd. All Rights Reserved.</div><!-- /.copyright -->
        </div><!-- /.footer-inner -->
    </div><!-- /.container -->
</section>
<?php
// echo $OUTPUT->footer();
?>

</body>

<?php

$latestjson= getLastTwoWeekPerformanceJson($userid,$currentProgramId);

?>
</html>
<div id="popup2" class="overlay">
    <div class="popup">
        <a class="close">×</a>
        <div class="content" style="width: 85%;margin: auto;max-height: 500px; padding: 1%; font-size: 14px;">
            <center>
                 <span id="sms-loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw" style=" color: #ea6645 "></i>
                 </span>
            </center>
            <div id="smsnotification"></div>
            <form role="form" method="POST" class="clearfix" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="smsform" name="createuser">
                To  <input type="text" name="numbers" id="numbers" required/><br/>
                Message
                <div style="width: 100%;"><textarea style="width: 100%; height: 150px;" id="area2" name="area2" cols="92" rows="12" required></textarea>
                    <div id="charNum">140</div>
                </div>
                <br/>
                <button type="button"  class="btn pull-right" id="sendmessage">Send SMS</button>

            </form>
        </div>
    </div>
</div>



<div id="popup1" class="overlay">
    <div class="popup">
        <a class="close">×</a>
        <div class="content" style="width: 85%;margin: auto;max-height: 500px; padding: 1%; font-size: 14px;">
            <center>
                 <span id="email-loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw" style=" color: #ea6645 "></i>
                 </span>
            </center>
            <div id="notification"></div>
            <form role="form" method="POST" class="clearfix" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="createuser" name="createuser">
                To  <input type="text" name="mailids" id="mailids" required />
                Subject <input type="text" id="subject" name="subject" required /><br/>

                Message
                <div style="width: 100%;"><textarea style="width: 100%; height: 150px;" id="area1" name="area1" cols="92" rows="12" required></textarea></div>
                <br/>
                <button type="button"  class="btn pull-right" id="sendemail">Send Mail</button>

            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/teacher/testcenter/js/nicEdit-latest.js"></script> <script type="text/javascript">
    //<![CDATA[
    bkLib.onDomLoaded(function() {
        new nicEditor({maxHeight : 260}).panelInstance('area1');

    });
    //]]>
</script>
<script src="<?php echo $CFG->wwwroot; ?>/teacher/jquery.table2excel.js"></script>

<script src="<?php echo $CFG->wwwroot; ?>/teacher/testcenter/js/bootstrap.min.js"></script>
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/d3-v3.min.js" charset="utf-8"></script>
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/c3.js"></script>
<script>


    var chart = c3.generate({
        bindto: '#chart',
        data: {
            x : 'x',
            columns: [

                /* ['x','C' ,'Data Structures','MySQL','Java','Design And Analysis of Algorithms'],
                 ['student meangrade',50,30,0,33.33,0],
                 ['class meangrade',0.55,0.42,0,0.33,0.26],*/
                <?php echo $subjectGrades; ?>
            ],
            type: 'bar'

        },
        axis: {
            x: {
                type: 'category', // this needed to load string x value
                label: {
                    text: 'Subject',
                    position: 'outer-middle'

                }
            },
            y:{
                max:100,
		min:10,
                label: {
                    text: 'Average Mean Grade',
                    position: 'outer-middle'

                }
            }
        }
    });

    chart.resize({
        height: 350,
        width: 750
    });



    var latestperformance = c3.generate({
        bindto: '#latestperformance',
        data: {
            type: 'line',
            columns: [
                <?php echo $latestjson['grade']; ?>
                //['data1', 30, 200, 100, 400, 150, 250]
            ]
        },
        axis: {
            x: {
                type: 'category',
                categories:  <?php echo $latestjson['dates']; ?>,//['2013-01-01', '2013-01-02', '2013-01-03', '2013-01-06', '2013-01-07', '2013-01-08']
                label: {
                    text: 'Date',
                    position: 'outer-middle'

                }
            },
            y:{
                max:100,
min:10,
                label: {
                    text: 'Average Mean Grade',
                    position: 'outer-middle'

                }
            }

        }
    });

    latestperformance.resize({
        height: 350,
        width: 750
    });


    var baseUrl='<?php echo $CFG->baseUrl ?>';
    var student='<?php echo $_GET['sid'] ?>';








    $j("#subjects-dropdown").on("change",function(){

        var subjectid=$j(this).val();
        var reports=getResults(subjectid,student);

    });
    $j(".course-list-table").tablesorter();

    function getResults(subject,student){
        $j.ajax({
            type: "GET",
            dataType: 'text',
            data: {
                "trmid": 8,
                "student": student,
                "trcid":subject
            },
            url: baseUrl + "teacher/myreports_ajax.php",
            success: function (data) {
                $j("#activities-table").html(data);

            },
            complete: function (xhr, status) {
                // $j("#courseTable").tablesorter();

                $j(".course-list-table").trigger("update");
                // set sorting column and direction, this will sort on the first and third column

                $j(".course-list-table").trigger([]);

                var $rows = $j('.course-list-table tbody tr');

                $j('.search').keyup(function() {
                    var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

                    $rows.show().filter(function() {
                        var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
                        return !~text.indexOf(val);
                    }).hide();
                });
            }
        });
    }//end of getResults


    $j("#sendmail").on("click",function(){
        $j("#notification").html("");
        $j("#createuser").show();
        $j("#mailids").val(' <?php echo $userobj->email ?>');
        $j("#popup1").addClass("overlay1");
        //alert(selected);

    });


    $("#area2").keyup(function(){
        el = $(this);
        if(el.val().length >= 140){
            el.val( el.val().substr(0, 140) );
        } else {
            $("#charNum").text(140-el.val().length);
        }
    });

    $j("#sms-loading").hide();

    $j(".close").on("click",function(){
        $j("#popup1").removeClass("overlay1");
        $j("#popup1").addClass("overlay");
        $j("#popup2").removeClass("overlay1");
        $j("#popup2").addClass("overlay");
        $j("#smsform").hide();
    });

    $j("#sendemail").on("click",function(){
        var mailids=$j("#mailids").val();
        var subject=$j("#subject").val();
        var message=$j(".nicEdit-main").html();
        if (mailids.length == 0) {
            $j("#mailids").focus();
        }
        else {
            sendMail(mailids, subject, message);
        }
    });

    $j("#sendsms").on("click",function(){
        $j("#smsform").show();

        $j("#smsnotification").html("");
        $j("#numbers").val('<?php echo $userobj->profile['mobile'] ?>');
        $j("#popup2").addClass("overlay1");
        //alert(selected);

    });


    $j("#sendmessage").on("click",function(){
        var numbers=$j("#numbers").val();
        var mess=$j("#area2").val();
        if (numbers.length == 0) {
            $j("#numbers").focus();
        }
        else {
            sendSms(numbers,mess);
        }
    });



    function sendMail(mailids,subject,message){
        $j("#email-loading").show();
        $j.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "trmid": 8,
                "mailids": mailids,
                "mailsubject":subject,
                "message":message
            },
            url: baseUrl + "supervisor/myreports_ajax.php",
            success: function (data) {

                $j("#notification").html(data);
                $j("#createuser").hide();
                $j("#email-loading").hide();
                $j("#mailids").val('');
                $j("#subject").val('');
                $j(".nicEdit-main").html('');
            }
        });
    }//end of sendMail


    function sendSms(numbers,mess){
        $j("#sms-loading").show();

        $j.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "mid":7 ,
                "numbers": numbers,
                "message":mess
            },
            url: baseUrl + "portal/courseutils.php",

            success: function (data) {
                $j("#smsnotification").html(data);
                $j("#sms-loading").hide();
                $j("#smsform").hide();
                $j("#numbers").val('');
                $j("#area2").val('');


            }
        });
    }//end of sendSms


    $j(".graph").on("click",function(){
        $j(".latest-performance").hide();
        $j(".graph").hide();
        $j(".latestsearch").hide();
        $j(".download").hide();
        //$(".xls").hide();
        $j(".latesttable").show();
        // $(".png").show();
        $j(".latest-performance-graph").show();
    });
    $j(".latesttable").on("click",function(){
        $j(".latest-performance-graph").hide();
        $j(".latesttable").hide();
        // $(".png").hide();
        $j(".latestsearch").show();
        $j(".download").show();
        $j(".graph").show();
        // $(".xls").show();
        $j(".latest-performance").show();
    });



    /* tabs script start here */


    /*$("#filterdiv > span").click(function(){

     alert($(this).text());
     });*/

    /* tabs script start here */

    var $rows = $j('.course-list-table tbody tr');

    $j('.search').keyup(function() {
        var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

        $rows.show().filter(function() {
            var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
    });



    //export table functionality

    var d = new Date();
    var dat = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();

    $j("#twoxls").click(function(){
        $j("#myTable").table2excel({
            // exclude CSS class
            exclude: ".noExl",
            name: "Table2Excel",
            filename: dat+" Last Two Weeks Performance" //do not include extension
        });
    });
    $j("#sxls").click(function(){
        $j("#subjectTable").table2excel({
            // exclude CSS class
            exclude: ".noExl",
            name: "Table2Excel",
            filename: dat+" Subject-wise Class Performance" //do not include extension
        });
    });

    $j('.course-dropdown').on('change', function () {
        var curl=(window.location.href).split('&');
        var url = curl[0]+'&cid='+$j(this).val(); // get selected value
        if (url) { // require a URL
            //alert(url);
            window.location = url; // redirect
        }
        return false;
    });

</script>

