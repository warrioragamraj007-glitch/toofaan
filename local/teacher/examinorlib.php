<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 16/5/16
 * Time: 1:46 PM
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
// require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');
require_once($CFG->dirroot.'/local/teacher/reports/reports_db.php');
include_once("course-timings.php");

require_login();
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}
$trmid =  $_GET['trmid'];
$courseid=$_GET['trcid'];
$categoryid=$_GET['trcatid'];
$topicid=$_GET['trtopicid'];
$catname=$_GET['catname'];
$topicname=$_GET['topicname'];
$subjectname=$_GET['subjectname'];
$grade=$_GET['grade'];
$attendance=$_GET['attendance'];
$fname=$_GET['firstname'];
$lname=$_GET['lastname'];
$student=$_GET['student'];
$stdsection=$_GET['tsecid'];
$topicname=$_GET['ttopicname'];
$typeflag=$_GET['type'];
$rollno=$_GET['rollno'];

$fromdate=$_GET['fromdate'];
$todate=$_GET['todate'];
$username=$_GET['username'];
$reasonMsg=$_GET['reason'];
$newIP=$_GET['newip'];
//non-editing teacher block, examinor
if (!user_has_role_assignment($USER->id, 3)){

    $url = new moodle_url('/');
    echo redirect($url);
}
$ExamPCField=$DB->get_field('user_info_field', 'id', array('shortname'=>'exampc'));

switch($trmid){

    case 1: echo getEnrolledStudents($courseid,$stdsection);break;
    case 2: echo showStudentDetails($username,$courseid,$ExamPCField); break;
    case 3: echo storeReason($username,$courseid,$reasonMsg,$newIP,$ExamPCField); break;

}


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

function storeReason($username,$courseid,$reasonMsg,$newIP,$ExamPCField){
    global $DB;
    $userid = $DB->get_field('user', 'id', array('username'=>$username));
    // var_dump($userid);
    // exit(0);
	$ExamIPField=$DB->get_field('user_info_field', 'id', array('shortname'=>'examip'));
    $reason = new stdClass();
    $reason->username   =$username;
    $reason->cid     = $courseid;
    $reason->reason=$reasonMsg;
    $reason->oldIP     = getStudentData($userid,$ExamPCField);
    if(strlen($newIP)>0){
        $reason->currentIP = $newIP;
        updateStudentData($userid,$ExamIPField,"NA"); 
        updateStudentData($userid,$ExamPCField,$newIP); 
    }
    
    // var_dump($reason);
    // var_dump($DB->get_field('exam', 'id', array('username' => $username,'cid' => $courseid)));
    

    try {
        $reasonid = $DB->get_field('exam', 'id', array('username' => $username,'cid' => $courseid));
        // var_dump($reasonid);
        // exit(0);
        if($reasonid){
            $ntimes = $DB->get_field('exam', 'ntimes', array('username' => $username,'cid' => $courseid));
            $reason->id=$reasonid;
            $reason->ntimes=$ntimes+1;
            //if($ntimes>=2){
            //    return "<p style='color:orange;font-size: 14px;'>you cannot update more than twice</p>";
            //}else{
                $DB->update_record_raw('exam', $reason, false);
            //}
            
        }else{
            //echo "insert ";
            $DB->insert_record_raw('exam', $reason, false);
        }
        return "<p style='color:green;font-size: 16px;'>Reason updated successfully. the current password is <b>".getP2($username)."</b></p>";
    } catch (dml_write_exception $e) {
        // During a race condition we can fail to find the data, then it appears.
        // If we still can't find it, rethrow the exception.
        $reasonid=$DB->get_field('exam', 'id', array('username' => $username,'cid' => $courseid));
        if ($reasonid === false) {
            //throw $e;
            return "<p style='color:orange;font-size: 14px;'>reason not updated, please try again</p>";
        }

    }    
}

function getP2($username){
    global $DB;
    return $DB->get_field('user', 'p2', array('username'=>$username));
}

function showStudentDetails($username,$cid,$ExamPCField){
    global $DB;
    $password = getP2($username);
    $userid = $DB->get_field('user', 'id', array('username'=>$username));
    $reasonid=$DB->get_field('exam', 'id', array('username' => $username,'cid' => $cid));
    $IP = getStudentData($userid,$ExamPCField);
    $html='';
    if($reasonid){
        $html=$html."  <table class='pschool'>
        <tbody>
        <tr><td>Username</td><td style='padding-left: 25px;'>".$username."</td></tr>
        <tr><td>PC NO </td><td style='padding-left: 25px;'>".$IP."</td></tr>
         <tr><td>New Password</td><td style='padding-left: 25px;'>".$password."</td></tr>
        </tbody>
      </table>";
    }else{
        $html='<p style="color:orange;font-size: 14px;">Please update the reason first</p>';
    }
    
    return $html;
}

function getEnrolledStudents($courseid,$stdsection){
    global $DB;
    $html='';
    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));

    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentArray=array();
    $att=0;

    foreach($students as $student) {
        $rollno=getStudentData($student->id,$rollfield);
        $stu_section=getStudentData($student->id,$sectionfield);
        //echo $stu_section;
        if(($stdsection==$stu_section)||(is_number($stdsection))){
                $html.='<tr>
                            <td>'.$rollno.'</td>
                            <td class="stdname">'.$student->firstname.'</td>
                            <td>'.$stu_section.'</td>
                            <td><button data-name="reason"  class="reason " id="'.$student->username.'" >update</button></td>
                            <td><button data-name="showdetails" class="showdetails" id="'.$student->username.'"  >showDetails</button></td>
                            </tr>';
        }//end of section check

    }//end of student object
    //var_dump($studentArray);
    if(!$html){
        $html.='<tr><td colspan="5">No Records Found</td></tr>';
    }
    return $html;

}


?>