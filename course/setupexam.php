<?php

require_once('../config.php');
require_once('lib.php');

$cid = optional_param('id', 0, PARAM_INT); // Course id.
if(isset($_POST['cid'])){
    $cid = $_POST["cid"];
}
//var_dump($id);
//exit(0);
$PAGE->set_pagelayout('admin');
if ($cid) {
    $pageparams = array('id' => $cid);
} 

$PAGE->set_url('/course/setupexam.php', $pageparams);
if ($cid) {
    // Editing course.
    if ($cid == SITEID){
        // Don't allow editing of  'site course' using this from.
        print_error('cannoteditsiteform');
    }
    // Login to the course and retrieve also all fields defined by course format.
    $course = get_course($cid);
    require_login($course);
    if (user_has_role_assignment($USER->id, 5) || user_has_role_assignment($USER->id, 3) ){
        redirect($CFG->wwwroot);
       
    }
}
else {
    require_login();
    // require_capability();
    if (user_has_role_assignment($USER->id, 5) || user_has_role_assignment($USER->id, 3) ){
        redirect($CFG->wwwroot);
       
    }
    print_error('needcoursecategroyid');
}

$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
echo "<h3>Setup Exam : {$course->fullname} </h3>";
//echo " <a style='margin:1px !important;float:right'  class='btn' target='_blank' href='updatecfg.php?id={$id}'>Mark this as current exam</a>";
$context = context_course::instance($cid);
$students = get_role_users(5 , $context);//getting all the students from a course level
$totalenrolledStudents=count($students);
$teachers = get_role_users(3 , $context);//getting all the teachers from a course level
$totalenrolledteachers=count($teachers);
echo "<h4>Enrolled Students : {$totalenrolledStudents}, Enrolled Teachers : {$totalenrolledteachers}</h4>";
echo "<h6 style='color:orange !important'>NOTE : Please make sure all the students are enrolled before setting exam</h6>";

$ExamPCField=$DB->get_field('user_info_field', 'id', array('shortname'=>'exampc'));
$roomNoField=$DB->get_field('user_info_field', 'id', array('shortname'=>'roomno'));
$ExamIPField=$DB->get_field('user_info_field', 'id', array('shortname'=>'examip'));
function getStudentData($userid,$fieldid){
    global $DB;
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;
    return $studata;
}
function updateStudentData($userid,$fieldid,$value){
    global $DB;
    if(!empty($value) && !is_null($value)){
        $sql="update `mdl_user_info_data` set `data`='".$value."' WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
        //echo $sql;
        $result=$DB->execute($sql,null);
    }else{
        //echo $value."  ----value";
    }
    
}

 
function generate_string($input, $strength = 16) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}
function getStudentROOM($userid,$fieldid){
    global $DB;
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;
    //var_dump($studata);
    return $studata;
}
function updateExamCourseID($cid){
    global $DB;
    $query = "update mdl_config set value={$cid} WHERE name='examcourseid'";
    $result=$DB->execute($query,null);
    return $result;
}
function updateExamCourseFlag($cid){
    global $DB;
    $query = "update mdl_course set examFlag=1 WHERE id={$cid}";
    $result=$DB->execute($query,null);
    return $result;
}
function randomPWD($userid){
    global $DB;
    $permitted_chars = '123456789';
    $newpasswordTXT = generate_string($permitted_chars, 8);
    $password = MD5($newpasswordTXT);
    $query = "update mdl_user set p1='', p2='".$newpasswordTXT."',password='".$password."' WHERE id={$userid}";
    //echo $query;
    $result=$DB->execute($query,null);

    return $result;
}
function checkExamStarted($cid){
    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 and activityid in (SELECT id FROM `mdl_course_modules` WHERE `course` = ".$cid." AND `module` IN($vpl,$quiz))";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    return (count($startedActivitiesRes)>0);
}
//updateExamCourseID(6);
//echo " CFG->examcourseid ".$CFG->examcourseid;
//echo $DB->get_field('config', 'value', array('name'=>'examcourseid'));

if (!empty($_POST)){
    if(isset($_POST["cid"]) ){
        if($DB->get_field('course', 'examFlag', array('id'=>$_POST["cid"]))==1){

            //nothing to do
            echo "<p style='color:green !important'>Exam setup is ready for this course</p>";
            echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printstudentlist.php?id={$cid}'>Seating Arrangement</a>";
            
            if(!checkExamStarted($cid)){
                echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printexamslips.php?id={$cid}'>Exam Slips</a>";
            }else{
                echo "<p><br/>Exam slips cannot be shown now , exam is in progress.</p>";
            }

        }else{

            $stuExamPcs = array();
            $onlystuPcs = array();

            foreach($students as $student){
                if($student->id){
                    //if(!getStudentData($student->id,$ExamPCField))
                    $stuExamPcs[] = getStudentData($student->id,$ExamPCField)."$".getStudentROOM($student->id,$roomNoField);
                    $onlystuPcs[] = getStudentData($student->id,$ExamPCField);
                }
            }
            $uniqueExamPcs = array_count_values($onlystuPcs);
            //var_dump($uniqueExamPcs);
            $duplicateFlag = true;
            foreach ($uniqueExamPcs as $key => $value) {
                if($value>1){
                    $exampc = explode("$",$key)[0];
                    if(strlen($exampc)>0){
                        $duplicateFlag = false;
                        echo '<p style="color:red !important;font-weight:bold">'.$exampc." is duplicate Exam PC, no.of times repeated :".$value."</p><br/>";
                    }
                }
            }
            
            if((count($stuExamPcs)==count($students))&&($duplicateFlag)){

                //updateExamCourseID($_POST["cid"]);
		updateExamCourseFlag($_POST["cid"]);
                //var_dump($stuExamPcs);
                //var_dump("==========================");
                shuffle($stuExamPcs);
                $examPcCount=0;
                //var_dump($stuExamPcs);
               
                foreach($students as $student){
                    if($student->id){
                        randomPWD($student->id);
                        $exampc = explode("$",$stuExamPcs[$examPcCount])[0];
                        $roomno = explode("$",$stuExamPcs[$examPcCount])[1];
                        updateStudentData($student->id,$ExamPCField,$exampc); 
                        updateStudentData($student->id,$roomNoField,$roomno); 
                        updateStudentData($student->id,$ExamIPField,"NA"); 
                        $examPcCount++;
                    }
                }
                echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printstudentlist.php?id={$cid}'>Seating Arrangement</a>";
                if(!checkExamStarted($cid)){
                    echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printexamslips.php?id={$cid}'>Exam Slips</a>";
                }else{
                    echo "<p><br/>Exam slips cannot be shown now, exam is in progress..</p>";
                }
            }//end of ipcount student count check
            else{
                echo "<p style='color:red !important'>No. of Exam PCs doesn't match. Exam can't setup for this course.</p>";
            }
        }//end of examFlag and cid check
    }//end of post cid check
}else{
//    $examcouseid = $DB->get_field('config', 'value', array('name'=>'examcourseid'));
    if($DB->get_field('course', 'examFlag', array('id'=>$cid))==1){
        //nothing to do
        echo "<p style='color:green !important'>Exam setup is ready for this course</p>";
        echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printstudentlist.php?id={$cid}'>Seating Arrangement</a>";
        if(!checkExamStarted($cid)){
            echo " <a style='margin:1px !important'  class='btn btn-primary' target='_blank' href='printexamslips.php?id={$cid}'>Exam Slips</a>";
        }else{
            echo "<p><br/>Exam slips cannot be shown now, exam is in progress... </p>";
        }

    }else if ($totalenrolledStudents==0){
        echo "<p style='color:red !important;font-weight: bold;'>Enrolled Students : {$totalenrolledStudents} , Exam can't setup for this course.</p>";
    }
    
    /*else if($examcouseid>0){
        $currentexamname = $DB->get_field('course', 'fullname', array('id'=>$examcouseid));
        echo "<p style='color:red !important;font-weight: bold;'>Previous Exam Course : {$currentexamname} , 
        <br/>if you setup exam on current course($course->fullname), previous exam settings will be deleted.</p>";
        echo '
        <form action="'.$_SERVER['PHP_SELF'].'" method="post">
            <input type="hidden" name="cid" value="'.$id.'">
            <input type="hidden" name="confirm" value="1">
            <button style="margin:1px !important"  class="btn btn-primary">setup exam</button>
        </form>';
    }*/
    else{
        echo '
        <form action="'.$_SERVER['PHP_SELF'].'" method="post">
            <input type="hidden" name="cid" value="'.$cid.'">
            <input type="hidden" name="confirm" value="1">
            <button style="margin:1px !important"  class="btn btn-primary">setup exam</button>
        </form>';
    }
}




echo $OUTPUT->footer();