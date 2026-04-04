<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 16/5/16
 * Time: 1:46 PM
 */
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');
require_once($CFG->dirroot.'/teacher/reports/reports_db.php');
require_once($CFG->dirroot . '/PHPMailer-master/PHPMailerAutoload.php');

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
$email=$_GET['email'];
$student=$_GET['student'];

$mailids=$_GET['mailids'];
$mailsubject=$_GET['mailsubject'];
$message=$_GET['message'];

$attendance=$_GET['attandance'];
$sdate=$_GET['sdate'];
$grade=$_GET['grade'];
$avgattandance=$_GET['avgattandance'];

switch($trmid){

    case 1: getCategories();break;
    case 2: echo getCoursesByEachCategory($categoryid);break;
    case 3: echo getCoursesByCateogory($categoryid);break;
    case 4: echo getStudentsByCourse($courseid,$categoryid,$fname,$lname,$email);break;
    case 5: echo getStudentInfo($student,$categoryid);break;
    case 6: echo getStudnetPerformance($student,$categoryid);break;
    case 7: echo getSubjectsGrades($student,$categoryid);break;
    case 8: echo sendEmail($mailids,$mailsubject,$message);break;
    case 9: echo getStudentsByCourseByAttandance($courseid,$categoryid,$attendance,$sdate,$student,$avgattandance);break;
    case 10: echo getStudentsByCourseByGrade($courseid,$categoryid,$grade,$student);break;


}


function sendEmail($mailids,$mailsubject,$message){

    $teleadmin='TELEUNIV ADMIN';

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
    //Set the hostname of the mail server
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'tls';
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication - use full email address for gmail



            $mailid = "teleuniv-admin@teleparadigm.com";
            $password ="tele123$";




    $mail->Username = $mailid;
    //Password to use for SMTP authentication
    $mail->Password = $password;
    //Set who the message is to be sent from
    $mail->setFrom('tessellator@gmail.com',"Tele Univ");
    //Set an alternative reply-to address
    $mail->addReplyTo('replyto@example.com', 'TeleUniv');
    //Set who the message is to be sent to
    $mail->addAddress($mailid, '');//self mail

    //Set the subject line
    $arr=explode(",",$mailids);
    foreach($arr as &$value){
        $mail->addBCC($value, ''); // send bulk mails via bcc
    }


    $mail->Subject = $mailsubject;
    $mail->msgHTML($message);
    $mail->AltBody = 'Update';
    if (!$mail->send()) {
        return "<center><h3>Failed To Send Email</h3></center>";

    } else {
        return "<center><h3>Email(s) sent SuccessFully</h3></center>";

    }
}

function getDayFormat($timestamp){

    $day = date( "D",$timestamp).', ';
    $date = date( "d", $timestamp).' ';
    $month = date( "M", $timestamp).' ';
    $year = date( "o", $timestamp);

    return $day.$date.$month.$year;
}

function getCategories(){
    global $USER,$DB;

    $getCategoriessql="SELECT `id` , `name` FROM `mdl_course_categories` ";

    $resultset=$DB->get_records_sql($getCategoriessql, null);
    foreach ($resultset as $res) {
        $category_typeids[]=array("catid"=>$res->id,"catname"=>$res->name);
    }
//var_dump($category_typeids[0]["catid"]);
    return $category_typeids;
}

function getCoursesByEachCategory($category){
    global $DB;
    $html='';
    $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$category";

    $resultset=$DB->get_records_sql($getCoursesql, null);
    foreach ($resultset as $res) {
        $course_typeids[]=array("cid"=>$res->id,"cname"=>$res->fullname);
        $courseid=$res->id;
        $coursename=$res->fullname;
        $classmeangrade=ClassMeanGrade($courseid);
        $classmeanattandance=ClassMeanAttandance($courseid);
        $context = context_course::instance($courseid);
        $teachers = get_role_users(3 , $context);//getting all the teachers from a course level
        $assistants = get_role_users(4 , $context);//getting all the assistants from a course level
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $teachername='';$count=0;
        $assistantname='';$acount=0;
        foreach($teachers as $teacher){
            if($count==0){
                $teachername.=$teacher->firstname.' '.$teacher->lastname;
            }else{
                $teachername.=','.$teacher->firstname.' '.$teacher->lastname;
            }
            $count++;
        }
        foreach($assistants as $assistant){
            if($acount==0){
                $assistantname.=$assistant->firstname.' '.$assistant->lastname;
            }else{
                $assistantname.=','.$assistant->firstname.' '.$assistant->lastname;
            }
            $acount++;
        }
        $html.= '<tr>

                                    <td>'.$coursename.'</td>
                                    <td>'.$teachername.'</td>
                                    <td class="assistant" title="'.$assistantname.'">'.count($assistants).'</td>
                                    <td>'.($classmeanattandance['classmeanattandance']*100).'</td>
                                    <td>'.$classmeangrade['classmeangrade'].'</td>
                                    <td data-scid='.$courseid.' data-scatid='.$category.' class="cstudent" >'.count($students).'</td>
                                    </tr>';
    }

    return $html;
}

function getStudentEnrolledCategories($userid){
    global $DB;
    $allEnrolledCourses = enrol_get_users_courses($userid);

    $enrolledCategories=array();

    $allEnrolledCourses=array_values($allEnrolledCourses);
    for($i=0;$i<count($allEnrolledCourses);$i++){
        $enrolledCategories[]=$allEnrolledCourses[$i]->category;
    }

    $enrolledCategories=array_unique($enrolledCategories);

    $getCategoriessql="SELECT `id` , `name` FROM `mdl_course_categories` WHERE `id` IN (".implode(',',$enrolledCategories).")";

    $resultset=$DB->get_records_sql($getCategoriessql, null);
    foreach ($resultset as $res) {
        $category_typeids[]=array("catid"=>$res->id,"catname"=>$res->name);
    }
//var_dump($category_typeids[0]["catid"]);
    return $category_typeids;
}


function getCoursesByCateogory($categoryid){
    global $DB;
        $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$categoryid";

        $resultset=$DB->get_records_sql($getCoursesql, null);
    $html="<option value='0'>All</option>";
if($categoryid){

    foreach ($resultset as $res) {
        $html=$html."<option value='".$res->id."'>".$res->fullname;
    }
}
    else{
        $html="<option value=0>Select Subject";
    }

    echo $html;
}


function getStudentsByCourse($courseid,$catid,$fname,$lname,$email){
global $DB,$CFG;
    $html='';

    if($courseid){
        $context = context_course::instance($courseid);
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $stdcnt=1;
        foreach($students as $student){
            $displayFlag1=$displayFlag2=$displayFlag3=1;
            if($fname){
                if (stripos($student->firstname,$fname) !== false) {
                    $displayFlag1=1;
                }else{
                    $displayFlag1=0;
                }
            }
            if($lname){
                if (stripos($student->lastname,$lname) !== false) {
                    $displayFlag2=1;
                }else{
                    $displayFlag2=0;
                }
            }
           if($email){
               if (stripos($student->email,$email) !== false) {
                   $displayFlag3=1;
               }else{
                   $displayFlag3=0;
               }
           }
            if($displayFlag1&&$displayFlag2&&$displayFlag3){
                $html.='<tr  class="student" data-id="'.$student->id.'">
                 <td><input data-email="'.$student->email.'" data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'" class="student-'.$student->id.'"  type="checkbox" name="selectstudents"> </td>
                <td><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a>
                </td>
                <td>'.$student->email.'</td>
                
                </tr>';
                $stdcnt++;
            }


        }
    }else{
        $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$catid";
        $count=0;
        $resultset=$DB->get_records_sql($getCoursesql, null);
        foreach ($resultset as $res) {
            //repeat the process for each courseid to get students
            if($count==0) {
                $context = context_course::instance($res->id);
                $students = get_role_users(5, $context);//getting all the students from a course level
                $stdcnt=1;
                foreach ($students as $student) {
                    $displayFlag1=$displayFlag2=$displayFlag3=1;

                    if($fname){
                        if (stripos($student->firstname,$fname) !== false) {
                            $displayFlag1=1;
                        }else{
                            $displayFlag1=0;
                        }
                    }
                    if($lname){
                        if (stripos($student->lastname,$lname) !== false) {
                            $displayFlag2=1;
                        }else{
                            $displayFlag2=0;
                        }
                    }
                    if($email){
                        if (stripos($student->email,$email) !== false) {
                            $displayFlag3=1;
                        }else{
                            $displayFlag3=0;
                        }
                    }
                    if($displayFlag1&&$displayFlag2&&$displayFlag3){
                        $html.='<tr  class="student" data-id="'.$student->id.'">
                        <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'" data-email="'.$student->email.'" class="student-'.$student->id.'"  type="checkbox" name="selectstudents"> </td>
                        <td><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a>
                        </td><td>'.$student->email.'</td>
                       
                        </tr>';
                        $stdcnt++;
                    }
                }
                $count++;
            }
        }
    }

    return $html;
}



function getStudentsByCourseByAttandance($courseid,$catid,$attandance,$date,$studentname,$avgattandance){
    global $DB,$CFG;

    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $html='';
    $displayFlag1=$displayFlag2=1;


    if($attandance==3){

        $html= getStudentsByCourseByAverageAtandance($courseid,$catid,$avgattandance,$studentname);
    }
    else if($courseid){

/* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/



        $beginOfDay = strtotime("midnight", strtotime($date));
        $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

        $sql="SELECT cm.id,q.name as quizname,v.name as labname ,cm.instance,cm.module,cm.completionexpected
    FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
    WHERE cm.course = '".$courseid."' AND  cm.completionexpected between '".$beginOfDay."' AND '".$endOfDay."' AND cm.module IN($vpl,$quiz)";

        $res1=$DB->get_records_sql($sql);

//checking for current activities attempted grades
        $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE `status` IN (1,0) AND (`activity_start_time` between '".$beginOfDay."' AND '".$endOfDay."' OR `activity_stop_time` between '".$beginOfDay."' AND '".$endOfDay."')";
//echo $startedActivitiesSql;
        $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
        $startedActivityIds=array();
        if(count($startedActivitiesRes)){
        foreach ($startedActivitiesRes as $item )
        {
            $startedActivityIds[]= $item->activityid;
        }
        }
        if(count($startedActivityIds)) {
            $rsql = "SELECT cm.id,q.name as quizname,v.name as labname,cm.instance,cm.module,cm.completionexpected
    	FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    	left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
	WHERE  cm.id IN (" . implode(',', $startedActivityIds) . ") AND cm.course = '" . $courseid . "' AND cm.module IN($vpl,$quiz) ";
            $res2=$DB->get_records_sql($rsql);
        }

        //$sres=$DB->get_records_sql($rsql);
        // $attandance=getGrade($courseid,$module,$instance,$studentId);


        /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE END*/


        $context = context_course::instance($courseid);
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $stdcnt=1;
        foreach($students as $student){
            $displayFlag=0;
            if($studentname){
                if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                    $studetnFlag=1;
                }else{
                    $studetnFlag=0;
                }
            }else{
                $studetnFlag=1;
            }
            if(count($res1)) {
                foreach ($res1 as $item) {
                    $module = $item->module;
                    $instance = $item->instance;
                    $sql_item = "SELECT name FROM mdl_modules WHERE id ='" . $module . "'";
                    $item_res = $DB->get_record_sql($sql_item);
                    $itemname = $item_res->name; //echo  $itemname;
                    $grading_info = grade_get_grades($courseid, 'mod', $itemname, $instance, $student->id);
                    $item = $grading_info->items[0];
                    $gradeI = $item->grades[$student->id];
                    $grade = $gradeI->grade;
                    if ($grade) {
                        $displayFlag = 1;
                    }
                }
            }
            if(count($res2)){
            foreach ($res2 as $item )
            {
                $module=$item->module;
                $instance=$item->instance;
                $sql_item="SELECT name FROM mdl_modules WHERE id ='".$module."'";
                $item_res=$DB->get_record_sql($sql_item);
                $itemname= $item_res->name; //echo  $itemname;
                $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $student->id);
                $item = $grading_info->items[0];
                $gradeI= $item->grades[$student->id];
                $grade = $gradeI->grade ;
                if($grade){
                    $displayFlag=1;
                }
            }}

//echo $attandance;
           // echo $displayFlag;

            if($studetnFlag){
            if($displayFlag){
                if($attandance==1||$attandance==2){
                    $html.='<tr  class="student" data-id="'.$student->id.'">
                   <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="attandancestudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>PRESENT</td>
                 
                </tr>';
                    $stdcnt++;
                }
            }else{
                    if(($attandance==0||$attandance==2)&&(count($res1)||(count($res2)))){
                        $html.='<tr  class="student" data-id="'.$student->id.'">
                     <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="attandancestudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>ABSENT</td>
                 
                </tr>';
                        $stdcnt++;
                    }

                }


            }//check student flag

        }//END OF STDUENT LOOP
    }else{
        $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$catid";
        $count=0;
        $resultset=$DB->get_records_sql($getCoursesql, null);
        foreach ($resultset as $res) {
            //repeat the process for each courseid to get students
            if($res) {
                $context = context_course::instance($res->id);
                $students = get_role_users(5, $context);//getting all the students from a course level
                $courseid=$res->id;
                /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/
                $displayFlag=0;

                $beginOfDay = strtotime("midnight", strtotime($date));
                $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

                $sql="SELECT cm.id,q.name as quizname,v.name as labname ,cm.instance,cm.module,cm.completionexpected
                    FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
                    left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
                    WHERE cm.course = '".$courseid."' AND  cm.completionexpected between '".$beginOfDay."' AND '".$endOfDay."' AND cm.module IN($vpl,$quiz)";

                $res1=$DB->get_records_sql($sql);

                //checking for current activities attempted grades
                $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE `status` IN (1,0) AND (`activity_start_time` between '".$beginOfDay."' AND '".$endOfDay."' OR `activity_stop_time` between '".$beginOfDay."' AND '".$endOfDay."')";

                $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
                $startedActivityIds=array();
                if(count($startedActivitiesRes)){
                foreach ($startedActivitiesRes as $item )
                {
                    $startedActivityIds[]= $item->activityid;
                }
                }

                if(count($startedActivityIds)) {
                    $rsql = "SELECT cm.id,q.name as quizname,v.name as labname,cm.instance,cm.module,cm.completionexpected
                    FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
                    left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
                    WHERE  cm.id IN (" . implode(',', $startedActivityIds) . ") AND cm.course = '" . $courseid . "' AND cm.module IN($vpl,$quiz)";
                    $res2=$DB->get_records_sql($rsql);
                }
                //$sres=$DB->get_records_sql($rsql);
                // $attandance=getGrade($courseid,$module,$instance,$studentId);


                /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE END*/

                $stdcnt=1;
                foreach ($students as $student) {
                    $displayFlag = 0;
                    if($studentname){
                        if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                            $studetnFlag=1;
                        }else{
                            $studetnFlag=0;
                        }
                    }else{
                        $studetnFlag=1;
                    }
                    if(count($res1)) {
                        foreach ($res1 as $item) {
                            $module = $item->module;
                            $instance = $item->instance;
                            $sql_item = "SELECT name FROM mdl_modules WHERE id ='" . $module . "'";
                            $item_res = $DB->get_record_sql($sql_item);
                            $itemname = $item_res->name; //echo  $itemname;
                            $grading_info = grade_get_grades($courseid, 'mod', $itemname, $instance, $student->id);
                            $item = $grading_info->items[0];
                            $gradeI = $item->grades[$student->id];
                            $grade = $gradeI->grade;
                            if ($grade) {
                                $displayFlag = 1;
                            }
                        }
                    }
                    if(count($res2)) {
                        foreach ($res2 as $item) {
                            $module = $item->module;
                            $instance = $item->instance;
                            $sql_item = "SELECT name FROM mdl_modules WHERE id ='" . $module . "'";
                            $item_res = $DB->get_record_sql($sql_item);
                            $itemname = $item_res->name; //echo  $itemname;
                            $grading_info = grade_get_grades($courseid, 'mod', $itemname, $instance, $student->id);
                            $item = $grading_info->items[0];
                            $gradeI = $item->grades[$student->id];
                            $grade = $gradeI->grade;
                            if ($grade) {
                                $displayFlag = 1;
                            }
                        }
                    }


                    if($studetnFlag) {
                        if ($displayFlag) {
                            if ($attandance) {
                                $html .= '<tr  class="student" data-id="' . $student->id . '">
                     <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="' . $student->email . '"  class="student-' . $student->id . '"  type="checkbox" name="selectstudents"> </td>            

                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">' . $student->firstname . " " . $student->lastname . '</a></td>
                 <td>PRESENT</td>
                 
                </tr>';
                                $stdcnt++;
                            }
                        } else {
                            if ($attandance != 1) {
                                $html .= '<tr  class="student" data-id="' . $student->id . '">
                               <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="' . $student->email . '"  class="student-' . $student->id . '"  type="checkbox" name="selectstudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">' . $student->firstname . " " . $student->lastname . '</a></td>
                 <td>ABSENT</td>
                 
                </tr>';
                                $stdcnt++;
                            }
                        }
                    }//end of student flag
                }//END OF STUDENT LOOP
                $count++;
            }
        }
    }

    if(!$html){
        $html="No Records Found";
    }
    return $html;
}





function getStudentsByCourseByGrade($courseid,$catid,$grade,$studentname){
    global $DB,$CFG;



    $html='';

    if($courseid){

        /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/



        $context = context_course::instance($courseid);
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $stdcnt=1;
        foreach($students as $student){
            
            $subjectgrade=TotalMeanGrade($courseid,$student->id);
            $subjectgrade=$subjectgrade['coursemeangrade'];
            if($studentname){
                if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                    $studetnFlag=1;
                }else{
                    $studetnFlag=0;
                }
            }else{
                $studetnFlag=1;
            }
            if($grade){
                if($grade==1) {
                    if ($subjectgrade == 100) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =1
                if($grade==2) {
                    if (($subjectgrade < 100) && ($subjectgrade >= 80)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =2
                if($grade==3) {
                    if (($subjectgrade < 80) && ($subjectgrade >= 60)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =3
                if($grade==4) {
                    if (($subjectgrade < 60) && ($subjectgrade >= 40)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =4
                if($grade==5) {
                    if (($subjectgrade < 40) && ($subjectgrade >= 1)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =5
                if($grade==6) {
                    if (($subjectgrade == 0)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =5
            }else{
                $displayFlag = 1;
            }



            if($studetnFlag){
                if($displayFlag){

                        $html.='<tr  class="student" data-id="'.$student->id.'">
                    <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="gradestudents"> </td>    
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>'.$subjectgrade.'</td>
                 
                </tr>';
                    $stdcnt++;

                }

            }//check student flag

        }//END OF STDUENT LOOP
    }else{
        $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$catid";
        $count=0;
        $resultset=$DB->get_records_sql($getCoursesql, null);
        foreach ($resultset as $res) {
            //repeat the process for each courseid to get students
            if($res) {
                $context = context_course::instance($res->id);
                $students = get_role_users(5, $context);//getting all the students from a course level
                $courseid=$res->id;
                /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/
                $displayFlag=0;

                $stdcnt=1;
                foreach ($students as $student) {
                    $subjectgrade=TotalMeanGrade($courseid,$student->id);
                    $subjectgrade=$subjectgrade['coursemeangrade'];

                    if($studentname){
                        if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                            $studetnFlag=1;
                        }else{
                            $studetnFlag=0;
                        }
                    }else{
                        $studetnFlag=1;
                    }
                    if($grade){
                        if($grade==1) {
                            if ($subjectgrade == 100) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =1
                        if($grade==2) {
                            if (($subjectgrade < 100) && ($subjectgrade >= 80)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =2
                        if($grade==3) {
                            if (($subjectgrade < 80) && ($subjectgrade >= 60)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =3
                        if($grade==4) {
                            if (($subjectgrade < 60) && ($subjectgrade >= 40)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =4
                        if($grade==5) {
                            if (($subjectgrade < 40) && ($subjectgrade >= 1)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =5
                        if($grade==6) {
                            if (($subjectgrade == 0)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =5
                    }else{
                        $displayFlag = 1;
                    }

                    if($studetnFlag){
                        if($displayFlag){

                            $html.='<tr  class="student" data-id="'.$student->id.'">
                             <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="gradestudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>'.$subjectgrade.'</td>
                
                </tr>';
                        $stdcnt++;
                        }

                    }//check student flag
                }//END OF STUDENT LOOP
                $count++;
            }
        }
    }

    if(!$html){
        $html="No Records Found";
    }
    return $html;
}




function getStudentsByCourseByAverageAtandance($courseid,$catid,$attandance,$studentname){
    global $DB,$CFG;




    $html='';

    if($courseid){

        /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/



        $context = context_course::instance($courseid);
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $stdcnt=1;
        foreach($students as $student){
            $displayFlag=1;
            $subjectgrade=TotalMeanGrade($courseid,$student->id);
            $totallabsquizes=$subjectgrade['totallabscount']+$subjectgrade['totalquizescount'];
            $totalattemptedlabsandquizes=$subjectgrade['attemptedlabs']+$subjectgrade['attemptedquiz'];

            $subjectgrade=round($totalattemptedlabsandquizes/$totallabsquizes,2)*100;


            if($studentname){
                if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                    $studetnFlag=1;
                }else{
                    $studetnFlag=0;
                }
            }else{
                $studetnFlag=1;
            }

            if($attandance){
                if($attandance==1) {
                    if ($subjectgrade == 100) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =1
                if($attandance==2) {
                    if (($subjectgrade < 100) && ($subjectgrade >= 80)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =2
                if($attandance==3) {
                    if (($subjectgrade < 80) && ($subjectgrade >= 60)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =3
                if($attandance==4) {
                    if (($subjectgrade < 60) && ($subjectgrade >= 40)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =4
                if($attandance==5) {
                    if (($subjectgrade < 40) && ($subjectgrade >= 1)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =5
                if($attandance==6) {
                    if (($subjectgrade == 0)) {
                        $displayFlag = 1;
                    } else {
                        $displayFlag = 0;
                    }
                }//if grade =5
            }else{
                $displayFlag = 1;
            }



            if($studetnFlag){
                if($displayFlag){

                    $html.='<tr  class="student" data-id="'.$student->id.'">
                    
 <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="attandancestudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>'.$subjectgrade.'</td>
                
                </tr>';
                    $stdcnt++;
                }

            }//check student flag

        }//END OF STDUENT LOOP
    }else{
        $getCoursesql="SELECT `id` , `fullname` FROM `mdl_course` where category=$catid";
        $count=0;
        $resultset=$DB->get_records_sql($getCoursesql, null);
        foreach ($resultset as $res) {
            //repeat the process for each courseid to get students
            if($res) {
                $context = context_course::instance($res->id);
                $students = get_role_users(5, $context);//getting all the students from a course level
                $courseid=$res->id;
                /* LOGIC TO GET ACTIVITIES BASED ON SELECTED DATE START*/


                $stdcnt=0;
                foreach ($students as $student) {
                    $displayFlag=0;
                    $subjectgrade=TotalMeanGrade($courseid,$student->id);
                    $totallabsquizes=$subjectgrade['totallabscount']+$subjectgrade['totalquizescount'];
                    $totalattemptedlabsandquizes=$subjectgrade['attemptedlabs']+$subjectgrade['attemptedquiz'];
                    $subjectgrade=round($totalattemptedlabsandquizes/$totallabsquizes,2);
                    if($studentname){
                        if ((stripos($student->firstname,$studentname) !== false)||(stripos($student->lastname,$studentname) !== false)) {
                            $studetnFlag=1;
                        }else{
                            $studetnFlag=0;
                        }
                    }else{
                        $studetnFlag=1;
                    }
                    if($attandance){
                        if($attandance==1) {
                            if ($subjectgrade == 100) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =1
                        if($attandance==2) {
                            if (($subjectgrade < 100) && ($subjectgrade >= 80)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =2
                        if($attandance==3) {
                            if (($subjectgrade < 80) && ($subjectgrade >= 60)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =3
                        if($attandance==4) {
                            if (($subjectgrade < 60) && ($subjectgrade >= 40)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =4
                        if($attandance==5) {
                            if (($subjectgrade < 40) && ($subjectgrade >= 1)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =5
                        if($attandance==6) {
                            if (($subjectgrade == 0)) {
                                $displayFlag = 1;
                            } else {
                                $displayFlag = 0;
                            }
                        }//if grade =5
                    }else{
                        $displayFlag = 1;
                    }

                    if($studetnFlag){
                        if($displayFlag){

                            $html.='<tr  class="student" data-id="'.$student->id.'">
                             
 <td><input data-mobile="'.get_complete_user_data(id,$student->id)->profile['mobile'].'"  data-email="'.$student->email.'"  class="student-'.$student->id.'"  type="checkbox" name="attandancestudents"> </td>
                <td class="studentname"><a target="_blank" href="'.$CFG->baseUrl.'supervisor/student_profile.php?sid='.$student->id.'">'.$student->firstname." ".$student->lastname.'</a></td>
                 <td>'.$subjectgrade.'</td>
                
                </tr>';
                            $stdcnt++;
                        }

                    }//check student flag
                }//END OF STUDENT LOOP
                $count++;
            }
        }
    }


    return $html;
}


function getTopicsByCourse($courseid){
    global $DB,$USER;
    $topics='';
    if($courseid){
        $secQuery="SELECT * FROM mdl_course_sections WHERE course='".$courseid."' AND name IS NOT NULL";
    }else{
        $allEnrolledCourses = enrol_get_users_courses($USER->id);
        $allEnrolledCourses=array_values($allEnrolledCourses);
        for($i=0;$i<count($allEnrolledCourses);$i++){
            $enrolledcids[]=$allEnrolledCourses[$i]->id;
        }
        $enrolledcids=array_unique($enrolledcids);
        $secQuery="SELECT * FROM mdl_course_sections WHERE course IN  (".implode(',',$enrolledcids).") AND name IS NOT NULL";
    }

    $sectons_obj = $DB->get_records_sql( $secQuery);

    $topics.="<option value='0'>All</option>";

    foreach ( $sectons_obj as $section) {
//var_dump($sec);
        if(empty($section->name))
        {
        }
        else{

            $topics.="<option value='".$section->id."'>".$section->name."</option>";

        }
    }

return $topics;
}




function getActivitiesByCourse($courseid){
    global $DB;
        $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
        $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND module IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    $activities=array();
    foreach ( $activities_obj as $act) {
        $activities[]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }
    return $activities;

}



function getGrade($courseid,$module,$instance,$studentId){

    global $DB;

        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
			FROM mdl_modules
			WHERE id ='".$module."'";

        $item_res=$DB->get_record_sql($sql_item);

        $itemname= $item_res->name;

        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;

        if($grade){
            return $grade;
        }else{
            return 0;
        }

}

        function getAllStudentsPerformance($courseid,$subject,$grade,$attandance,$fname,$lname){
            global $CFG;
        $html='';

            if($courseid){
                $context = context_course::instance($courseid);
                $students = get_role_users(5 , $context);//getting all the students from a course level

                foreach($students as $student){

                    $userobj = get_complete_user_data(id, $student->id);
                    if(!empty($lname)&&!empty($fname)){
                        if ((stripos($userobj->firstname,$fname) !== false)&&(stripos($userobj->lastname,$lname) !== false)) {
                            $displayFlag=1;
                        }
                    }
                    else if(!empty($fname)){
                        if (stripos($userobj->firstname,$fname) !== false) {
                            $displayFlag=1;
                        }
                    }
                    else if(!empty($lname)){
                        if (stripos($userobj->lastname,$lname) !== false) {
                            $displayFlag=1;
                        }
                    }

                    if(empty($lname)&&empty($fname)){
                        $displayFlag=1;
                    }

                    if($displayFlag) {

                        $meangrades = TotalMeanGrade($courseid, $student->id);

                        //print_r($meangrades);
                        $avg_attandance = ($meangrades['attemptedlabs'] + $meangrades['attemptedquiz']) / ($meangrades['totallabscount'] + $meangrades['totalquizescount']);
                        $avg_attandance = round($avg_attandance, 2)*100;
                        $labaverage = round($meangrades['labaverage'] / $meangrades['totallabscount'], 2);
                        $quizaverage = round($meangrades['quizaverage'] / $meangrades['totalquizescount'], 2);

                        if ($grade == 0) {
                            if ($attandance == 0) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        } else if ($attandance == 0) {
                            if ($grade == 0) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }

                        if ($grade == 6) {
                            if (($labaverage == 0) || ($quizaverage == 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 1) {
                            if (($labaverage == 100) || ($quizaverage == 100)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 2) {

                            if (($labaverage < 100) && ($labaverage >= 80) || ($quizaverage < 100) && ($quizaverage >= 80)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 3) {

                            if (($labaverage < 80) && ($labaverage >= 60) || ($quizaverage < 80) && ($quizaverage >= 60)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 4) {

                            if (($labaverage < 60) && ($labaverage >= 40) || ($quizaverage < 60) && ($quizaverage >= 40)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 5) {

                            if (($labaverage < 40) && ($labaverage > 0) || ($quizaverage < 40) && ($quizaverage > 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }

                        if ($attandance == 1) {
                            if ($avg_attandance == 100) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 2) {
                            if (($avg_attandance < 100) && ($avg_attandance >= 80)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 3) {
                            if (($avg_attandance < 80) && ($avg_attandance >= 60)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 4) {
                            if (($avg_attandance < 60) && ($avg_attandance >= 40)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 5) {
                            if (($avg_attandance < 40) && ($avg_attandance > 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 6) {
                            if ($avg_attandance == 0) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a href="'.$CFG->baseUrl.'teacher/student_profile.php?sid='.$student->id.'">' . $userobj->firstname . '</a></td>
                                    <td >' . $userobj->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }//end of attandance
                    }//end of display flag
                    $displayFlag=0;//resetting display flag
                }//end of student
                return $html;
            }

        }



function TotalMeanGrade($courseid,$studentId){
    global $DB;
    $courseReport=array();
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $sql="SELECT *
	    FROM mdl_course_modules
	    WHERE course = '".$courseid."' AND  completionexpected >0 AND module IN($vpl,$quiz)";
    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);

    $totalgrade=0;
    $meangrade=0;
    $totalLabsCount=0;
    $totalLabsAttemptedCount=0;
    $totalQuizCount=0;
    $totalQuizAttemptedCount=0;
    $labaverage=0;
    $quizaverage=0;
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;

        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
		FROM mdl_modules
		WHERE id ='".$module."'";
        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name;
        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;

        /***logic to count total activities and attempted activities***/
        if($module==$vpl){
            $totalLabsCount++;
            if($grade)
            {$labaverage=$labaverage+$grade;$totalLabsAttemptedCount++;
            }else{

                $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                $submissions=$DB->get_fieldset_sql($tsql);


                if( count($submissions)>0)
                {
                    $totalLabsAttemptedCount++;
                }
            }
        }
        if($module==$quiz){
            $totalQuizCount++;
            if($grade){
                $quizaverage=$quizaverage+$grade;$totalQuizAttemptedCount++;
            }
        }

        $totalgrade=$totalgrade+$grade;


    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT * FROM `mdl_course_modules` WHERE  `ID` IN (".implode(',',$startedActivityIds).") AND course = '".$courseid."'  AND module IN($vpl,$quiz)";
        $currentRes=$DB->get_records_sql($rsql);
        $items_completed_today=$items_completed_today+count($currentRes);
        foreach ($currentRes as $item )
        {
            $module=$item->module;
            $instance=$item->instance;

            /**************GETTING ITEM NAME **********/
            $sql_item="SELECT name
			FROM mdl_modules
			WHERE id ='".$module."'";

            $item_res=$DB->get_record_sql($sql_item);

            $itemname= $item_res->name;

            $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
            $item = $grading_info->items[0];
            $gradeI= $item->grades[$studentId];
            $grade = $gradeI->grade ;

            /***logic to count total activities and attempted activities***/
            if($module==$vpl){
                $totalLabsCount++;
                if($grade){
                    $labaverage=$labaverage+$grade;$totalLabsAttemptedCount++;
                }else{
                    $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                    $submissions=$DB->get_fieldset_sql($tsql);

                    if( count($submissions)>0)
                    {
                        $totalLabsAttemptedCount++;
                    }
                }
            }
            if($module==$quiz){$totalQuizCount++;if($grade){$quizaverage=$quizaverage+$grade;$totalQuizAttemptedCount++;}}

            $totalgrade=$totalgrade+$grade;

        }
    }//end of if (current activities check)


    /*echo "Labs:".$totalLabsCount;
    echo "--Quiz:".$totalQuizCount;
    echo "<br/>ALabs:".$totalLabsAttemptedCount;
    echo "--AQuiz:".$totalQuizAttemptedCount;*/



    if($totalgrade>0)
    {
        $meangrade=$totalgrade/$items_completed_today;
    }

    $courseReport=array("totallabscount"=>$totalLabsCount,"labaverage"=>$labaverage,"totalquizescount"=>$totalQuizCount,"quizaverage"=>$quizaverage,"coursemeangrade"=>round($meangrade,2),'attemptedlabs'=>$totalLabsAttemptedCount,'attemptedquiz'=>$totalQuizAttemptedCount);
    return $courseReport;


}

function ClassMeanGrade($courseid){

    global $DB;
    $classMeangrade=0;
    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $totalenrolled=count($students);

    foreach($students as $student){

        if($student->id){
            $meangrade=TotalMeanGrade($courseid,$student->id);
            $classMeangrade=$classMeangrade+$meangrade['coursemeangrade'];
        }
    }

    $classTotalMean=$classMeangrade/$totalenrolled;

    return array("classmeangrade"=>round($classTotalMean,2));
}

function ClassMeanAttandance($courseid){

    global $DB;
    $classMeanAttandance=0;
    $totalLabs=0;
    $attemptedLabs=0;
    $totalQuizes=0;
    $attemptedQuizes=0;

    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $totalenrolled=count($students);

    foreach($students as $student){

        if($student->id){
            $meangrade=TotalMeanGrade($courseid,$student->id);
            $totalLabs=$totalLabs+$meangrade['totallabscount'];
            $attemptedLabs=$attemptedLabs+$meangrade['attemptedlabs'];
            $totalQuizes=$totalQuizes+$meangrade['totalquizescount'];
            $attemptedQuizes=$attemptedQuizes+$meangrade['attemptedquiz'];
        }
    }

    $classMeanAttandance=($attemptedLabs+$attemptedQuizes)/($totalLabs+$totalQuizes);

    return array("classmeanattandance"=>round($classMeanAttandance,2));
}


function getCoursesByCateogoryByUser($userid,$categoryid){

    $allEnrolledCourses = enrol_get_users_courses($userid);

    $allEnrolledCourses=array_values($allEnrolledCourses);
    //var_dump($allEnrolledCourses);

    for($i=0;$i<count($allEnrolledCourses);$i++){
        if($allEnrolledCourses[$i]->category==$categoryid) {

            $courseidArray[]=array("id"=>$allEnrolledCourses[$i]->id,"name"=>$allEnrolledCourses[$i]->fullname);

        }
    }


    return $courseidArray;
}

function getSubjectsGrades($userid,$programid){

    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    $html='';
    $format='';$classmean='';$subjectnames='';$meangrade='';
    for($i=0;$i<count($courseidArray);$i++){
        $mgrade=TotalMeanGrade($courseidArray[$i]['id'],$userid);
        $meanGrade=$mgrade['coursemeangrade'];
        $cgrade=ClassMeanGrade($courseidArray[$i]['id']);
        $classMean=$cgrade['classmeangrade'];
        $subjectnames.=",'".$courseidArray[$i]['name']."'";
        if(!$meanGrade){
            $meanGrade=0;
        }if(!$classMean){
            $classMean=0;
        }
        $meangrade.=",".$meanGrade;
        $classmean.=",".$classMean;
        $html.= '<tr>
                <td>'.$courseidArray[$i]['name'].'</td>
                <td>'.$meanGrade.'</td>
                <td>'.$classMean.'</td>

                </tr>';
    }

    //$html[$i]=array('x'.$subjectnames=>array('student meangrade'=>$meangrade),'class meangrade'=>($activities[$i]->grade));

   // $format="['x'$subjectnames],"."['student meangrade'$meangrade],"."['class meangrade'$classmean],";
    /*['x', 'aa', '123', '123dfg', '#$%'],
                ['student meangrade', 30, 20, 50, 40, 60, 50],
                ['class meangrade', 130, 120, 150, 140, 160, 150],*/
    //$format= json_encode(array('success'=>'true','result'=>$format));


    return $html;

}



function getTotalCourseLabs($programid,$userid){
    $totallabscount=0;
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    for($i=0;$i<count($courseidArray);$i++){
        $subjectReport=TotalMeanGrade($courseidArray[$i]['id'],$userid);
        $totallabscount+=$subjectReport['totallabscount'];
    }
    return $totallabscount;
}

function getTotalCourseQuizes($programid,$userid){
    $totalquizscount=0;
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    for($i=0;$i<count($courseidArray);$i++){
        $subjectReport=TotalMeanGrade($courseidArray[$i]['id'],$userid);

        $totalquizscount+=$subjectReport['totalquizescount'];
    }

    return $totalquizscount;
}

function getAttemptedCourseLabs($programid,$userid){
    $attendedlabscount=0;
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    for($i=0;$i<count($courseidArray);$i++){
        $subjectReport=TotalMeanGrade($courseidArray[$i]['id'],$userid);
        $attendedlabscount+=$subjectReport['attemptedlabs'];
    }
    return $attendedlabscount;
}

function getAttemptedCourseQuizes($programid,$userid){
    $attemptedquizscount=0;
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    for($i=0;$i<count($courseidArray);$i++){
        $subjectReport=TotalMeanGrade($courseidArray[$i]['id'],$userid);
        $attemptedquizscount+=$subjectReport['attemptedquiz'];
    }
    return $attemptedquizscount;
}


function getTotalCourseMeanGrade($userid,$programid){

    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);
    $totalmeangrade=0;$meangrade=0;
    for($i=0;$i<count($courseidArray);$i++){
        $mgrade=TotalMeanGrade($courseidArray[$i]['id'],$userid);
        $meanGrade=$mgrade['coursemeangrade'];
        $meangrade=$meangrade+$meanGrade;
    }

    $totalmeangrade=round($meangrade/count($courseidArray),2);

    return $totalmeangrade;

}


function getLastTwoWeekPerformance($userid,$programid){
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);

    $activities=array();
    for($i=0;$i<count($courseidArray);$i++){
        $result=getLastTwoWeekPerformanceOfUser($courseidArray[$i]['id'],$userid);
        //var_dump($result);
        $result_activities= $result['activities'];
        //  return $result->activities;
        $activities = array_merge($activities, $result_activities);

    }
    return $activities;

}

function getLastTwoWeekPerformanceOfUser($courseid,$studentId){

    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $at= strtotime(date("m/d/Y"));

    $from=strtotime('-2 weeks');
    $to=time() + 86400;


    $sql="SELECT cm.id,q.name as quizname,v.name as labname ,cm.instance,cm.module,cm.completionexpected
    FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
    WHERE cm.course = '".$courseid."' AND  cm.completionexpected between '".$from."' AND '".$to."' AND cm.module IN($vpl,$quiz)";



    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);

    $moduletype='';
    $activities = array();
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;
        $activityid=$item->id;
        $completeddate=$item->completionexpected;
        if($module==$vpl){
            $actname=$item->labname;
        }
        if($module==$quiz){
            $actname=$item->quizname;
        }
        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
        FROM mdl_modules
        WHERE id ='".$module."'";
        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name; //echo  $itemname;
        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;

        /***logic to count total activities and attempted activities***/
        /***logic to count total activities and attempted activities***/
        if($module==$vpl){
            $moduletype='vpl';
            if($grade){
                $gradeval=round($grade,2);
            }else{
                $gradeval='ABSENT';
                $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                $submissions=$DB->get_fieldset_sql($tsql);

                if( count($submissions)>0)
                {
                    $gradeval='PRESENT';
                }
            }
        }
        if($module==$quiz){$moduletype='quiz';if($grade){$gradeval=round($grade,2);}else{$gradeval='ABSENT';}}


        $activities[]=array('id'=>$activityid,'actname'=>$actname,'moduletype'=>$moduletype,'grade'=>$gradeval,'completeddate'=>$completeddate);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT cm.id,q.name as quizname,v.name as labname,cm.instance,cm.module,cm.completionexpected
    	FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    	left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
	WHERE  cm.id IN (".implode(',',$startedActivityIds).") AND cm.course = '".$courseid."' AND cm.module IN($vpl,$quiz) ";

//echo $rsql;

        $currentRes=$DB->get_records_sql($rsql);
        $items_completed_today=$items_completed_today+count($currentRes);
        foreach ($currentRes as $item )
        {
            $module=$item->module;
            $instance=$item->instance;
            $activityid=$item->id;
            $completeddate=$item->completionexpected;

            if($module==$vpl){
                $actname=$item->labname;
            }
            if($module==$quiz){
                $actname=$item->quizname;
            }
            /**************GETTING ITEM NAME **********/
            $sql_item="SELECT name
		FROM mdl_modules
		WHERE id ='".$module."'";

            $item_res=$DB->get_record_sql($sql_item);

            $itemname= $item_res->name;

            $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
            $item = $grading_info->items[0];
            $gradeI= $item->grades[$studentId];
            $grade = $gradeI->grade ;

            /***logic to count total activities and attempted activities***/
            /***logic to count total activities and attempted activities***/
            if($module==$vpl){
                $moduletype='vpl';
                if($grade){
                    $gradeval=round($grade,2);
                }else{
                    $gradeval='ABSENT';
                    $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                    $submissions=$DB->get_fieldset_sql($tsql);

                    if( count($submissions)>0)
                    {
                        $gradeval='PRESENT';
                    }
                }
            }
            if($module==$quiz){$moduletype='quiz';if($grade){$gradeval=round($grade,2);}else{$gradeval='ABSENT';}}

            $act_item="SELECT activity_start_time
		FROM mdl_activity_status
		WHERE activityid ='".$activityid."'";

            $actitem_res=$DB->get_record_sql($act_item);

            $act_start_time= $actitem_res->activity_start_time;


            $activities[]=array('id'=>$activityid,'actname'=>$actname,'moduletype'=>$moduletype,'grade'=>$gradeval,'completeddate'=>$act_start_time);

        }
    }//end of if (current activities check)


    return array("activities"=>$activities);


}


function getLastTwoWeekPerformanceJson($userid,$programid){
    $courseidArray=getCoursesByCateogoryByUser($userid,$programid);//array(18,19,21,22,23);

    $activities=array();
    for($i=0;$i<count($courseidArray);$i++){
        $result=getLastTwoWeekPerformanceOfUser($courseidArray[$i]['id'],$userid);
        //var_dump($result);
        $result_activities= $result['activities'];
        //  return $result->activities;
        $activities = array_merge($activities, $result_activities);
        //break;
    }
    // print_r($activities);
    $resultarray[]=array();
    for($i=0;$i<count($activities);$i++) {
        //echo $activities[$i]->grade;
        //  echo '<br/>';
        if($activities[$i]['completeddate']) {
            $gdate = date("y-m-d", $activities[$i]['completeddate']);
        }else{
            $gdate=date("y-m-d",time());
        }
        //echo $gdate;echo '<br/>';
        if (array_key_exists($gdate, $resultarray)) {
            $gradearray = $resultarray[$gdate];
            $gradetotal = $gradearray['gradetotal'] + round($activities[$i]['grade'],2);
            $gradecount = $gradearray['gradecount'] + 1;
            $resultarray[$gdate] = array('gradetotal' => $gradetotal, 'gradecount' => $gradecount);
        } else {
            $resultarray[$gdate] = array('gradetotal' => $activities[$i]['grade'], 'gradecount' => 1);
        }
    }
    //print_r($resultarray);
    $meangrades='';
    $dates='';
    foreach($resultarray as $key => $val){
        if($key){
            // $finalresult[]=array('date'=>$key,'meangrade'=>round($val['gradetotal']/$val['gradecount'],2));
            $day = date("d", strtotime($key));
            $month=date("M", strtotime($key));
            $key=$day.'-'.$month;
            $grade=round($val['gradetotal']/$val['gradecount'],2);
            $dates.="'$key',";
            $meangrades.=",'$grade'";
        }

    }
    $finalresult=array("grade"=>'["meangrades"'.$meangrades.']',"dates"=>'['.$dates.']');
//print_r("<br/>");
    return $finalresult;


}



function TotalCompletedActivities($courseid,$studentId)
{

    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $at= strtotime(date("m/d/Y"));




    $sql="SELECT cm.id,q.name as quizname,v.name as labname ,cm.instance,cm.module
    FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
    WHERE cm.course = '".$courseid."' AND  cm.completionexpected >0 AND cm.module IN($vpl,$quiz)";



    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);

    $moduletype='';
    $activities = array();
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;
        $activityid=$item->id;
        if($module==$vpl){
            $actname=$item->labname;
        }
        if($module==$quiz){
            $actname=$item->quizname;
        }
        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
        FROM mdl_modules
        WHERE id ='".$module."'";
        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name; //echo  $itemname;
        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;

        /***logic to count total activities and attempted activities***/
        /***logic to count total activities and attempted activities***/
        if($module==$vpl){
            $moduletype='vpl';
            if($grade){
                $gradeval=round($grade,2);
            }else{
                $gradeval='ABSENT';
                $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                $submissions=$DB->get_fieldset_sql($tsql);

                if( count($submissions)>0)
                {
                    $gradeval='PRESENT';
                }
            }
        }
        if($module==$quiz){$moduletype='quiz';if($grade){$gradeval=round($grade,2);}else{$gradeval='ABSENT';}}


        $activities[]=array('id'=>$activityid,'actname'=>$actname,'moduletype'=>$moduletype,'grade'=>$gradeval);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT cm.id,q.name as quizname,v.name as labname,cm.instance,cm.module
    	FROM mdl_course_modules cm left JOIN mdl_quiz q ON cm.course = q.course  AND  cm.instance = q.id
    	left JOIN mdl_vpl v ON cm.course = v.course  AND  cm.instance = v.id
	WHERE  cm.id IN (".implode(',',$startedActivityIds).") AND cm.course = '".$courseid."' AND cm.module IN($vpl,$quiz)";



        $currentRes=$DB->get_records_sql($rsql);
        $items_completed_today=$items_completed_today+count($currentRes);
        foreach ($currentRes as $item )
        {
            $module=$item->module;
            $instance=$item->instance;
            $activityid=$item->id;

            if($module==$vpl){
                $actname=$item->labname;
            }
            if($module==$quiz){
                $actname=$item->quizname;
            }
            /**************GETTING ITEM NAME **********/
            $sql_item="SELECT name
		FROM mdl_modules
		WHERE id ='".$module."'";

            $item_res=$DB->get_record_sql($sql_item);

            $itemname= $item_res->name;

            $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
            $item = $grading_info->items[0];
            $gradeI= $item->grades[$studentId];
            $grade = $gradeI->grade ;

            /***logic to count total activities and attempted activities***/
            /***logic to count total activities and attempted activities***/
            if($module==$vpl){
                $moduletype='vpl';
                if($grade){
                    $gradeval=round($grade,2);
                }else{
                    $gradeval='ABSENT';
                    $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

                    $submissions=$DB->get_fieldset_sql($tsql);

                    if( count($submissions)>0)
                    {
                        $gradeval='PRESENT';
                    }
                }
            }
            if($module==$quiz){$moduletype='quiz';if($grade){$gradeval=round($grade,2);}else{$gradeval='ABSENT';}}

            $activities[]=array('id'=>$activityid,'actname'=>$actname,'moduletype'=>$moduletype,'grade'=>$gradeval);

        }
    }//end of if (current activities check)


    return array("activities"=>$activities);

}



function getResults($student,$subject){

    $activities=TotalCompletedActivities($subject,$student);
    $activities=$activities['activities'];
    $html='';

    if(!count($activities)){
        echo '<tr>
                <th class="no-data"  colspan="5" rowspan="5">No Report is generated yet.</th>
              </tr>';
        return ;
    }
    for($i=0;$i<count($activities);$i++){

        $html.= '<tr>
        <td >'.($i+1).'</td>
        <td >'.strtoupper($activities[$i]['moduletype']).'</td>
        <td >'.ucwords($activities[$i]['actname']).'</td>
        <td>'.$activities[$i]['grade'].'</td>
        </tr>';
    }
    echo $html;
}





function getStudentInfo($userid,$catid){
    global $CFG;
    $userobj = get_complete_user_data(id, $userid);
    $totalCourseMeanGrade=getTotalCourseMeanGrade($userid,$catid);
    if(empty($userobj->email))
        $email= "--";
    else
        $email= $userobj->email;

    if(empty($totalCourseMeanGrade))
        $grade= "0";
    else
        $grade= $totalCourseMeanGrade;


    $html=' <div class="row prof" style="background: whitesmoke none repeat scroll 0% 0%;
margin: 9px 20px;">
                    <div class="col-md-2">
                        <figure class="course-image">
                            <div class="image-wrapper">
                            <img style="margin:15px;" src="'.$CFG->wwwroot.'/user/pix.php/'.$userid.'/f1.jpg"></div>
                        </figure>
                    </div>
                    <div class="col-md-10">
                        <header>
                            <h2 class="course-date">'.ucfirst($userobj->firstname ."  ". $userobj->lastname).'</h2>
                            <div class="course-category">
                                <div style="font-size: 16px; margin-top: -5px;" class="course-category pull-right">Email:<a href="#">
                                 '.$email.'</a></div></div>


                        </header><hr style="margin-top: 5px;
margin-bottom: 13px;">
                        <div class="course-count-down pull-left" >
                            <figure class="course-start">Overall Grade:</figure>
                            <!-- /.course-start -->
                            <div class="count-down-wrapper" style="">'.$grade.'
                                </div><!-- /.count-down-wrapper -->

                        </div>



                    </div>

                </div>


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
                                        '.$userobj->firstname.'
                                    </div><!-- /input-group -->
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Last Name</td>
                                <td>

                                    <div class="input-group">

                                        '.$userobj->lastname.'
                                    </div><!-- /input-group -->
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Email</td>
                                <td>
                                    <div class="input-group">
                                        '.$userobj->email.'
                                    </div><!-- /input-group -->
                                </td>
                            </tr>


                            </tbody>
                        </table>
                    </div>

';


    return $html;
}




function getStudnetPerformance($userid,$catid){


    $totalLabsandQuizes=getTotalCourseQuizes($catid,$userid)+getTotalCourseLabs($catid,$userid);
    $totalAttemptedLabsandQuizes=getAttemptedCourseQuizes($catid,$userid)+getAttemptedCourseLabs($catid,$userid);

    $totalCourseMeanGrade=getTotalCourseMeanGrade($userid,$catid);
    $totallabPerformance=getAttemptedCourseLabs($catid,$userid).'/'.getTotalCourseLabs($catid,$userid);
    $totalquizPerformance=getAttemptedCourseQuizes($catid,$userid).'/'.getTotalCourseQuizes($catid,$userid);


    $html='

                        <div class="status-count">
                            <div class="meangrade-label status-count-label">Mean Grade</div>
                            <div class="status-values">'.$totalCourseMeanGrade.'</div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label attandance-label">Attandance</div>
                            <div class="status-values">'.$totalAttemptedLabsandQuizes.'/'.$totalLabsandQuizes.'</div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label labs-label">Labs</div>
                            <div class="status-values">'.$totallabPerformance.'</div>
                        </div>
                        <div class="status-count">
                            <div class="status-count-label quiz-lable">Quiz</div>
                            <div class="status-values">'.$totalquizPerformance.'</div>
                        </div>

                    <!-- end of status box -->';

    return $html;


}
















?>

