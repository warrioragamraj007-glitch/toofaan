<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 12/9/16
 * Time: 1:22 PM
 */


//define('CLI_SCRIPT', true);
//$_SERVER['HTTP_HOST']='teleuniv.net';
// Add these lines at the beginning of the updateWebinarAttendance function
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// require_once(dirname(__FILE__) . '/../config.php');
require_once('../../config.php');
// require_once($CFG->dirroot.'/teacher/testcenter/testcenterutil.php');
define('SMSURL','http://www.bulksmsapps.com/api/apibulkv2.aspx?apikey=5ec97466-ac51-4b6f-9991-92c116c04e69&sender=KMITEC');



$smsmid=$_GET['smsmid'];
$reflinkid = $_GET['linkid'];
$cdate = $_GET['curdate'];
$todayCourse=5;
$cid=$_GET['cid'];
$aid=$_GET['aid'];
$userid=$_GET['userid'];

$parentphones=$_GET['parentphones'];
$rollnos=$_GET['rollnos'];

if($_POST){
    $rollnos=$_POST['rollnos'];
    $smsmid=$_POST['smsmid'];
    $parentphones=$_POST['parentphones'];
}

switch($smsmid) {
    case 1:
        getAbsentCountToday($todayCourse);
        break;
    case 2:
        sendsms($todayCourse,$parentphones);
        break;
    case 3:
        sendnotification($todayCourse,$rollnos);
        break;
    case 4:
        storelinkCount($reflinkid,$userid);
        break;
    case 5:
        getlinkhits();
        break;
    case 6:
        getlinkhitsbydate($cdate);
        break;
    case 7:
        storelinkCountByDate($reflinkid);
        break;
    case 8:
        getOveralllinkhits();
        break;
    case 9:
        updateWebinarAttendance($cid,$aid,$userid);
        break;
}


function checkTodayAttendance($cid,$aid,$userid){
global $DB;

    $sql = "select id FROM mdl_webinar_attendance where cid='".$cid."' and aid='".$aid."' and userid='".$userid."'";
    //echo $sql;
    $result1 = $DB->get_records_sql($sql,null);
    if($result1){
        return 0;
    }else{
        return 1;
    }

    /*if(($from<$logintime)&&($logintime<$to)){
        return 0;
    }else{
        return 1;
    }*/

}

function getMorningTeleconnectID($todayCourse){
    global $DB;
    $slot_end="9.30";
    $from = strtotime("6.00");
    $to = strtotime($slot_end);
    $teleconnect=$DB->get_field('modules', 'id', array('name'=>'teleconnect'));

    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status` WHERE (`status` = 2  OR `status` = 1 OR `status` = 0) AND (`activity_start_time`  between '".$from."' AND '".$to."') OR (`activity_stop_time`  between '".$from."' AND '".$to."')";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    //var_dump(count($startedActivitiesRes));
    $result1=array_values($startedActivitiesRes);

    foreach($result1 as $act){
        $sql="SELECT `module` FROM `mdl_course_modules` WHERE `id` ='".$act->activityid."' and course='".$todayCourse."'";
        $actres=$DB->get_record_sql($sql);
        if($actres->module==$teleconnect){
            return $act->activityid;
        }
    }
}

function getAbsentCountToday($todayCourse){

global $DB,$CFG;

    if($todayCourse){
        $context = context_course::instance($todayCourse);
        $students = get_role_users(5 , $context);//getting all the students from a course level
        $csvarry=array();
        $csvarry[]=array('parentnumber','message');
        $actname=$actnames='';
        $absentiesparentsppno='';
        $absentiesrollno='';
        $count=0;
        $totalCount=0;

        $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
        $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
        $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

        $actid=getMorningTeleconnectID($todayCourse);
        foreach($students as $student){

            if($CFG->optimize){
                $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$student->id."' AND `fieldid` ='".$rollfield."'";
                $roll=$DB->get_record_sql($sql);
                $rollnumber=$roll->data;
            }else{
                $rollnumber=get_complete_user_data('id',$student->id)->profile['rollno'];
            }

            if($rollnumber){

                if($CFG->optimize){
                    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$student->id."' AND `fieldid` ='".$sectionfield."'";
                    $sec=$DB->get_record_sql($sql);
                    $stusection=$sec->data;
                }else{
                    $stusection=get_complete_user_data('id',$student->id)->profile['section'];
                }

                $stusection=explode("-",$stusection);
                $attflag=1;
                $year=$stusection[0];

                if($year==2){
                $totalCount++;
                $attflag=checkTodayAttendance($todayCourse,$actid,$student->id);
                   // echo '<br/>';
                   // echo $student->firstname.' '.$student->lastname.'('.$rollnumber.')-'.$attflag.'<br/>';
                if($attflag){
                    $count++;
                    if($CFG->optimize){
                        $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$student->id."' AND `fieldid` ='".$parentphonefield."'";
                        $ppnoresult=$DB->get_record_sql($sql);
                        $ppno=$ppnoresult->data;
                    }else{
                        $ppno=get_complete_user_data('id',$student->id)->profile['parentcontactno'];
                    }


                    $smsmsg='Your ward is absent for today\'s early morning Java session';
                    $csvarry[]=array($ppno,$smsmsg);
                    if($absentiesparentsppno){
                        if(preg_match('/^\d{10}$/',$ppno))
                        {
                            $absentiesparentsppno=$absentiesparentsppno.','.$ppno;
                        }

                    }else{
                        if(preg_match('/^\d{10}$/',$ppno))
                        {
                            $absentiesparentsppno = $ppno;
                        }
                    }
                    if($absentiesrollno){
                        $absentiesrollno=$absentiesrollno.','.$rollnumber;
                    }else{
                        $absentiesrollno=$rollnumber;
                    }
                }
            }//end of each 2nd year student section check
            }//end of each student rollno
           // echo '<br/>';

        }//end of student for loop

    }

    $slot_end="9.30";
    $from = strtotime("6.00");
    $to = strtotime($slot_end);

    if(($from<time())&&(time()<$to)) {
        echo $count . '/' . $totalCount;
    }else{
        echo "--";
    }
    echo '<input id="parent-phones" type="hidden" value="'.$absentiesparentsppno.'" />';
    echo '<input id="rollnos" type="hidden" value="'.$absentiesrollno.'" />';
    //echo $absentiesrollno;

}

function checkSMS($todayCourse){
    global $DB;
    $curdate=date("y-m-d",time());
    $sql = "select smsid FROM mdl_webinar_attendance_sms where courseid='".$todayCourse."' and curdate='".$curdate."'";
    $result1 = $DB->get_record_sql($sql,null);
    if($result1){
        if($result1->smsid){
            return $result1->smsid;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}
function addSMSID($todayCourse,$smsid){
    global $DB;
    $curdate=date("y-m-d",time());
    $smsmsgid=new stdClass();
    $smsmsgid->smsid=$smsid;
    $smsmsgid->courseid=$todayCourse;
    $smsmsgid->curdate=$curdate;
    //var_dump($smsmsgid);
    $DB->insert_record_raw('webinar_attendance_sms', $smsmsgid, false);

        echo "SMS sent Successfully";
}
function sendsms($todayCourse,$parentphones){

    $logger = Logger::getLogger("Send Bulk SMS Service - FS-JAVA: ");

    $slot_end="9.30";
    $from = strtotime("6.00");
    $to = strtotime($slot_end);

    if(checkSMS($todayCourse)){
        echo "SMS is already sent today";
        return ;
    }else{

        /*if(($from<time())&&(time()<$to)) {

        }else{
            echo "you cannot send sms  after 9.30AM";
            return;
        }*/
    $absentiesparentsppno=$parentphones;
    $logger->info($absentiesparentsppno);
    //echo $absentiesparentsppno;
    $smsmsg='Your ward is absent for today\'s early morning Java session';
    $msg = urlencode($smsmsg);
    //$absentiesparentsppno='9885008881';
    $url=SMSURL."&number=".$absentiesparentsppno."&message=".$msg;
    $logger->info($url);
    $curl_handle=curl_init();
    curl_setopt($curl_handle,CURLOPT_URL,$url);
    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
    $buffer =curl_exec($curl_handle);
    curl_close($curl_handle);

    //var_dump($buffer);

    if (preg_match('/\d{8}/', $buffer)){
        //echo "SMS sent Successfully";
        $str = explode('-',$buffer);
        preg_match('/\d{8}/', $str[1],$smsResId);
        addSMSID($todayCourse,$smsResId[0]);
    }
    else{  echo "SMS not sent , Try Again";    }

    }//sms not sent checking  end

}


function sendnotification($todayCourse,$rollnos){


    $slot_end="9.30";
    $from = strtotime("6.00");
    $to = strtotime($slot_end);
    $logger = Logger::getLogger("Send Notification Service - FS-JAVA: ");
    if(($from<time())&&(time()<$to)) {

    }else{
        echo "you cannot send  notification after 9.30AM";
        return;
    }
    $rollnosarray=explode(",",$rollnos);
    $curdate=date("d-M-Y",time());
    $smsmsg='Your ward is absent for early morning Java session today ('.$curdate.')';
    $msg = urlencode($smsmsg);
    $title=urlencode("Morning Class Attendance");

    for($i=0;$i<count($rollnosarray);$i++){
        //$rollnosarray[$i]='A104';
        $url="http://teleuniv.in/sanjaya/trishul.php?rollno=".$rollnosarray[$i]."&title=".$title."&message=".$msg;
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,$url);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

    }
        $logger->info($rollnosarray);
        echo "Notification Sent Successfully";


}

function storelinkCount($reflinkid,$userid){
   global $DB;



    $sql = "select linkhits FROM mdl_subject_topic_videos where id='".$reflinkid."'";
    //echo $sql;
    $result1 = $DB->get_records_sql($sql,null);
    $result1=array_values($result1);
    //var_dump($result1[0]->linkhits);
    $count=$result1[0]->linkhits;
    $count=$count+1;
    $subject_topic_videos=new stdClass();
    $subject_topic_videos->linkhits=$count;
    $subject_topic_videos->id=$reflinkid;
    $DB->update_record_raw('subject_topic_videos', $subject_topic_videos, false);
    storelinkCountByDate($reflinkid);


       // echo $userid." Hello ".$reflinkid;

   // $DB->insert_record_raw('mdl_video_log', $mdl_video_log, false);
 $insertTable= $DB->execute("insert into mdl_video_log (userid, sessionid) values('".$userid."', '".$reflinkid."')");


   // $sql="UPDATE `mdl_subject_topic_videos` SET `linkhits` = '".$count."' WHERE `id` ='".$reflinkid."'";
   // $result1 = $DB->get_records_sql($sql,null);*/
}

function getlinkhits(){
    global $DB;
            $yr="6";
            $sql = "select id,topicname,linkhits FROM mdl_subject_topic_videos where year='".$yr."' order by id desc";

            $result1 = $DB->get_records_sql($sql,null);
            $i=0;
            if($result1){
                echo '<table class="table table-hover course-list-table " id="myTable">
                                    <thead>
                                    <tr>
                                        <th>SNO</th>
                                        <th>Topic</th>
                                        <th>Hits</th>
                                    </tr>
                                    </thead>

                                    <tbody class="latest-performance-table">';
                foreach($result1 as $row1) {
                    $i++;
                    echo "<tr><td>".$i."</td><td>".$row1->topicname."</td><td>".$row1->linkhits."</td></tr>";

        }
        echo '</tbody></table>';
    }

}//end of getlinkhits


function storelinkCountByDate($reflinkid){
    global $DB;
    $cdate=date('y-m-d',time());
    $sql = "select id,hits FROM mdl_fs_video_hits where topicid='".$reflinkid."' and curdate='".$cdate."'";
    //echo $sql;
    $result1 = $DB->get_records_sql($sql,null);
    if($result1){
        $result1=array_values($result1);
        $count=$result1[0]->hits;
        $count=$count+1;
        $subject_topic_videos=new stdClass();
        $subject_topic_videos->hits=$count;
        $subject_topic_videos->topicid=$reflinkid;
        $subject_topic_videos->curdate=$cdate;
        $subject_topic_videos->id=$result1[0]->id;
        $DB->update_record_raw('fs_video_hits', $subject_topic_videos, false);
    }else{
        $subject_topic_videos=new stdClass();
        $subject_topic_videos->hits=1;
        $subject_topic_videos->topicid=$reflinkid;
        $subject_topic_videos->curdate=$cdate;
        $DB->insert_record_raw('fs_video_hits', $subject_topic_videos, false);
    }

}//end of storelinkCountByDate

function getlinkhitsbydate($cdate){
    global $DB;
    $yr="6";
    $sql = "select stv.id,stv.topicname as topicname,fvh.hits as linkhits FROM mdl_subject_topic_videos as stv,mdl_fs_video_hits as fvh where stv.year='".$yr."' AND fvh.curdate='".$cdate."' AND stv.id=fvh.topicid order by stv.id desc ";

    $result1 = $DB->get_records_sql($sql,null);
    $i=0;
    if($result1){
        echo '';
        foreach($result1 as $row1) {
            //var_dump($row1);
            $i++;
            echo "<tr><td>".$i."</td><td>".$row1->topicname."</td><td>".$row1->linkhits."</td></tr>";

        }
        echo '';
    }

}//end of getlinkhitsbydate


function getOveralllinkhits(){
    global $DB;
    $yr="6";
    $sql = "select id,topicname,linkhits FROM mdl_subject_topic_videos where year='".$yr."' order by id desc";

    $result1 = $DB->get_records_sql($sql,null);
    $i=0;
    if($result1){
        //echo '';
        foreach($result1 as $row1) {
            $i++;
            $sql1 = "select SUM(hits) as hits FROM mdl_fs_video_hits where topicid='".$row1->id."'";
            //echo '<br/>';
            $result2 = $DB->get_record_sql($sql1,null);
            //var_dump($result2);
            if($result2){$count=$result2->hits;}else{$count=0;}
            echo "<tr><td>".$i."</td><td>".$row1->topicname."</td><td>".$count."</td></tr>";

        }
        //echo '</tbody>';
    }

}//end of getOveralllinkhits





function updateWebinarAttendance($cid,$aid,$userid){
    global $DB;
	if($cid&&$aid&&$userid){
    $sql = "select id FROM mdl_webinar_attendance where cid='".$cid."' and aid='".$aid."' and userid='".$userid."'";
    // echo $sql;
    $result1 = $DB->get_records_sql($sql,null);
    if($result1){
        $result1=array_values($result1);
        $id=$result1[0]->id;
        $webinar_attendance=new stdClass();
        $webinar_attendance->id=$id;
        $webinar_attendance->cid=$cid;
        $webinar_attendance->aid=$aid;
        $webinar_attendance->userid=$userid;
        $webinar_attendance->attendance = 1; // Set the appropriate value here

        $webinar_attendance->updatedon=time();
        $DB->update_record_raw('webinar_attendance', $webinar_attendance, false);
    }else{
        $webinar_attendance=new stdClass();
        $webinar_attendance->cid=$cid;
        $webinar_attendance->aid=$aid;
        $webinar_attendance->userid=$userid;
        $webinar_attendance->attendance = 1; // Set the appropriate value here

        $webinar_attendance->updatedon=time();
        var_dump($webinar_attendance);
        $DB->insert_record_raw('webinar_attendance', $webinar_attendance, false);
    }
	}
}

