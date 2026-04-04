
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


switch($trmid){

    case 1: getTeacherEnrolledCategories();break;
    case 2: echo getCoursesByCateogory($categoryid);break;
    case 3: echo getTopicsByCourse($courseid);break;
    case 4: echo getActivitiesByTopicAndCourse($courseid,$topicid);break;
    case 5: echo getGradeByTopicAndCourse($courseid,$topicid,$catname,$topicname,$subjectname);break;
    case 6: echo getAllStudentsPerformance($courseid,$subjectname,$grade,$attendance,$fname,$lname);break;
    case 7: echo getAttandanceByTopicAndCourse($courseid,$topicid,$catname,$topicname,$subjectname);break;
    case 8: echo getResults($student,$courseid);break;
    case 9: echo getActivitiesByTopicCourse($courseid,$topicid);break;
    case 10: echo get_student_sections_by_course($courseid);break;
    case 11: echo get_students_activity_info($courseid,$topicid,$stdsection);break;
    case 12: echo get_students_activity_summary($courseid,$topicid,$stdsection,$topicname);break;
    case 13: echo get_students_activity_info_report($courseid,$topicid,$stdsection);break;
    case 14: echo getAllStudentsPerformanceByWeek($courseid,$typeflag);break;
    case 15: echo getAllStudentsPerformanceCumulative($courseid,$typeflag);break;
    case 16: echo getCPCAttendanceReportDaily($courseid);break;
    case 17: echo json_encode(getBottomStudentsDaily($courseid));break;
    case 18: echo json_encode(getCPCAttendanceReport($courseid));break;
    case 19: echo json_encode(getCPCTodayPerformance($courseid));break;
    case 20: echo update_webinar_attendance($rollno,$attendance);break;
    case 21: echo getCourseTodayPerformance($courseid,$stdsection);break;
    case 22: echo get_student_sections_by_course($courseid);break;
    case 23: echo getActivitiesModsByTopicAndCourse($courseid,$topicid);break;
    case 24: echo getAssignmentsByCourse($courseid);break;
    case 25: echo getStudentCustomReports($courseid,$fromdate,$todate,$stdsection);break;
    case 27: echo getOverallCourseReport($courseid);break;



}



/*************************************************************************************************************************/


function getOverallCourseReport($courseid){
global $USER,$DB;
//echo $courseid;

$maxactivities="SELECT count(a.id) as total FROM mdl_user_vpl_grades_view as a
where a.course='".$courseid."' /* and id in(696,705,740,760,772,773,789,790,824,825,831,832,845,846,864,862,898,928,930,1013,1012,1025,1096,1097,1098,1128,1129,1327,1315,1316,1297,1270,1260,1234)*/ group by a.userid order by total desc limit 1";

    $totact=$DB->get_record_sql($maxactivities, null);

//var_dump($totact);
 $getCategoriessql="select distinct c.username as HallTicketNo,c.section,c.year,c.phone1,c.fullname as StudentName ,b.tgrade as TotalScore,b.total as TotalActivities, round((b.tgrade/'".$totact->total."'),2) as Percentile from (SELECT a.userid,round(sum(a.grade),0) as tgrade,count(a.id) as total FROM mdl_user_vpl_grades_view as a
where a.course='".$courseid."' group by a.userid ) as b left join mdl_complete_user_view as c on b.userid=c.id order by round((b.tgrade/'".$totact->total."'),2) desc;";
//echo $getCategoriessql;
    $results=$DB->get_records_sql($getCategoriessql, null);
$sno=1;
//var_dump($results);
if( count($results)>0){
    foreach ($results as $res) {
//var_dump($res);

		$totalscore=is_null($res->totalscore)?'0':$res->totalscore;
		$totalactivities=is_null($res->totalactivities)?'0':$res->totalactivities;
		$percentile=is_null($res->percentile)?'0':$res->percentile;
		$year=$res->year==''?'0':$res->year;
		if($year==0)
		  continue;
	  	$html.='<tr><td>'.$sno++.'</td><td>'.$res->hallticketno.'</td><td>'.$res->studentname.'</td>
                <td>'.$totalscore.'</td><td>'.$totalactivities.'</td><td>'.$percentile.'</td><td>'.$res->section.'</td><td>'.$year.'</td>
<td>'.$res->phone1.'</td>';
	}
echo $html;
}
else{
        echo '<tr>
                <th class="no-data"  colspan="5" >No Records Found.</th>
              </tr>';
        return ;
    }
}




function getAssignmentsByCourse($courseid){
    global $DB;
    $assign=$DB->get_field('modules', 'id', array('name'=>'assign'));

    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid."  AND `module` IN($assign)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    //echo $activitiesSql;
    $activities=array();
    $current_activities=array();
    foreach ( $activities_obj as $act) {
        if($act->completionexpected){
            $status=2;
            $statusdate="<span class='status-text stopped actstatus".$act->id."'><b>CLOSED </b><br>on ".userdate($act->completionexpected)."</span>";

        }else{

            $status=-1;
            $statusdate="Not Started";

            $sql=" SELECT * FROM `mdl_activity_status_tsl` WHERE activityid='".$act->id."' ";
            $ActivitiesRes=$DB->get_records_sql($sql);
            foreach ($ActivitiesRes as $resitem )
            {
                if($resitem->status==0){
                    $status=0;
                    $statusdate="<span class='status-text stopped actstatus".$resitem->activityid."'><b>STOPPED </b><br>on ".userdate($resitem->activity_stop_time)."</span>";

                }
                if($resitem->status==1){
                    $status=1;
                    $statusdate="<span class='status-text started actstatus".$resitem->activityid."'><b>STARTED </b><br>on ".userdate($resitem->activity_start_time)."</span>";

                }
            }
        }//end of status check
        $activities[$act->id]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance,"status"=>$status,"sdate"=>$statusdate);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }


    if(count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "'  AND `module` IN($assign)";
        $currentRes = $DB->get_records_sql($rsql);
        //echo $rsql;
        foreach ($currentRes as $item) {
            if(array_key_exists($item->id,$activities)){

            }else{
                if($item->completionexpected){
                    $status=2;
                    $statusdate="Closed At ".userdate($item->completionexpected);
                }else{

                    $status=-1;
                    $statusdate="Not Started";
                    $sql=" SELECT * FROM `mdl_activity_status_tsl` WHERE activityid='".$item->id."' ";
                    $ActivitiesRes=$DB->get_records_sql($sql);
                    foreach ($ActivitiesRes as $resitem )
                    {
                        if($resitem->status==0){
                            $status=0;
                            $statusdate="<span class='status-text stopped actstatus".$resitem->activityid."'><b>STOPPED </b><br>on ".userdate($resitem->activity_stop_time)."</span>";

                        }
                        if($resitem->status==1){
                            $status=1;
                            $statusdate="<span class='status-text started actstatus".$resitem->activityid."'><b>STARTED </b><br>on ".userdate($resitem->activity_start_time)."</span>";

                        }
                    }
                }//end of status check
                $activities[]=array("id"=>$item->id,"module"=>$item->module,"instance"=>$item->instance,"status"=>$status,"sdate"=>$statusdate);

            }

        }
    }
//var_dump($activities);

    $activities=array_values($activities);

    //var_dump($activities);
    $html='';$ctrlstmt="disabled='true' style='cursor:not-allowed'";
    foreach ( $activities as $act) {

        if(!empty(getAssignmentname($act['instance']))&& getAssignmentname($act['instance'])!="") {
            $html .= "<tr><td  style='width: 6% ! important;' ><input class='radio-activity' name='radio-button' type='radio' data-insid='" . $act['instance']."' data-aid='" . $act['id']."' data-mid='".$act['module'] . "'/></td>
            <td><span class='assignname" . $act['id']."'>" . getAssignmentname($act['instance']) . "</span>
             <span data-assid='". $act['id']."' class='assignname ' title='Show Description'>
             <i class='fa fa-info-circle' aria-hidden='true'></i>
                </span>
            <div class='desc desc".$act['id']."'  style='display:none' >".getAssignmentDesc($act['instance'])."</div></td>
            <td>".$act['sdate']."</td>

            <td style='vertical-align: middle;'>
                <button   data-mid='".$act['module']."' class='showhide show show".$act['id']."' id='show' value='".$act['id']."' ";

            if($act['status']==1||$act['status']==2){ $html.=$ctrlstmt;}
            if($act['status']==0){ $html.=" style='cursor:pointer'";}

            $html.="  title='Start Activity'>
                    Start</button>
                <button  data-mid='".$act['module']."'  class='showhide stop hide".$act['id']."' id='hide' value='".$act['id']."' ";

            if($act['status']==0||$act['status']==2){ $html.=$ctrlstmt;}
            if($act['status']==1){ $html.="style='cursor:pointer'";}

            $html.=" title='Stop Activity'>Stop</button>

           <!-- <button  data-mid='".$act['module']."'  class='showhide stop hide30'  id='view' value='".$act['id']."' -->";

            $html.="<!--  >View</button> -->

            </td></tr>";
        }

    }

//$resultarray=array_merge($activities,$current_activities);

    if(!$html){
        $html='<tr><td colspan="4" style="text-align: center">No Assignments Available for This Course</td></tr>';
    }
    return $html;

}

function getAssignmentname($iid){
    global $DB;
    $rsql = "SELECT name	FROM mdl_assign
	WHERE  id = $iid";
    $currentRes = $DB->get_record_sql($rsql);
    return $currentRes->name;
}
function getAssignmentDesc($iid){
    global $DB;
    $rsql = "SELECT intro FROM mdl_assign WHERE  id = $iid";
    $currentRes = $DB->get_record_sql($rsql);
    return $currentRes->intro;
}


/*************************************************************************************************************************/


function getDayFormat($timestamp){

    $day = date( "D",$timestamp).', ';
    $date = date( "d", $timestamp).' ';
    $month = date( "M", $timestamp).' ';
    $year = date( "o", $timestamp);

    return $day.$date.$month.$year;
}

function getTeacherEnrolledCategories(){
    global $USER,$DB;
    $allEnrolledCourses = enrol_get_users_courses($USER->id);

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
    global $USER;
    $allEnrolledCourses = enrol_get_users_courses($USER->id);

    $enrolledCourses=array();
//var_dump($allEnrolledCourses);
    $allEnrolledCourses=array_values($allEnrolledCourses);
   // $html='<div class="option selected active" data-selectable="" data-value="0">All</div>';
// var_dump($categoryid);
// var_dump($allEnrolledCourses);
  $teacherEnrolledCategories=getTeacherEnrolledCategories();
  $html="<option value='0'>Select Course</option>";
    for($i=0;$i<count($allEnrolledCourses);$i++){
// var_dump($allEnrolledCourses[$i]->category);       
// if(($allEnrolledCourses[$i]->category==$categoryid)||()) {
           // $html.="<div class='option' data-selectable='' data-value=".$allEnrolledCourses[$i]->id.">".$allEnrolledCourses[$i]->fullname."</div>";
           $html=$html."<option value='".$allEnrolledCourses[$i]->id."'>".$allEnrolledCourses[$i]->fullname;
            //$enrolledCourses[] = array("value"=>$allEnrolledCourses[$i]->id,"text"=>$allEnrolledCourses[$i]->fullname);
        }
  //  }

    //var_dump($category_typeids[0]["catid"]);
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

    $topics.="<option value='0'>Select Topic</option>";

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



function getRawTopicsByCourse($courseid){
    global $DB,$USER;
    $topics='';

    $from=strtotime('-2 weeks');
    $to=time() + 86400;

    if($courseid){
        $secQuery="SELECT * FROM mdl_course_sections WHERE course='".$courseid."' AND name IS NOT NULL ";

    }else{
        $allEnrolledCourses = enrol_get_users_courses($USER->id);
        $allEnrolledCourses=array_values($allEnrolledCourses);
        for($i=0;$i<count($allEnrolledCourses);$i++){
            $enrolledcids[]=$allEnrolledCourses[$i]->id;
        }
        $enrolledcids=array_unique($enrolledcids);
        $secQuery="SELECT * FROM mdl_course_sections WHERE course IN  (".implode(',',$enrolledcids).")   AND name IS NOT NULL ";
    }

    $sectons_obj = $DB->get_records_sql( $secQuery);

    /*$topics.="<option value='0'>All</option>";

    foreach ( $sectons_obj as $section) {
//var_dump($sec);
        if(empty($section->name))
        {
        }
        else{

            $topics.="<option value='".$section->id."'>".$section->name."</option>";

        }
    }*/

    return $sectons_obj;
}



function getActivitiesByTopicCourse($courseid,$topicid){
    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    // $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND `section` = ".$topicid." AND `module` IN($vpl)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    //echo $activitiesSql;
    $activities=array();
    $current_activities=array();
    foreach ( $activities_obj as $act) {
        $activities[$act->id]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid." AND `module` IN($vpl)";
        $currentRes = $DB->get_records_sql($rsql);
        //echo $rsql;
        foreach ($currentRes as $item) {
            if(array_key_exists($item->id,$activities)){

            }else{
                $activities[]=array("id"=>$item->id,"module"=>$item->module,"instance"=>$item->instance);
            }

        }
    }
//var_dump($activities);

    $activities=array_values($activities);

    // var_dump($activities);
    $vact="<option value='0'>Select Activity</option>";

    foreach ( $activities as $act) {

        if(!empty(getVplname($act['instance']))&& getVplname($act['instance'])!="") {
            $vact .= "<option value='" . $act['id'] . "'>" . getVplname($act['instance']) . "</option>";
        }


    }

//$resultarray=array_merge($activities,$current_activities);
    return $vact;

}



function getActivitiesByTopicAndCourse($courseid,$topicid){
    global $DB;
        $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
       // $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND `section` = ".$topicid." AND `module` IN($vpl)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    //echo $activitiesSql;
    $activities=array();
    $current_activities=array();
    foreach ( $activities_obj as $act) {
        $activities[$act->id]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid." AND `module` IN($vpl)";
        $currentRes = $DB->get_records_sql($rsql);
        //echo $rsql;
        foreach ($currentRes as $item) {
            if(array_key_exists($item->id,$activities)){

            }else{
                $activities[]=array("id"=>$item->id,"module"=>$item->module,"instance"=>$item->instance);
            }

        }
    }
//var_dump($activities);

    $activities=array_values($activities);

   // var_dump($activities);
    $vact="<option value='0'>All</option>";

    foreach ( $activities as $act) {

if(!empty(getVplname($act['instance']))&& getVplname($act['instance'])!="") {
    $vact .= "<option value='" . $act['id'] . "'>" . getVplname($act['instance']) . "</option>";
}


    }

//$resultarray=array_merge($activities,$current_activities);
    return $vact;

}
function getVplname($iid){
    global $DB;
    $rsql = "SELECT name	FROM mdl_vpl
	WHERE  id = $iid";
    $currentRes = $DB->get_record_sql($rsql);
    return $currentRes->name;
}

function getLatestActivitiesByTopicAndCourse($courseid,$topicid){
    global $DB;
        $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
        $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $from=strtotime('-2 weeks');
    $to=time() + 86400;
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND completionexpected between '".$from."' AND '".$to."' AND `section` = ".$topicid." AND `module` IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    $activities=array();
    foreach ( $activities_obj as $act) {
        $activities[$act->id]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid." AND `module` IN($vpl,$quiz)";
        $currentRes = $DB->get_records_sql($rsql);
        foreach ($currentRes as $item) {
            if(array_key_exists($item->id,$activities)){

            }else{
                $activities[]=array("id"=>$item->id,"module"=>$item->module,"instance"=>$item->instance);
            }

        }
    }

    $activities=array_values($activities);

    return $activities;

}
function getActivitiesByCourse($courseid){
    global $DB;
        $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
        $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND `module` IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    $activities=array();
    foreach ( $activities_obj as $act) {
        $activities[]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }
    return $activities;

}


function getGradeByTopicAndCourse($courseid,$topicid,$catname,$topicname,$subjectname){
    $topicmeangrade=0;

    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentcount=0;
    $grade_100=0;
    $grade_lt100_80=0;
    $grade_lt80_60=0;
    $grade_lt60_40=0;
    $grade_lt40_gt0=0;
    $grade_0=0;
    $sgrade=0;

    if($topicid) {
        $activities = getActivitiesByTopicAndCourse($courseid, $topicid);
        //print_r($activities);
        foreach($students as $student){

            if($student->id){
                for($i=0;$i<count($activities);$i++){
                    $grade=getGrade($courseid,$activities[$i]['module'],$activities[$i]['instance'],$student->id);
                    $topicmeangrade=$topicmeangrade+$grade;
                    $studentcount++;
                    $sgrade=$sgrade+$grade;

                }

                $sgrade=round($sgrade/count($activities),2);
               // echo $student->id.'-'.$sgrade.'-';echo '<br/>';

                if($sgrade==0){
                    $grade_0++; $sgrade=0;
                }
                if($sgrade==100){
                    $grade_100++; $sgrade=0;
                }
                if($sgrade>=80&&$sgrade<100){
                    $grade_lt100_80++; $sgrade=0;
                }
                if($sgrade>=60&&$sgrade<80){
                    $grade_lt80_60++; $sgrade=0;
                }
                if($sgrade>=40&&$sgrade<60){
                    $grade_lt60_40++; $sgrade=0;
                }
                if($sgrade>0&&$sgrade<40){
                    $grade_lt40_gt0++; $sgrade=0;
                }


            }

        }

        echo '<tr>
                                   <!-- <td>'.$catname.'</td>-->
                                    <td>'.$subjectname.'</td>
                                    <td>'.$topicname.'</td>
                                    <td>'.round($topicmeangrade/$studentcount,2).'</td>
                                    <td>'.$grade_100.'</td>
                                    <td>'.$grade_lt100_80.'</td>
                                    <td>'.$grade_lt80_60.'</td>
                                    <td>'.$grade_lt60_40.'</td>
                                    <td>'.$grade_lt40_gt0.'</td>
                                    <td>'.$grade_0.'</td>
                                    </tr>';



    }else{//end of topic id
        $topics=getRawTopicsByCourse($courseid);
        $activities = getActivitiesByCourse($courseid);

        $topics=array_values($topics);


      //  var_dump($topics);

        for($t=0;$t<count($topics);$t++) {
            $topic=$topics[$t];
        $topicid = $topic->id;
        $topicname = $topic->name;

        if ($topicname) {// check whether the section is ready or not
            $activities = getActivitiesByTopicAndCourse($courseid, $topicid);


            foreach ($students as $student) {

                if ($student->id) {
                    for ($i = 0; $i < count($activities); $i++) {
                        $grade = getGrade($courseid, $activities[$i]['module'], $activities[$i]['instance'], $student->id);
                        $topicmeangrade = $topicmeangrade + $grade;
                        $studentcount++;
                        $sgrade = $sgrade + $grade;
                    }
                    $sgrade = round($sgrade / count($activities), 2);
                    //echo $sgrade;
                    if ($sgrade == 0) {
                        $grade_0++;
                        $sgrade = 0;
                    }
                    if ($sgrade == 100) {
                        $grade_100++;
                        $sgrade = 0;
                    }
                    if ($sgrade >= 80 && $sgrade < 100) {
                        $grade_lt100_80++;
                        $sgrade = 0;
                    }
                    if ($sgrade >= 60 && $sgrade < 80) {
                        $grade_lt80_60++;
                        $sgrade = 0;
                    }
                    if ($sgrade >= 40 && $sgrade < 60) {
                        $grade_lt60_40++;
                        $sgrade = 0;
                    }
                    if ($sgrade > 0 && $sgrade < 40) {
                        $grade_lt40_gt0++;
                        $sgrade = 0;
                    }


                }

            }

            echo '<tr>
                                  <!-- <td>'.$catname.'</td>-->
                                    <td>' . $subjectname . '</td>
                                    <td>' . $topicname . '</td>
                                    <td>' . round($topicmeangrade / $studentcount, 2) . '</td>
                                    <td>' . $grade_100 . '</td>
                                    <td>' . $grade_lt100_80 . '</td>
                                    <td>' . $grade_lt80_60 . '</td>
                                    <td>' . $grade_lt60_40 . '</td>
                                    <td>' . $grade_lt40_gt0 . '</td>
                                    <td>' . $grade_0 . '</td>
                                    </tr>';
            //re-initializing variables
            $topicmeangrade = $studentcount = $grade_100 = $grade_lt100_80 = $grade_lt80_60 = $grade_lt60_40 = $grade_lt40_gt0 = $grade_0 = 0;


        }//end checking section is ready or not
    }//end of topics array
    }//end of else

}


function getAttandanceByTopicAndCourse($courseid,$topicid,$catname,$topicname,$subjectname){
    $topicmeangrade=0;
global $DB;
    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentcount=0;
    $attandance_100=0;
    $attandance_lt100_80=0;
    $attandance_lt80_60=0;
    $attandance_lt60_40=0;
    $attandance_lt40_gt0=0;
    $attandance_0=0;
    $sgrade=0;

    if($topicid) {
        $activities = getActivitiesByTopicAndCourse($courseid, $topicid);
        //print_r($activities);
        foreach($students as $student){

            if($student->id){
                $totalactivities=0;
                $attendedactivities=0;
                for($i=0;$i<count($activities);$i++){
                    $grade=getGrade($courseid,$activities[$i]['module'],$activities[$i]['instance'],$student->id);
                    if($grade){

                        $attendedactivities++;

                    }
                    else{
                        $instance=$activities[$i]['instance'];
                        $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$student->id."'";

                        $submissions=$DB->get_fieldset_sql($tsql);

                        if( count($submissions)>0)
                        {
                            $attendedactivities++;
                        }
                    }
                    $totalactivities++;
                    $topicmeangrade=$topicmeangrade+$grade;
                    $studentcount++;
                    $sgrade=$sgrade+$grade;

                }
                //echo $student->id.'-'.$attendedactivities.'-'.$totalactivities;echo '<br/>';
                $sattandance=round($attendedactivities/$totalactivities,2);
                $sattandance=($sattandance*100);

                if($sattandance==0){
                    $attandance_0++; $sattandance=0;
                }
                if($sattandance==100){
                    $attandance_100++; $sattandance=0;
                }
                if($sattandance>=80&&$sattandance<100){
                    $attandance_lt100_80++; $sattandance=0;
                }
                if($sattandance>=60&&$sattandance<80){
                    $attandance_lt80_60++; $sattandance=0;
                }
                if($sattandance>=40&&$sattandance<60){
                    $attandance_lt60_40++; $sattandance=0;
                }
                if($sattandance>0&&$sattandance<40){
                    $attandance_lt40_gt0++; $sattandance=0;
                }


            }

        }

        echo '<tr>
                                   <!-- <td>'.$catname.'</td>-->
                                    <td>'.$subjectname.'</td>
                                    <td>'.$topicname.'</td>
                                    <td>'.round($topicmeangrade/$studentcount,2).'</td>
                                    <td>'.$attandance_100.'</td>
                                    <td>'.$attandance_lt100_80.'</td>
                                    <td>'.$attandance_lt80_60.'</td>
                                    <td>'.$attandance_lt60_40.'</td>
                                    <td>'.$attandance_lt40_gt0.'</td>
                                    <td>'.$attandance_0.'</td>
                                    </tr>';



    }else{//end of topic id
        $topics=getRawTopicsByCourse($courseid);
        //$activities = getActivitiesByCourse($courseid);

        $topics=array_values($topics);

        for($t=0;$t<count($topics);$t++) {
            $topic=$topics[$t];
            $topicid = $topic->id;
            $topicname = $topic->name;
            if ($topicname) {// check whether the section is ready or not
                $activities = getActivitiesByTopicAndCourse($courseid, $topicid);
                foreach ($students as $student) {

                    if ($student->id) {
                        $totalactivities=0;
                        $attendedactivities=0;
                        for ($i = 0; $i < count($activities); $i++) {
                            $grade = getGrade($courseid, $activities[$i]['module'], $activities[$i]['instance'], $student->id);
                            if($grade){
                                $attendedactivities++;
                            }else{

                                $instance=$activities[$i]['instance'];
                                $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$student->id."'";

                                $submissions=$DB->get_fieldset_sql($tsql);

                                if( count($submissions)>0)
                                {
                                    $attendedactivities++;
                                }
                            }
                            $totalactivities++;
                            $topicmeangrade = $topicmeangrade + $grade;
                            $studentcount++;
                            $sgrade = $sgrade + $grade;
                        }
                        $sattandance=round($attendedactivities/$totalactivities,2);
                        $sattandance=($sattandance*100);
                        // echo $student->id.'-'.$sgrade.'-';echo '<br/>';
                        if($sattandance==0){
                            $attandance_0++; $sattandance=0;
                        }
                        if($sattandance==100){
                            $attandance_100++; $sattandance=0;
                        }
                        if($sattandance>=80&&$sattandance<100){
                            $attandance_lt100_80++; $sattandance=0;
                        }
                        if($sattandance>=60&&$sattandance<80){
                            $attandance_lt80_60++; $sattandance=0;
                        }
                        if($sattandance>=40&&$sattandance<60){
                            $attandance_lt60_40++; $sattandance=0;
                        }
                        if($sattandance>0&&$sattandance<40){
                            $attandance_lt40_gt0++; $sattandance=0;
                        }


                    }

                }

                echo '<tr>
                                  <!-- <td>'.$catname.'</td>-->
                                    <td>' . $subjectname . '</td>
                                    <td>' . $topicname . '</td>
                                    <td>' . round($topicmeangrade / $studentcount, 2) . '</td>
                                    <td>' . $attandance_100 . '</td>
                                    <td>' . $attandance_lt100_80 . '</td>
                                    <td>' . $attandance_lt80_60 . '</td>
                                    <td>' . $attandance_lt60_40 . '</td>
                                    <td>' . $attandance_lt40_gt0 . '</td>
                                    <td>' . $attandance_0 . '</td>
                                    </tr>';
                //re-initializing variables
                $topicmeangrade = $studentcount = $attandance_100 = $attandance_lt100_80 = $attandance_lt80_60 = $attandance_lt60_40 = $attandance_lt40_gt0 = $attandance_0 =$totalactivities=$attendedactivities=0;


            }//end checking section is ready or not
        }//end of topics array
    }//end of else

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


function getLastTwoWeekPerformanceOfTeacher(){
    global $USER,$DB;
    $categories=getTeacherEnrolledCategories();
    $html='';
    $resulthtml='';
    $labscount=0;
    $labsaverage=0;
    $quizcount=0;
    $quizaverage=0;
    $allstudentslabavg=0;
    $allstudentsquizavg=0;
    $lgrade_100=0;
    $lgrade_lt100_80=0;
    $lgrade_lt80_60=0;
    $lgrade_lt60_40=0;
    $lgrade_lt40_gt0=0;
    $lgrade_0=0;
    $grade_100=0;
    $grade_lt100_80=0;
    $grade_lt80_60=0;
    $grade_lt60_40=0;
    $grade_lt40_gt0=0;
    $grade_0=0;
    $numberofrows=0;
    $catarray=array();$catnamesarray=array();
    for($i=0;$i<count($categories);$i++){
        $catarray[]=$categories[$i]['catid'];
        $catnamesarray[$categories[$i]['catid']]=$categories[$i]['catname'];
    }

    $allEnrolledCourses = enrol_get_users_courses($USER->id);

    $allEnrolledCourses=array_values($allEnrolledCourses);




     for($i=0;$i<count($allEnrolledCourses);$i++){




       /* echo count($allEnrolledCourses);
        echo $allEnrolledCourses[$i]->category;
        echo '--';
        echo $catarray;
        echo '<br/>';*/

       if(in_array($allEnrolledCourses[$i]->category,$catarray)){
           $courseid=$allEnrolledCourses[$i]->id;

           $labdiv=$quizdiv='';
            $topics=getRawTopicsByCourse($courseid);
            $catname=$catnamesarray[$allEnrolledCourses[$i]->category];
            $subjectname=$allEnrolledCourses[$i]->fullname;
           $context = context_course::instance($courseid);
            $students = get_role_users(5 , $context);//getting all the students from a course level
           $topics=array_values($topics);

           for($t=0;$t<count($topics);$t++) {
               $topic=$topics[$t];
                $topicname = $topic->name;

                   if ($topicname) {// check whether the section is ready or not
                      // echo $topicname;
                     //  echo '<br/>';
                    $topicmeangrade=0;
                   $studentcount=0;

                   $sgrade=0;

                   $topicid = $topic->id;

                       $activities = getLatestActivitiesByTopicAndCourse($courseid, $topicid);
                      // echo $topicname;
                       //print_r($activities);
                       //echo '<br/>';
                       foreach ($students as $student) {

                           if ($student->id) {
                               $studentcount++;
                               for ($acount = 0; $acount < count($activities); $acount++) {
                                   $sql_item="SELECT name FROM mdl_modules WHERE id ='".$activities[$acount]['module']."'";

                                   $item_res=$DB->get_record_sql($sql_item);

                                   $itemname= $item_res->name;
                                   $grade = getGrade($courseid, $activities[$acount]['module'], $activities[$acount]['instance'], $student->id);
                                   $topicmeangrade = $topicmeangrade + $grade;

                                   $sgrade = $sgrade + $grade;
                                   if($itemname=='vpl'){
                                        $labscount++;
                                       $labsaverage=$labsaverage+$grade;
                                   }
                                   if($itemname=='quiz'){
                                       $quizcount++;
                                       $quizaverage=$quizaverage+$grade;
                                   }
                               }
                               $sgrade = round($sgrade / count($activities), 2);

                               $labsaverage=round($labsaverage/$labscount,2);
                               $quizaverage=round($quizaverage/$quizcount,2);




                             //  echo $sgrade;
                               if ($labsaverage == 0) {
                                   $lgrade_0++;

                               }
                               if ($labsaverage == 100) {
                                   $lgrade_100++;
                                   $sgrade = 0;
                               }
                               if ($labsaverage >= 80 && $labsaverage < 100) {
                                   $lgrade_lt100_80++;
                                   $sgrade = 0;
                               }
                               if ($labsaverage >= 60 && $labsaverage < 80) {
                                   $lgrade_lt80_60++;
                                   $sgrade = 0;
                               }
                               if ($labsaverage >= 40 && $labsaverage < 60) {
                                   $lgrade_lt60_40++;
                                   $sgrade = 0;
                               }
                               if ($labsaverage > 0 && $labsaverage < 40) {
                                   $lgrade_lt40_gt0++;
                                   $sgrade = 0;
                               }


                               if ($quizaverage == 0) {
                                   $grade_0++;
                                   $sgrade = 0;
                               }
                               if ($quizaverage == 100) {
                                   $grade_100++;
                                   $sgrade = 0;
                               }
                               if ($quizaverage >= 80 && $quizaverage < 100) {
                                   $grade_lt100_80++;
                                   $sgrade = 0;
                               }
                               if ($quizaverage >= 60 && $quizaverage < 80) {
                                   $grade_lt80_60++;
                                   $sgrade = 0;
                               }
                               if ($quizaverage >= 40 && $quizaverage < 60) {
                                   $grade_lt60_40++;
                                   $sgrade = 0;
                               }
                               if ($quizaverage > 0 && $quizaverage < 40) {
                                   $grade_lt40_gt0++;
                                   $sgrade = 0;
                               }

                               $allstudentslabavg=$allstudentslabavg+$labsaverage;
                               $allstudentsquizavg=$allstudentsquizavg+$quizaverage;

                               $totallabcount=$labscount;
                               $totalquizcount=$quizcount;

                               $labscount=0;
                               $labsaverage=0;
                               $quizcount=0;
                               $quizaverage=0;

                           }//checking student id

                       }//end of student loop

                       $allstudentslabavg=round($allstudentslabavg/$studentcount,2);
                       $allstudentsquizavg=round($allstudentsquizavg/$studentcount,2);

                       $html.= '<tr>
                                       <td>' . getDayFormat($topic->startdatetime) . '</td>
                                       <td>' . $subjectname . '</td>
                                       <td>' . $topicname . '</td>
                                       <td>' .$totallabcount . '</td>
                                       <td>' . $allstudentslabavg . '</td>
                                       <td>' . $totalquizcount . '</td>
                                       <td>' . $allstudentsquizavg . '</td>
                                       </tr>';
                       $numberofrows++;
                       $resulthtml.= $html;
                       $html='';

                       $totalaverageMean=round(($allstudentslabavg+$allstudentsquizavg)/2,2);

                       $datesVsAvgs[]=array("date"=>$topic->startdatetime,"subject"=>$subjectname,'meangrade'=>$totalaverageMean);
                       //re-initializing variables
                       $topicmeangrade = $studentcount = 0;

// $grade_100 = $grade_lt100_80 = $grade_lt80_60 = $grade_lt60_40 = $grade_lt40_gt0 = $grade_0
                   }//end checking section is ready or not


               }//end of topics iteration


        }//end of checking category id in catarray

    }//end of enrolled courses loop

   // $labdiv='100='.$lgrade_100.'--grade0='.$lgrade_0.'--gradelt100_80='.$lgrade_lt100_80.'--grade_lt80_60='.$lgrade_lt80_60.'--grade_lt60_40='.$lgrade_lt60_40.'--grade_lt40_gt0='.$lgrade_lt40_gt0;

 //   $quizdiv='100='.$grade_100.'--grade0='.$grade_0.'--gradelt100_80='.$grade_lt100_80.'--grade_lt80_60='.$grade_lt80_60.'--grade_lt60_40='.$grade_lt60_40.'--grade_lt40_gt0='.$grade_lt40_gt0;


   // echo $labdiv;
    //echo '<br/>';
    //echo $quizdiv;

    $resutlArray=array("rhtml"=>$resulthtml,"graphresult"=>$datesVsAvgs,"rowcount"=>$numberofrows);
return $resutlArray;
}


function getTeacherEnrolledCourses(){
    global $USER;
    //$categories=getTeacherEnrolledCategories();
$displayFlag=0;
    $allEnrolledCourses = enrol_get_users_courses($USER->id);
    $allEnrolledCourses=array_values($allEnrolledCourses);

    $courses=array();
    for($i=0;$i<count($allEnrolledCourses);$i++) {
        $subjectname = $allEnrolledCourses[$i]->fullname;
        $subjectid = $allEnrolledCourses[$i]->id;
        $courses[]=array("id"=>$subjectid,"name"=>$subjectname);
    }

    return $courses;
}
//echo getAllStudentsPerformance(40,'java');
        function getAllStudentsPerformance($courseid,$subject,$grade,$attandance,$fname,$lname){
            global $CFG;
        $html='';


           /* <option value="0">All</option>
                                    <option value="1">100</option>
                                    <option value="2"><100 and >=80</option>
                                    <option value="3"><80 and >=60</option>
                                    <option value="4"><60 and >=40</option>
                                    <option value="5"><40 and >0</option>
                                    <option value="6">0</option>*/


            if($courseid){
                $context = context_course::instance($courseid);
                $students = get_role_users(5 , $context);//getting all the students from a course level

                foreach($students as $student){
                    //$userobj = get_complete_user_data(id, $student->id);
                    if(!empty($lname)&&!empty($fname)){
                        if ((strpos(strtolower($student->firstname),strtolower($fname)) !== false)&&(strpos(strtolower($student->lastname),strtolower($lname)) !== false)) {
                            $displayFlag=1;
                        }
                    }
                    else if(!empty($fname)){
                        if (strpos(strtolower($student->firstname),strtolower($fname)) !== false) {
                            $displayFlag=1;
                        }
                    }
                    else if(!empty($lname)){
                        if (strpos(strtolower($student->lastname),strtolower($lname)) !== false) {
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
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        } else if ($attandance == 0) {
                            if ($grade == 0) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }

                        if ($grade == 6) {
                            if (($labaverage == 0) || ($quizaverage == 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 1) {
                            if (($labaverage == 100) || ($quizaverage == 100)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank" href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 2) {

                            if (($labaverage < 100) && ($labaverage >= 80) || ($quizaverage < 100) && ($quizaverage >= 80)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 3) {

                            if (($labaverage < 80) && ($labaverage >= 60) || ($quizaverage < 80) && ($quizaverage >= 60)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 4) {

                            if (($labaverage < 60) && ($labaverage >= 40) || ($quizaverage < 60) && ($quizaverage >= 40)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($grade == 5) {

                            if (($labaverage < 40) && ($labaverage > 0) || ($quizaverage < 40) && ($quizaverage > 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }

                        if ($attandance == 1) {
                            if ($avg_attandance == 100) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 2) {
                            if (($avg_attandance < 100) && ($avg_attandance >= 80)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 3) {
                            if (($avg_attandance < 80) && ($avg_attandance >= 60)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 4) {
                            if (($avg_attandance < 60) && ($avg_attandance >= 40)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 5) {
                            if (($avg_attandance < 40) && ($avg_attandance > 0)) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
                                    <td >' . $labaverage . '</td>
                                    <td >' . $quizaverage . '</td>
                                    <td >' . $avg_attandance . '</td></tr>';
                            }
                        }
                        if ($attandance == 6) {
                            if ($avg_attandance == 0) {
                                $html .= '<tr><td>' . $subject . '</td>
                                    <td ><a target="_blank"  href="'.$CFG->wwwroot.'/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">' . $student->firstname . '</a></td>
                                    <td >' . $student->lastname . '</td>
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
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT * FROM `mdl_course_modules` WHERE  `ID` IN (".implode(',',$startedActivityIds).") AND course = '".$courseid."' AND module IN($vpl,$quiz)";
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

    }

    $format="['x'$subjectnames],"."['student meangrade'$meangrade],"."['class meangrade'$classmean],";
    /*['x', 'aa', '123', '123dfg', '#$%'],
                ['student meangrade', 30, 20, 50, 40, 60, 50],
                ['class meangrade', 130, 120, 150, 140, 160, 150],*/

    return $format;

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
    WHERE cm.course = '".$courseid."' AND  cm.completionexpected between '".$from."' AND '".$to."' AND cm.module IN($vpl,$quiz) ORDER BY cm.completionexpected ASC";



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
	WHERE  cm.id IN (".implode(',',$startedActivityIds).") AND cm.course = '".$courseid."' AND cm.module IN($vpl,$quiz)";

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

//print_r($activities);
$activities=array_sort($activities, 'completeddate', SORT_ASC);
    return array("activities"=>$activities);


}

function array_sort($array, $on, $order=SORT_ASC){

    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
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
	ksort($resultarray);
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
    $finalresult=array("grade"=>'["meangrade"'.$meangrades.']',"dates"=>'['.$dates.']');
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
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
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



/******************** today reports code started here *******************/


function get_student_sections_by_course($courseid){
    global $DB;
    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $stuarr=array();$stcnt=0;
    foreach($students as $student){

        $stu_section=getStudentData($student->id,$sectionfield);

        if($stu_section){
            $stuarr[$stcnt++]=array('stusec'=>$stu_section,'stid'=>$student->id);
        }
    }
    $ss=array_unique(array_column($stuarr, 'stusec'));
    sort($ss);
    //var_dump($ss);

    $html="<option value='0'>All</option>";
    for($i=0;$i<count($ss);$i++){
        $html=$html."<option value='".$ss[$i]."'>".$ss[$i];

    }
    return $html;
}

function getGradeByActivity($studentId,$courseid,$module,$instance,$vpl){

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

    /***logic to count total activities and attempted activities***/
    if($module==$vpl){

        if($grade)
        {
        }else{

            $tsql="SELECT  datesubmitted
                        FROM mdl_vpl_submissions
                        WHERE vpl ='".$instance."'
                        AND userid ='".$studentId."'";

            $submissions=$DB->get_fieldset_sql($tsql);


            if( count($submissions)>0)
            {
                return -1;
            }
        }

    }
    return $grade;
}
function get_students_activity_info($courseid,$topicid,$stdsection)
{
    global $DB;
    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));
$phonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'studentcontactno'));
    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    //var_dump($students);

    $vpl = $DB->get_field('modules', 'id', array('name' => 'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $html='<thead>
                                <tr>
                                   <!-- <th class="header" style="text-align:center">Course</th>-->
                                    <th class="header">Roll No</th>
                                    <th class="header">Name</th>
                                    <th class="header">Section</th>
                                    <th class="header">Phone</th>
                                    <th class="header">Email</th>
                                    <th class="header">Online Session</th>
                                    <th class="header">Total</th>
                                    <th style="text-align:center" class="header">Graded</th>
                                    ';

    $activities=getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz);
	//var_dump($activities);
    $labcount=0;$qcount=0;
    for($i=0;$i<count($activities);$i++){
        if($vpl==$activities[$i]['module']) {
            $labcount++;
            $html .= '<th class="header">Lab' . $labcount . '</th>';
        }
        if($quiz==$activities[$i]['module']){
            $qcount++;
            $html.='<th class="header">quiz'.$qcount.'</th>';
        }
        $curdate=$activities[$i]['startdate'];
    }
    //var_dump($activities);
    $html.='</tr></thead><tbody class="grade-info">';
    $showall=false;$recordsCount=0;
    if(!$stdsection){$showall=true;}
    foreach($students as $student) {
       // $userobj = get_complete_user_data(id, $student->id);
       // $rollno=get_complete_user_data(id,$student->id)->profile['rollno'];
       // $section=get_complete_user_data(id,$student->id)->profile['section'];
       // $parentno=get_complete_user_data(id,$student->id)->profile['parentcontactno'];

        $rollno=$student->username;//getStudentData($student->id,$rollfield);
        $section=getStudentData($student->id,$sectionfield);
        $parentno=getStudentData($student->id,$parentphonefield);
        $phone=getStudentData($student->id,$parentphonefield);
	$email=$student->email;

        $name=$student->firstname.' '.$student->lastname;

        if(($section==$stdsection)||$showall){
            $totalattempted=0;
            $html .= '<tr><td>' . $rollno . '</td>
                       <td class="stdname"><span>'.$name.'</span><input type="hidden" class="parentno" value="'.$parentno.'" /></td>';
            $acts=$totalattacts='';
            for($i=0;$i<count($activities);$i++) {
                $grade = getGradeByActivity($student->id, $courseid, $activities[$i]['module'], $activities[$i]['instance'], $vpl);

                if(($grade==-1)||($grade==NULL)){$grade='--';}else if($grade){$totalattempted++;}
                $acts.='<td>' . (($grade!='--')?round($grade,2):$grade) . '</td>';
                //var_dump($grade);
            }//end of activities
            $total='<td>' . count($activities) . '</td>';
            $totalattacts='<td>' . $totalattempted . '</td>';
            $html.='<td>' . $section . '</td>';
            $html.='<td>' . $phone . '</td>';
            $html.='<td>' . $email . '</td>';
            $onlineatt=getOnlineAtt($rollno,$curdate);
            $html.='<td>' . $onlineatt.'</td>';
            $html.=$total.$totalattacts.$acts;
            $html.='</tr>';
            $recordsCount++;
        }//end of student section check

    }//end of student object

    $html.='</tbody>';

    return $html.'<p id="records">'.$recordsCount.'</p>';

}

function get_students_activity_info_report($courseid,$topicid,$stdsection)
{
    global $DB;
    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $vpl = $DB->get_field('modules', 'id', array('name' => 'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $html='<thead>
                                <tr>
                                   <!-- <th class="header" style="text-align:center">Course</th>-->
                                    <th class="header">Roll No</th>
                                    <th class="header">Name</th>
                                    <th class="header">ParentNo</th>
                                    <th class="header">Section</th>
                                    <th class="header">Online Session</th>
                                    <th class="header">Total</th>
                                    <th style="text-align:center" class="header">Graded</th>
                                    ';

    $activities=getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz);
    $labcount=0;$qcount=0;
    for($i=0;$i<count($activities);$i++){
        if($vpl==$activities[$i]['module']) {
            $labcount++;
            $html .= '<th class="header">Lab' . $labcount . '</th>';
        }
        if($quiz==$activities[$i]['module']){
            $qcount++;
            $html.='<th class="header">quiz'.$qcount.'</th>';
        }
        $curdate=$activities[$i]['startdate'];
    }
    //var_dump($activities);
    $html.='</tr></thead><tbody class="grade-info">';
    $showall=false;
    if(!$stdsection){$showall=true;}
    foreach($students as $student) {
       // $userobj = get_complete_user_data(id, $student->id);
       // $rollno=get_complete_user_data(id,$student->id)->profile['rollno'];
       // $section=get_complete_user_data(id,$student->id)->profile['section'];
      //  $parentno=get_complete_user_data(id,$student->id)->profile['parentcontactno'];

      //  $name=$userobj->firstname.' '.$userobj->lastname;

        $rollno=$student->username;//getStudentData($student->id,$rollfield);
        $section=getStudentData($student->id,$sectionfield);
        $parentno=getStudentData($student->id,$parentphonefield);

        $name=$student->firstname.' '.$student->lastname;

        if(($section==$stdsection)||$showall){
            $totalattempted=0;
            $html .= '<tr><td>' . $rollno . '</td>
                       <td class="stdname"><span>'.$name.'</span></td><td>'.$parentno.'</td>';
            $acts=$totalattacts='';
            for($i=0;$i<count($activities);$i++) {
                $grade = getGradeByActivity($student->id, $courseid, $activities[$i]['module'], $activities[$i]['instance'], $vpl);

                if(($grade==-1)||($grade==NULL)){$grade='--';}else if($grade){$totalattempted++;}
                $acts.='<td>' . (($grade!='--')?round($grade,2):$grade) . '</td>';
                //var_dump($grade);
            }//end of activities
            $total='<td>' . count($activities) . '</td>';
            $totalattacts='<td>' . $totalattempted . '</td>';
            $html.='<td>' . $section . '</td>';
            $onlineatt=getOnlineAtt($rollno,$curdate);
            $html.='<td>' . $onlineatt.'</td>';
            $html.=$total.$totalattacts.$acts;
            $html.='</tr>';

        }//end of student section check

    }//end of student object

    $html.='</tbody>';

    return $html;

}

function getOnlineAtt($stdroll,$curdate){
    global $DB;

    $sql = "select * FROM mdl_webinar_attendance where  curdate='".$curdate."'";

    $result = $DB->get_records_sql($sql,null);
    if($result){
        $sql = "select attendance FROM mdl_webinar_attendance where rollno='".$stdroll."'  and curdate='".$curdate."'";

        $result1 = $DB->get_record_sql($sql,null);
        if($result1){
            if($result1->attendance){
                return 'PRESENT';
            }else{
                return 'ABSENT';
            }

        }else{
            return '--';
        }
    }else{
        return '--';
    }


}
function get_students_activity_summary($courseid,$topicid,$stdsection,$topicname)
{
    global $DB;
    $context = context_course::instance($courseid);
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $vpl = $DB->get_field('modules', 'id', array('name' => 'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $html='<thead>
                                <tr>
                                   <!-- <th class="header" style="text-align:center">Course</th>-->
                                    <th class="header">Topic</th>
                                    <th class="header">Section</th>
                                    ';
    $activities=getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz);
    $labcount=0;$qcount=0;$count=count($activities);
    $activitiesarray=array();$temp=$count;
    for($i=0;$i<=count($activities);$i++){

        $html .= '<th class="header">' . $temp.' of '.$count . '</th>';

        $temp--;
    }

    $html.='</tr></thead><tbody class="grade-info">';
    $showall=false;

    if(!$stdsection){$showall=true;}
    foreach($students as $student) {


        $section=getStudentData($student->id,$sectionfield);
        //$section=get_complete_user_data('id',$student->id)->profile['section'];


        if(($section==$stdsection)||$showall){
            $totalattempted=0;

            $acts=$totalattacts='';
            for($i=0;$i<count($activities);$i++) {
                $grade = getGradeByActivity($student->id, $courseid, $activities[$i]['module'], $activities[$i]['instance'], $vpl);

                if(($grade==-1)||($grade==NULL)){$grade='--';}else if($grade){$totalattempted++;}
                $acts.='<td>' . (($grade!='--')?round($grade,2):$grade) . '</td>';
                //var_dump($grade);
            }//end of activities
            //echo $student->id.'-'.$totalattempted.'<br/>';
            $activitiesarray[]=$totalattempted;

        }//end of student section check

    }//end of student object

    $resultarray=array_count_values($activitiesarray);
    //var_dump($resultarray);
    ksort($resultarray);
    //echo $count.'-'.count($resultarray);

    for($i=0;$i<count($resultarray);$i++){

        if(array_key_exists($i,$resultarray)){

        }else{
            $resultarray[$i]='--';
        }
    }
    //$resultarray[$i]='--';
    //var_dump($resultarray);
    //var_dump("act count-".$count);
    //var_dump("arraysize-".count($resultarray));
    ksort($resultarray);
    //var_dump($resultarray);
    //var_dump(count($resultarray)==($count+1));
    if(count($resultarray)==($count+1)){
        $summaryarray=$resultarray;
    }else{
        $remain=array_fill(count($resultarray), ($count-count($resultarray)+1), '--');
        $summaryarray=(array_merge($resultarray,$remain));
    }
    // var_dump($summaryarray);
    krsort($summaryarray);
    if(!$stdsection){$stdsection="All";}
    $html .= '<tr>';
    $html .= '<td >'.$topicname.'</td>';
    $html .= '<td >'.$stdsection.'</td>';
    for($i=count($summaryarray)-1;$i>=0;$i--){
        $html .= '<td >' . $summaryarray[$i] . '</td>';
    }
    $html.='</tr>';
    $html.='</tbody>';

    return $html;

}

function getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz)
{
    global $DB;

    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = " . $courseid . " AND `section` = " . $topicid . " AND `module` IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql($activitiesSql);
    //echo $activitiesSql;
    $activities = array();
    $current_activities = array();
    foreach ($activities_obj as $act) {

        $activities[$act->id] = array("id" => $act->id, "module" => $act->module, "instance" => $act->instance);
    }
    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes = $DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds = array();
    foreach ($startedActivitiesRes as $item) {
        $startedActivityIds[] = $item->activityid;
        if($item->activity_start_time){
            $startdate=$item->activity_start_time;
        }

    }
    $startdate=date("y-m-d",$startdate);
    // echo $startdate;
    $activitieswithdate=array();
    if (count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid . " AND `module` IN($vpl,$quiz)";
        $currentRes = $DB->get_records_sql($rsql);
        foreach ($currentRes as $item) {
            if (array_key_exists($item->id, $activities)) {
                $activitieswithdate[]= array("id" => $item->id, "module" => $item->module, "instance" => $item->instance,"startdate"=>$startdate);
            } else {
                $activitieswithdate[] = array("id" => $item->id, "module" => $item->module, "instance" => $item->instance,"startdate"=>$startdate);
            }

        }
    }
    $activities = array_values($activitieswithdate);

    return $activities;

}


/******************** today reports code ended here *******************/


// $weekflag , if one week then value is 1, if it is Cumulative then value is 2
//if $typeflag = 1 means top 50, $typeflag = 2 means bottom 50
function getAllStudentsPerformanceByWeek($courseid,$typeflag){

    global $DB,$CFG;

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
   // $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

    if($courseid) {
        $context = context_course::instance($courseid);
        $students = get_role_users(5, $context);//getting all the students from a course level
        $weekflag=1;
        $html='';
        $allstudents=array();
        foreach ($students as $student) {



                $rollno=$student->username;//getStudentData($student->id,$rollfield);
                $stu_section=getStudentData($student->id,$sectionfield);



            //$userobj = get_complete_user_data(id, $student->id);
           // $stu_section=get_complete_user_data(id,$student->id)->profile['section'];
           // $rollno=get_complete_user_data(id,$student->id)->profile['rollno'];
            $grade=getCurrentWeekPerformanceOfUser($courseid,$student->id,$weekflag);
            $attendance=getAttendanceOfOnlineSessions($rollno,$weekflag);
            $allstudents[]=array('rollno'=>$rollno,
                'name'=>$student->firstname.' '.$student->lastname,
                'section'=>$stu_section,
                'grade'=>$grade,
                'attendance'=>$attendance);
        }
        $grades = array();
        foreach ($allstudents as $key => $row)
        {
            $grades[$key] = $row['grade'];
        }
        switch($typeflag){
            case 1:array_multisort($grades, SORT_DESC, $allstudents);break;
            case 2:array_multisort($grades, SORT_ASC, $allstudents);break;
        }

        $max=(count($allstudents)>50)?50:count($allstudents);
        for($i=0;$i<$max;$i++){
            $html.='<tr>
                        <td>'.$allstudents[$i]['rollno'].'</td>
                        <td class="stdname">'.$allstudents[$i]['name'].'</td>
                        <td>'.$allstudents[$i]['section'].'</td>
                        <td>'.$allstudents[$i]['grade'].'</td>
                        <td>'.$allstudents[$i]['attendance'].'</td>
                        </tr>';
        }
    }
    echo $html;
}
//if $typeflag = 1 means top 50, $typeflag = 2 means bottom 50
function getAllStudentsPerformanceCumulative($courseid,$typeflag){
    global $DB,$CFG;

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

    if($courseid) {
        $context = context_course::instance($courseid);
        $students = get_role_users(5, $context);//getting all the students from a course level
        $weekflag=2;
        $html='';
        $allstudents=array();
        foreach ($students as $student) {


                $rollno=$student->username;//getStudentData($student->id,$rollfield);
                $stu_section=getStudentData($student->id,$sectionfield);



            //$userobj = get_complete_user_data(id, $student->id);
          //  $stu_section=get_complete_user_data(id,$student->id)->profile['section'];
           // $rollno=get_complete_user_data(id,$student->id)->profile['rollno'];
            $grade=getCurrentWeekPerformanceOfUser($courseid,$student->id,$weekflag);
            $attendance=getAttendanceOfOnlineSessions($rollno,$weekflag);
            $allstudents[]=array('rollno'=>$rollno,
                                 'name'=>$student->firstname.' '.$student->lastname,
                                'section'=>$stu_section,
                                'grade'=>$grade,
                                'attendance'=>$attendance);


        }
        $grades = array();
        foreach ($allstudents as $key => $row)
        {
            $grades[$key] = $row['grade'];
        }
        switch($typeflag){
            case 1:array_multisort($grades, SORT_DESC, $allstudents);break;
            case 2:array_multisort($grades, SORT_ASC, $allstudents);break;
        }
        $max=(count($allstudents)>50)?50:count($allstudents);
        for($i=0;$i<$max;$i++){
            $html.='<tr>
                        <td>'.$allstudents[$i]['rollno'].'</td>
                        <td class="stdname">'.$allstudents[$i]['name'].'</td>
                        <td>'.$allstudents[$i]['section'].'</td>
                        <td>'.$allstudents[$i]['grade'].'</td>
                        <td>'.$allstudents[$i]['attendance'].'</td>
                        </tr>';
        }
    }
    echo $html;
}

function getThisWeekDateRanges(){
    $monday = strtotime("last monday");
    $monday = date('w', $monday)==date('w') ? $monday+5*86400 : $monday;

    $friday = strtotime(date("Y-m-d",$monday)." +4 days");

    $date_range=array();
    $date_range['this_week_sd']=strtotime(date("y-m-d",$monday));
    $date_range['this_week_ed']=strtotime(date("y-m-d",$friday));
    return $date_range;
}
function getThisWeekDateList(){
    $monday = strtotime("last monday");
    $date_list=array();
    $monday = date('w', $monday)==date('w') ? $monday+5*86400 : $monday;
    for($i=0;$i<=4;$i++){
        $date_list[]=date("y-m-d",strtotime(date("Y-m-d",$monday)." +$i days"));
    }
    return $date_list;
}

// $weekflag , if one week then value is 1, if it is Cumulative then value is 2
function getAttendanceOfOnlineSessions($htno,$weekflag){
    global $DB;
    switch($weekflag){
        case 1:
            $date_list=getThisWeekDateList();
            $sql="SELECT COUNT( DISTINCT `curdate` ) AS TOTALCLASSES FROM `mdl_webinar_attendance` where curdate IN (".'"'.implode('","',$date_list).'"'.") ";
            $res=$DB->get_record_sql($sql);
            $totalClasses= $res->totalclasses;

            $sql="SELECT COUNT( * ) AS ATTENDEDCLASSES FROM `mdl_webinar_attendance` where curdate IN (".'"'.implode('","',$date_list).'"'.") AND rollno='".$htno."' AND attendance=1";

            $res=$DB->get_record_sql($sql);
            $attendedClasses= $res->attendedclasses;

            $attendance = round(($attendedClasses/$totalClasses)*100,2);
        break;
        case 2:

            $sql="SELECT COUNT( DISTINCT `curdate` ) AS TOTALCLASSES FROM `mdl_webinar_attendance` ";
            $res=$DB->get_record_sql($sql);
            $totalClasses= $res->totalclasses;

            $sql="SELECT COUNT( * ) AS ATTENDEDCLASSES FROM `mdl_webinar_attendance` where rollno='".$htno."'  AND attendance=1";

            $res=$DB->get_record_sql($sql);
            $attendedClasses= $res->attendedclasses;

            $attendance = round(($attendedClasses/$totalClasses)*100,2);
        break;
    }
    return $attendance;

}


function getCurrentWeekPerformanceOfUser($courseid,$studentId,$weekflag){

    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $daterange=getThisWeekDateRanges();
    $from=$daterange['this_week_sd'];
    $to=$daterange['this_week_ed'];

    switch($weekflag) {
        case 1:
            $sql="SELECT *
            FROM mdl_course_modules
            WHERE course = '".$courseid."' AND  completionexpected between '".$from."' AND '".$to."'  AND module IN($vpl,$quiz)";
            $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0) AND (`activity_start_time`  between '".$from."' AND '".$to."') OR (`activity_stop_time`  between '".$from."' AND '".$to."')";
            break;
        case 2:
            $sql="SELECT *
            FROM mdl_course_modules
            WHERE course = '".$courseid."' AND  completionexpected >0  AND module IN($vpl,$quiz)";
            $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0) ";
            break;

    }
   /* if($studentId==11){
        echo $sql;
        echo '<br/>';
        echo $startedActivitiesSql;
    }*/
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

    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT * FROM `mdl_course_modules` WHERE  `ID` IN (".implode(',',$startedActivityIds).") AND course = '".$courseid."' AND module IN($vpl,$quiz)";
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

                }
            }
            if($module==$quiz){$totalQuizCount++;if($grade){$quizaverage=$quizaverage+$grade;$totalQuizAttemptedCount++;}}

            $totalgrade=$totalgrade+$grade;

        }
    }//end of if (current activities check)



    if($totalgrade>0)
    {
        $meangrade=$totalgrade/$items_completed_today;
    }
    //echo $totalLabsCount.'-'.$totalQuizCount;
    //echo '<br/>';
    return round($meangrade,2);


}



function getStudentData($userid,$fieldid){
    global $DB;
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;
    return $studata;
}



function getCPCAttendanceReportDaily($courseid)
{
    global $DB;

    $html='';
    if((date("D",time())=='Tue')||(date("D",time())=='Thu')||(date("D",time())=='Fri')||(date("D",time())=='Sun')){
        $html.='<tr><td colspan="3">No CPC Class Today</td></tr>';
        echo $html;
        exit(0);
    }

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));

    $context = context_course::instance($courseid);


        $students = get_role_users(5 , $context);//getting all the students from a course level
        $studentArray=array();

        foreach($students as $student) {

            $rollno=$student->username;//getStudentData($student->id,$rollfield);

            $cpcatt=getCPCAtt($student->id);
            if($cpcatt){
                $att="PRESENT";
            }else{
                $att="ABSENT";
            }
            $html.='<tr>
                        <td>'.$rollno.'</td>
                        <td class="stdname">'.$student->firstname.''.$student->lastname.'</td>
                        <td>'.$att.'</td>
                        </tr>';
           // $studentArray[]=array('rollno'=>$rollno,'attendance'=>$cpcatt,'parentphone'=>$parentno);

        }//end of student object

    if($html){
        echo $html;
    }else{
        $html.='<tr><td colspan="3">No Records Found</td></tr>';
        echo $html;
    }

        //var_dump($studentArray);



}

function getCPCAtt($userid){
    global $DB;
    if(date("D",time())=='Sat'){
        $slot_end="17.30";//"17.00";
        $from = strtotime("13.00");//strtotime("13.00");
    }else{
        $slot_end="12.30";
        $from = strtotime("9.00");
    }

    $to = strtotime($slot_end);
    $sql="SELECT `currentlogin` FROM `mdl_user` WHERE `id` ='".$userid."'";
    $fielddata=$DB->get_record_sql($sql);
    $currentlogin=$fielddata->currentlogin;

    if(($currentlogin<$to)&&($currentlogin>$from)){
        return 1;
    }else{
        return 0;
    }
}


function getBottomStudentsDaily($courseid)
{
    global $DB;
    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

    $context = context_course::instance($courseid);
    $topicid=getTodayFSTopicId($courseid);
    //echo 'topicid-'.$topicid;
    if($topicid){
    $curdate=date('y-m-d',time());
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentArray=array();
    $vpl = $DB->get_field('modules', 'id', array('name' => 'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $activities=getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz);
    $stdcount=0;
    foreach($students as $student) {


        $rollno=$student->username;//getStudentData($student->id,$rollfield);
        $parentno=getStudentData($student->id,$parentphonefield);

            $totalattempted=0;
            $acts=$totalattacts='';
            for($i=0;$i<count($activities);$i++) {
                $grade = getGradeByActivity($student->id, $courseid, $activities[$i]['module'], $activities[$i]['instance'], $vpl);

                if(($grade==-1)||($grade==NULL)){$grade='--';}else if($grade){$totalattempted++;}

            }//end of activities
            $total=count($activities);
            $totalattacts=$totalattempted;
            $onlineatt=getOnlineAtt($rollno,$curdate);
            if(($totalattacts==0)&&($total!=0)&&($onlineatt=='ABSENT')){
                if($stdcount<25){
                    $studentArray[]=array('rollno'=>$rollno,'parentphone'=>$parentno);
                }
                $stdcount++;
            }

    }//end of student object
        return $studentArray;

    }else{
        return array();
    }

}


function getTodayFSTopicId($todayCourse){
    global $DB;

    $slot_end="17.00";
    $from = strtotime("14.30");
    $to = strtotime($slot_end);
    $courseTime=getCourseTimings($todayCourse,date("D",time()));
    if($courseTime){
        $from=strtotime($courseTime[0]);
        $to=strtotime($courseTime[1]);
    }else{
        return null;
    }


    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE (`status` = 2  OR `status` = 1 OR `status` = 0) AND (`activity_start_time`  between '".$from."' AND '".$to."') OR (`activity_stop_time`  between '".$from."' AND '".$to."')";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);

    $result1=array_values($startedActivitiesRes);

    foreach($result1 as $act){
        $sql="SELECT `module`,section FROM `mdl_course_modules` WHERE `id` ='".$act->activityid."' and course='".$todayCourse."'";
        $actres=$DB->get_record_sql($sql);
        if(($actres->module==$vpl)||($actres->module==$quiz)){
            return $actres->section;
        }
    }//end of for
}// end of getMorningTeleconnectID


function getCPCAttendanceReport($courseid)
{
    global $DB;

    $html='';

    $courseTime=getCourseTimings($courseid,date("D",time()));
    if($courseTime){
        $from=strtotime($courseTime[0]);
        $to=strtotime($courseTime[1]);
    }else{
        return array();
        exit(0);
    }

    /*if((date("D",time())=='Tue')||(date("D",time())=='Thu')||(date("D",time())=='Fri')||(date("D",time())=='Sun')){
        return array();
        exit(0);
    }*/

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));


    $context = context_course::instance($courseid);


    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentArray=array();

    foreach($students as $student) {

        $rollno=$student->username;//getStudentData($student->id,$rollfield);


        $cpcatt=getCPCAtt($student->id);
        if($cpcatt){
            $att="PRESENT";
        }else{
            $att="ABSENT";
        }

        $html.='<tr>
                        <td>'.$rollno.'</td>
                        <td class="stdname">'.$student->firstname.''.$student->lastname.'</td>
                        <td>'.$att.'</td>
                        </tr>';
         $studentArray[]=array('rollno'=>$rollno,'attendance'=>$att);

    }//end of student object

    if(count($studentArray)){
        return $studentArray;
    }else{
        return array();
    }

    //var_dump($studentArray);

}

function getCPCTodayPerformance($courseid){

    global $DB;

    $html='';
    /*if((date("D",time())=='Tue')||(date("D",time())=='Thu')||(date("D",time())=='Fri')||(date("D",time())=='Sun')){
        return array();
        exit(0);
    }*/

    $courseTime=getCourseTimings($courseid,date("D",time()));
    if($courseTime){
        $from=strtotime($courseTime[0]);
        $to=strtotime($courseTime[1]);
    }else{
        return array();
        exit(0);
    }

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));


    $context = context_course::instance($courseid);


    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentArray=array();
    $att=0;
    foreach($students as $student) {

        $rollno=$student->username;//getStudentData($student->id,$rollfield);

        $studentArray[]=array('rollno'=>$rollno,'performance'=>TotalMeanGradeToday($courseid,$student->id));

    }//end of student object
//var_dump($studentArray);
    if(count($studentArray)){
        return $studentArray;
    }else{
        return array();
    }
}


function TotalMeanGradeToday($courseid,$studentId){
    global $DB;

    /*if(date("D",time())=='Sat'){
        $slot_end=strtotime("18.30");//"17.00";
        $from = strtotime("13.00");//strtotime("13.00");
    }else{
        $slot_end=strtotime("13.00");
        $from = strtotime("9.00");
    }*/

    $courseTime=getCourseTimings($courseid,date("D",time()));
    if($courseTime){
        $from=strtotime($courseTime[0]);
        $slot_end=strtotime($courseTime[1]);
    }else{
        return array();
        exit(0);
    }

    $courseReport=array();
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $sql="SELECT *
	    FROM mdl_course_modules
	    WHERE course = '".$courseid."' AND (completionexpected between '".$from."' and '".$slot_end."') AND module IN($vpl,$quiz)";
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
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0 OR `status` = 2) AND ((`activity_start_time`  between '".$from."' AND '".$slot_end."') OR (`activity_stop_time`  between '".$from."' AND '".$slot_end."')) ";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    $startedActivityIds=array();
    //echo $startedActivitiesSql;
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)){
        $rsql = "SELECT * FROM `mdl_course_modules` WHERE  `ID` IN (".implode(',',$startedActivityIds).") AND course = '".$courseid."' AND module IN($vpl,$quiz)";
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

function getCourseTodayPerformance($courseid,$stdsection){

    global $DB;

    $html='';
    /*if((date("D",time())=='Tue')||(date("D",time())=='Thu')||(date("D",time())=='Fri')||(date("D",time())=='Sun')){
        return array();
        exit(0);
    }*/

    $courseTime=getCourseTimings($courseid,date("D",time()));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    if($courseTime){
        $from=strtotime($courseTime[0]);
        $to=strtotime($courseTime[1]);
    }else{
        $html.='<tr><td colspan="9">No Records Found</td></tr>';
    }

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));


    $context = context_course::instance($courseid);


    $students = get_role_users(5 , $context);//getting all the students from a course level
    $studentArray=array();
    $att=0;
    foreach($students as $student) {
$att++;
        $rollno=$student->username;//getStudentData($student->id,$rollfield);
        $stu_section=getStudentData($student->id,$sectionfield);

        if(($stdsection==$stu_section)||(is_number($stdsection))){

       // $studentArray[]=array('rollno'=>$rollno,'performance'=>TotalMeanGradeToday($courseid,$student->id));
        $performance=TotalMeanGradeToday($courseid,$student->id);
        //$studentArray[]=array('rollno'=>$rollno,'performance'=>$performance);
        //$courseReport=array("totallabscount"=>$totalLabsCount,"labaverage"=>$labaverage,"totalquizescount"=>$totalQuizCount,"quizaverage"=>$quizaverage,"coursemeangrade"=>round($meangrade,2),'attemptedlabs'=>$totalLabsAttemptedCount,'attemptedquiz'=>$totalQuizAttemptedCount);
        if($performance['totallabscount']||$performance['totalquizescount']){
            $html.='<tr>
                                    <td>'.$att.'</td>
                        <td>'.$rollno.'</td>
                        <td>'.$student->firstname.'</td>
                        <td class="stdname">'.$performance['totallabscount'].'</td>
                        <td>'.$performance['totalquizescount'].'</td>
                        <td>'.$performance['attemptedlabs'].'</td>
                        <td>'.$performance['attemptedquiz'].'</td>
                        <td>'.$performance['labaverage'].'</td>
                        <td>'.$performance['quizaverage'].'</td>
                            <td>'.$stu_section.'</td>
                        </tr>';
        }
        }//end of section check

    }//end of student object
//var_dump($studentArray);
        if(!$html){
            $html.='<tr><td colspan="9">No Records Found</td></tr>';
        }
        return $html;

}

function update_webinar_attendance($rollno,$attendance){

    global $DB;
    $curdate=date("y-m-d",time());

    $query = "SELECT rollno,curdate FROM mdl_webinar_attendance WHERE rollno='".$rollno."' and curdate='".$curdate."'";

    //echo $query;

    $recResult=$DB->get_record_sql($query);

    if(!$recResult){

        $insertTable= $DB->execute("insert into mdl_webinar_attendance (rollno, curdate,attendance) values('".$rollno."', '".$curdate."', '".$attendance."')");
        //echo 'row'.($i+1).'\n <br/>';
        return 1;

    } else {
        return 0;
    }
}



function getActivitiesModsByTopicAndCourse($courseid,$topicid){
    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = ".$courseid." AND `section` = ".$topicid." AND `module` IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql( $activitiesSql);
    //echo $activitiesSql;
    $activities=array();
    $current_activities=array();
    foreach ( $activities_obj as $act) {
        $activities[$act->id]=array("id"=>$act->id,"module"=>$act->module,"instance"=>$act->instance);
    }

    //checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds=array();
    foreach ($startedActivitiesRes as $item )
    {
        $startedActivityIds[]= $item->activityid;
    }

    if(count($startedActivityIds)) {
        $rsql = "SELECT *	FROM mdl_course_modules
	WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid." AND `module` IN($vpl,$quiz)";
        $currentRes = $DB->get_records_sql($rsql);
        //echo $rsql;
        foreach ($currentRes as $item) {
            if(array_key_exists($item->id,$activities)){

            }else{
                $activities[]=array("id"=>$item->id,"module"=>$item->module,"instance"=>$item->instance);
            }

        }
    }
//var_dump($activities);

    $activities=array_values($activities);

    // var_dump($activities);
    $vact="<option value='0'>All</option>";

    foreach ( $activities as $act) {

        if(!empty(getVplname($act['instance']))&& getVplname($act['instance'])!="" && $act['module']==$vpl) {
            $vact .= "<option value='" . $act['id'].'-'.$act['module'] . "'>" . getVplname($act['instance']) . "</option>";
        }
        if(!empty(getQuizname($act['instance']))&& getQuizname($act['instance'])!=""  && $act['module']==$quiz) {
            $vact .= "<option value='" . $act['id'].'-'.$act['module'] . "'>" . getQuizname($act['instance']) . "</option>";
        }


    }

//$resultarray=array_merge($activities,$current_activities);
    return $vact;

}


function getQuizname($iid){
    global $DB;
    $rsql = "SELECT name FROM mdl_quiz
	WHERE  id = $iid";
    $currentRes = $DB->get_record_sql($rsql);
    return $currentRes->name;
}



//custom reports code start here

function getDateListInRange($first,$last,$format='y-m-d'){
    date_default_timezone_set('Asia/Kolkata');
    $step = '+1 day';
    $output_format = $format;

    //$first='17-07-01';
    //$last='17-07-10';
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }
    return $dates;

}

function getTotalLabsandQuizesCount($courseid,$from,$to){

    global $DB,$CFG;

    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $totallabssql= "SELECT count(DISTINCT id) as totallabs
                    FROM `mdl_user_vpl_grades_view` WHERE id
                    IN (SELECT id FROM `mdl_course_modules` WHERE
                    ((`ID` IN (SELECT activityid FROM `mdl_activity_status_tsl`
                    WHERE (`status` = 1 OR `status` = 0) AND (`activity_start_time`  between '".$from."' AND '".$to."')
                    OR (`activity_stop_time`  between '".$from."' AND '".$to."')))
                    OR  (completionexpected between '".$from."' AND '".$to."'))
                    AND (course = '".$courseid."' AND module IN($vpl)) ) LIMIT 1";
//echo $totallabssql;
//echo userdate($from);
//echo userdate($to);
    $totalquizesql= "SELECT count(DISTINCT id) as totalquizes
                    FROM `mdl_user_quiz_grades_view` WHERE id
                    IN (SELECT id FROM `mdl_course_modules` WHERE
                    ((`ID` IN (SELECT activityid FROM `mdl_activity_status_tsl`
                    WHERE (`status` = 1 OR `status` = 0) AND (`activity_start_time`  between '".$from."' AND '".$to."')
                    OR (`activity_stop_time`  between '".$from."' AND '".$to."')))
                    OR  (completionexpected between '".$from."' AND '".$to."'))
                    AND (course = '".$courseid."' AND module IN($quiz)) ) LIMIT 1";
//echo "<br/><br/>";

//echo $totalquizesql;
    $totallabsres=$DB->get_record_sql($totallabssql);
    $totalquizeres=$DB->get_record_sql($totalquizesql);
    if($totallabsres){$totallabs=$totallabsres->totallabs;}else{$totallabs=0;}
    if($totalquizeres){$totalquizes=$totalquizeres->totalquizes;}else{$totalquizes=0;}

    return array("labscount"=>$totallabs,"quizcount"=>$totalquizes);

}
function getStudentCustomReports($courseid,$fromdate,$todate,$stdsection){

    global $DB;

    $rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    // $parentphonefield=$DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));
    $datelabels=getDateListInRange($fromdate,$todate,'d.m.y');
    $dateslist=getDateListInRange($fromdate,$todate,'y-m-d');
    $html='<table class="CSSTableGenerator table table-hover course-list-table display nowrap"  width="100%" cellspacing="0" id="fresh-datatables">
            <thead><tr><th style="width: 130px">RollNo</th><th >Name</th><th style="width: 75px">Section</th>';
    $colwidth=count($datelabels);
    for($dt=0;$dt<$colwidth;$dt++){
        $html.='<th class="dates" style="width: 100px">'.$datelabels[$dt].'</th>';
    }
    $html.='</thead></tr><tbody>';

    $from=strtotime($fromdate);
    $to=strtotime($todate);
    if($courseid) {


        $totalactivitiesCount=getTotalLabsandQuizesCount($courseid,$from,$to);

        $context = context_course::instance($courseid);
        $students = get_role_users(5, $context);//getting all the students from a course level
        $weekflag=1;

        $allstudents=array();
        if($totalactivitiesCount['labscount']||$totalactivitiesCount['quizcount']){
        foreach ($students as $student) {



            $rollno=$student->username;//getStudentData($student->id,$rollfield);
            $stu_section=getStudentData($student->id,$sectionfield);

            if($stu_section==$stdsection){
                $html.='<tr>';

                $html.='<td>'.$rollno.'</td><td>'.$student->firstname.' '.$student->lastname.'</td><td>'.$stu_section.'</td>';
                //$userobj = get_complete_user_data(id, $student->id);
                // $stu_section=get_complete_user_data(id,$student->id)->profile['section'];
                // $rollno=get_complete_user_data(id,$student->id)->profile['rollno'];

                for($dt=0;$dt<count($dateslist);$dt++) {
                    $beginOfDay = strtotime("midnight", strtotime($dateslist[$dt]));
                    $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;
                    $from=$beginOfDay;$to=$endOfDay;


                    $totalLabsandQuizesCount=getTotalLabsandQuizesCount($courseid,$from,$to);
                    $totallabsCount=$totalLabsandQuizesCount['labscount'];
                    $totalquizCount=$totalLabsandQuizesCount['quizcount'];



                    $vplResArray = $quizResArray = null;
                    $vplhtml = '';
                    $quizhtml = '';

                    //echo $rollno;echo '<br/>';echo '<br/>';
                    if ($totallabsCount || $totalquizCount){
                        $gradeRes = getCustomDatePerformanceOfUser($courseid, $student->id, $from, $to);
                        $lcount=$qcount=1;
                        if ($gradeRes['vpl'] || $gradeRes['quiz']) {
                            $vplResArray = $gradeRes['vpl'];
                            $quizResArray = $gradeRes['quiz'];

                            foreach ($vplResArray as $vr) {
                                $grade=($vr->grade!=null)?round($vr->grade,2):'NG';
                                $vplhtml .= 'L'.$lcount.' - '.$grade. '('.$vr->submissions.')'."<br/>";
                                $lcount++;
                            }
                            foreach ($quizResArray as $qr) {
                                $quizhtml .= 'Q'.$qcount.' - '.round($qr->grade,2) ."<br/>";
                                $qcount++;
                            }
                            //var_dump($quizResArray);
                        }
                        if($qcount<=$totalquizCount){
                            for($j=$qcount;$j<=$totalquizCount;$j++){
                                $quizhtml .= 'Q'.$j.' - NA'."<br/>";
                            }
                        }
                        if($lcount<=$totallabsCount){
                            for($i=$lcount;$i<=$totallabsCount;$i++){
                                $vplhtml .= 'L'.$i.' - NA'."<br/>";

                            }
                        }

                    }//end of checking labs and quizes count for perticular date
                    else{
                        $vplhtml='NA';
                    }
                    $html .= '<td title="'.$vplhtml.','.$quizhtml.'"><div>'. $vplhtml . '</div><div>' . $quizhtml . '</div></td>';


                }//end of dateslist
                $html.='</tr>';
            }//end of student section check
        }//end of students loop

        }//end of activities check in given date range
        else{
            $html.='<tr><td style="text-align:center"  colspan="'.($colwidth+3).'">no records found in given range</td></tr>';
        }

        $html.='</tbody></table>';

    }//end of course id check
    echo $html;
}




function getCustomDatePerformanceOfUser($courseid,$studentId,$from,$to){

    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));


    $vplsql = "SELECT *
                FROM `mdl_user_vpl_grades_view` WHERE id IN
                (SELECT id FROM `mdl_course_modules` WHERE
                ((`ID` IN (SELECT activityid FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0)
                 AND (`activity_start_time`  between '".$from."' AND '".$to."')
                 OR (`activity_stop_time`  between '".$from."' AND '".$to."')))
                 OR  (completionexpected between '".$from."' AND '".$to."')) AND
                 (course = '".$courseid."' AND module IN($vpl)) ) AND userid=$studentId";

    $quizsql = "SELECT *
                  FROM `mdl_user_quiz_grades_view` WHERE id IN (SELECT id FROM `mdl_course_modules` WHERE
                 ((`ID` IN (SELECT activityid FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0)
                  AND (`activity_start_time`  between '".$from."' AND '".$to."') OR (`activity_stop_time`  between '".$from."' AND '".$to."')))
                  OR  (completionexpected between '".$from."' AND '".$to."')) AND (course = '".$courseid."' AND module IN($quiz)) )
                  AND userid=$studentId";

    //echo $vplsql;
    //echo $quizsql;
    //exit(0);

   $vplRes=$DB->get_records_sql($vplsql);
    $quizRes=$DB->get_records_sql($quizsql);
    $vplRes=array_values($vplRes);
    $quizRes=array_values($quizRes);
    $res=array("vpl"=>$vplRes,"quiz"=>$quizRes);

    return $res;

}

//custom reports code ends here


?>
