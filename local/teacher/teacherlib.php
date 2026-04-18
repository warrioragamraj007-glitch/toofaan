<?php
require('../../config.php');
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
global $CFG;
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/modinfolib.php');
include_once("course-timings.php");
require_once('../../mod/vpl/vpl_submission.class.php');

function timespent_on_activity($activityid,$userid){

    $vpl = new mod_vpl($activityid);

    //var_dump($vpl);
    $submissionslist = $vpl->user_submissions($userid);
    if(count($submissionslist) == 0){
        return 0;
    }
    $submissionslist=array_reverse($submissionslist);
    $workperiods=array();
    if($submissionslist){
        $last_save_time=0;
        $rest_time=20*60; //20 minutes. Rest period before next work
        $first_work=10*59; //10 minutes. Work before first save
        $intervals=-1;
        $work_start=0;
        foreach ($submissionslist as $submission) {
            /*Start new work period*/
            if($submission->datesubmitted-$last_save_time >= $rest_time){
                if($work_start>0){ //Is not the first submission
                    if($intervals>0){//First work as average
                        $first_work = (float)($last_save_time-$work_start)/$intervals;
                    }//else use the last $first_work
                    $workperiods[]=($last_save_time-$work_start+$first_work)/(3600.0);
                }
                $work_start=$submission->datesubmitted;
                $intervals=0;
            }else{//Count interval
                $intervals++;
            }
            $last_save_time=$submission->datesubmitted;
        }
        if($intervals>0){//First work as average
            $first_work = (float)($last_save_time-$work_start)/$intervals;
        }//else use the last $first_work
        $workperiods[]=($last_save_time-$work_start+$first_work)/(3600.0);
    }

    $hours=0.0;

    for($i=0; $i<count($workperiods); $i++){
        $x_data[]=$i+1;
        $hours+=$workperiods[$i];
    }

    return round($hours,2);

    //return $workperiods;
}


function getStudentData($userid, $fieldid) {
    global $DB;
    $sql = "SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata = $DB->get_record_sql($sql);
    $studata = $fielddata->data;

    return $studata;
}
function is_activity_deletion_in_progress($activity_id)
    {
        global $DB;
        // Check if deletioninprogress is 1
        return $DB->record_exists('course_modules', ['id' => $activity_id, 'deletioninprogress' => 1]);
    }
function getCourseActivities($courseid) {
        global $DB;
        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        $response = array();
        foreach ($sections as $section) {
            // if (!is_activity_deletion_in_progress($activity->id)){
            $activities = $DB->get_records('course_modules', array('section' => $section->id));
            $sectionData = array(
                'section_id' => $section->id,
                'section' => $section->name,
                'activities' => array()
            );
            if (!empty($activities)) {
                foreach ($activities as $activity) {
                    if (!is_activity_deletion_in_progress($activity->id)){
                    try{
                        $modinfo = get_fast_modinfo($courseid);
                   
                        $mod = $modinfo->get_cm($activity->id);
                
                        if ($mod->modname !== 'resource'){
                        $activityData = array(
                            'name' => $mod->name,
                            // 'type' => $mod->modname, //  modname represents the type ('quiz' or 'vpl')
                            'type' => $mod->modname,
                            'deleted' => false
                        );
                        $sectionData['activities'][] = $activityData;
                    }
                }
                    catch(Exception $e)
                    {
                        continue;
                    }
                }
                    
                }
            }
            $response[] = $sectionData;
        }
        echo json_encode($response);
    }
if (isset($_POST)){

$mid = $_POST['mid'];
$courseid = (int)$_POST['selected_course'];
}
function getStudentSectionsByCourse($courseid){
    global $DB;
    $context = context_course::instance($courseid);
    $students = get_role_users(5, $context);
    $sectionfield = $DB->get_field('user_info_field', 'id', array('shortname' => 'section'));
    $stuarr = array();
    $stcnt = 0;

    foreach ($students as $student){
        $stu_section = getStudentData($student->id, $sectionfield);

        if ($stu_section){
            $stuarr[$stcnt++] = array('stusec' => $stu_section, 'stid' => $student->id);
        }
    }

    $ss = array_unique(array_column($stuarr, 'stusec'));
    sort($ss);

    $html = "<option value='0'>All</option>";

    foreach ($ss as $section){
        $html .= "<option value='".$section."'>".$section;
    }

    return $html;
}
// function getGradeByActivity($studentId,$courseid,$module,$instance,$vpl){

//     global $DB;
//     /**************GETTING ITEM NAME **********/
   
//     $sql_item="SELECT name
//         FROM mdl_modules
//         WHERE id ='".$module."'";
//     $item_res=$DB->get_record_sql($sql_item);
//     $itemname= $item_res->name;
//     $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
//     // var_dump($grading_info);
//     $item = $grading_info->items[0];
//     // var_dump($item);
//     // if($studentId==6){
//     //     echo "studentid and module ".$studentId." ".$instance;
//     //     echo $itemname;
//     //     var_dump($grading_info);
//     // }
//     $gradeI= $item->grades[$studentId];
//     $grade = $gradeI->grade ;

//     /***logic to count total activities and attempted activities***/
//     if($module==$vpl){

//         if($grade)
//         {
//         }else{

//             $tsql="SELECT  datesubmitted
//                         FROM mdl_vpl_submissions
//                         WHERE vpl ='".$instance."'
//                         AND userid ='".$studentId."'";

//             $submissions=$DB->get_fieldset_sql($tsql);
//             if( count($submissions)>0)
//             {
//                 return -1;
//             }
//         }

//     }
//     // var_dump($grade);
//     //         exit(0);
//     return $grade;
// }
// updated  the code to handle the assignment module grades and submission status by chandrika
function getGradeByActivity($studentId,$courseid,$module,$instance,$vpl){
    global $DB;

    $sql_item = "SELECT name
                 FROM mdl_modules
                 WHERE id = '".$module."'";
    $item_res = $DB->get_record_sql($sql_item);

    if (!$item_res) {
        return NULL;
    }

    $itemname = $item_res->name;

    // 1. Try from Moodle gradebook first
    $grading_info = grade_get_grades($courseid, 'mod', $itemname, $instance, $studentId);

    $grade = NULL;
    if (!empty($grading_info->items) &&
        !empty($grading_info->items[0]) &&
        isset($grading_info->items[0]->grades[$studentId])) {
        $grade = $grading_info->items[0]->grades[$studentId]->grade;
    }

    // 2. VPL special handling
    if ($module == $vpl) {
        if ($grade !== NULL && $grade !== '') {
            return $grade;
        } else {
            $tsql = "SELECT datesubmitted
                     FROM mdl_vpl_submissions
                     WHERE vpl = '".$instance."'
                     AND userid = '".$studentId."'";

            $submissions = $DB->get_fieldset_sql($tsql);

            if (count($submissions) > 0) {
                return -1;
            }
        }
    }

    // 3. Assignment fallback
    if ($itemname == 'assign' && ($grade === NULL || $grade === '')) {
        $asql = "SELECT grade
                 FROM mdl_assign_grades
                 WHERE assignment = '".$instance."'
                 AND userid = '".$studentId."'
                 ORDER BY timemodified DESC
                 LIMIT 1";

        $assigngrade = $DB->get_field_sql($asql);

        if ($assigngrade !== false && $assigngrade !== NULL && $assigngrade !== '') {
            return $assigngrade;
        }

        $ssql = "SELECT status
                 FROM mdl_assign_submission
                 WHERE assignment = '".$instance."'
                 AND userid = '".$studentId."'
                 LIMIT 1";

        $submissionstatus = $DB->get_field_sql($ssql);

        if ($submissionstatus) {
            return -1; // submitted but not graded
        }
    }

    return $grade;
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
                // echo $grade;
                // var_dump($grade);
            }//end of activities
            // echo $student->id.'-'.$totalattempted.'<br/>';
            $activitiesarray[]=$totalattempted;

        }//end of student section check

    }//end of student object

    $resultarray=array_count_values($activitiesarray);
    //var_dump($resultarray);
    ksort($resultarray);
    // echo $count.'-'.count($resultarray);

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

/*function getAllActivitiesByTopicAndCourse($courseid, $topicid, $vpl, $quiz)
{
    global $DB;

    $activitiesSql = "SELECT * FROM `mdl_course_modules` WHERE `course` = " . $courseid . " AND `section` = " . $topicid . " AND `module` IN($vpl,$quiz)";
    $activities_obj = $DB->get_records_sql($activitiesSql);
// var_dump($activities_obj);
// exit(0);
    $activities = array();

    // Checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes = $DB->get_records_sql($startedActivitiesSql);
    // var_dump($startedActivitiesRes);
    // exit(0);
    $startedActivityIds = array();

    foreach ($startedActivitiesRes as $item) {
        $startedActivityIds[] = $item->activityid;
        if ($item->activity_start_time) {
            // Convert timestamp to date format
            $startdate = date("Y-m-d", $item->activity_start_time);
        }
    }
    $activitieswithdate = array();

    if (count($startedActivityIds)) {
        // Correct the SQL query for retrieving current activities
        $rsql = "SELECT * FROM mdl_course_modules
            WHERE  id IN (" . implode(',', $startedActivityIds) . ") AND course = '" . $courseid . "' AND `section` = " . $topicid . " AND `module` IN($vpl,$quiz)";
        $currentRes = $DB->get_records_sql($rsql);
        // var_dump($currentRes);
        // exit(0);
        foreach ($currentRes as $item) {
            if (array_key_exists($item->activityid, $activities)) {
                $activitieswithdate[] = array("id" => $item->activityid, "module" => $item->module, "instance" => $item->instance, "startdate" => $startdate);
            } else {
                $activitieswithdate[] = array("id" => $item->activityid, "module" => $item->module, "instance" => $item->instance, "startdate" => $startdate);
            }
        }
    }
    $activities = array_values($activitieswithdate);
    return $activities;
}*/

//updated the function to get all the vpl.quiz,assign activites by chandrika
function getAllActivitiesByTopicAndCourse($courseid, $topicid, $vpl, $quiz, $assign = null)
{
    global $DB;

    $modulelist = array($vpl, $quiz);
    if (!empty($assign)) {
        $modulelist[] = $assign;
    }
    $modulelist = implode(',', $modulelist);

    $activities = array();

    // Checking for current activities attempted grades
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 OR `status` = 0";
    $startedActivitiesRes = $DB->get_records_sql($startedActivitiesSql);

    $startedActivityIds = array();
    $startdate = '';

    foreach ($startedActivitiesRes as $item) {
        $startedActivityIds[] = $item->activityid;
        if (!empty($item->activity_start_time)) {
            $startdate = date("Y-m-d", $item->activity_start_time);
        }
    }

    $activitieswithdate = array();

    if (count($startedActivityIds) > 0) {
        $rsql = "SELECT * FROM mdl_course_modules
                 WHERE id IN (" . implode(',', $startedActivityIds) . ")
                 AND course = '" . $courseid . "'
                 AND section = " . $topicid . "
                 AND module IN(" . $modulelist . ")";

        $currentRes = $DB->get_records_sql($rsql);

        foreach ($currentRes as $item) {
            $activitieswithdate[] = array(
                "id" => $item->id,
                "module" => $item->module,
                "instance" => $item->instance,
                "startdate" => $startdate
            );
        }
    }

    $activities = array_values($activitieswithdate);
    return $activities;
}

// function getcourse(){
//     global $DB;
//     $teacher_courses = enrol_get_my_courses();
//     $options = "";

//     foreach ($teacher_courses as $course) {
//         $options .= "<option value=\"$course->id\">$course->fullname</option>";
//     }

//     return $options;
// }
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


function get_students_activity_info($courseid,$topicid,$stdsection)
{
    global $DB;

    $rollfield        = $DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
    $sectionfield     = $DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $parentphonefield = $DB->get_field('user_info_field', 'id', array('shortname'=>'parentcontactno'));

    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context); // all students

    $vpl    = $DB->get_field('modules', 'id', array('name' => 'vpl'));
    $quiz   = $DB->get_field('modules', 'id', array('name'=>'quiz'));
    $assign = $DB->get_field('modules', 'id', array('name'=>'assign'));

    $activities = getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz,$assign);
//        echo "Assign module id: ".$assign."<br>";
// echo "<pre>";
// print_r($activities);
// echo "</pre>";

    // ---------------------------
    // Step 1: Compute Total Marks
    // ---------------------------
    $studentTotals = [];
    $totalActivitiesCount = count($activities);

    foreach($students as $student)
    {
        $section = getStudentData($student->id,$sectionfield);
        if($stdsection && $stdsection != $section) continue;

        $totalScore = 0;

        foreach($activities as $act)
        {
            $grade = getGradeByActivity($student->id,$courseid,$act['module'],$act['instance'],$vpl);

            if ($grade !== NULL && $grade >= 0) {
                $totalScore += $grade;
            }
        }

        // ✅ Fixed Rule: Each activity = 100 marks
        $maxScore = $totalActivitiesCount * 100;

        $studentTotals[] = [
            'student' => $student,
            'section' => $section,
            'score'   => round($totalScore,2),
            'maxscore'=> $maxScore
        ];
    }

    // Sort by score DESC (Rank)
    usort($studentTotals, function($a,$b){
        return $b['score'] <=> $a['score'];
    });

    // ---------------------------
    // Step 2: Build Table Headers
    // ---------------------------
    $html='<thead><tr>
            <th class="header sorter-false">Rank</th>
            <th class="header">Roll No</th>
            <th class="header">Name</th>
            <th class="header">Section</th>
            <th class="header">Total Activities</th>
            <th class="header">Graded</th>';

    $labcount=$qcount=$assigncount=0;
    foreach($activities as $act){
        if($act['module']==$vpl)   { $labcount++;    $html.='<th class="header">Lab'.$labcount.'</th>'; }
        if($act['module']==$quiz)  { $qcount++;      $html.='<th class="header">Quiz'.$qcount.'</th>'; }
        if($act['module']==$assign){ $assigncount++; $html.='<th class="header">Assign'.$assigncount.'</th>'; }
    }

    // Total Column at end
    $html.='<th class="header">Total Grade</th>
        <th class="header">Attendance</th>
        </tr></thead><tbody class="grade-info">';

    // ---------------------------
    // Step 3: Rows
    // ---------------------------
    $rank=1; $recordsCount=0;

    foreach($studentTotals as $entry)
    {
        $student = $entry['student'];
        $rollno  = $student->username;
        $parentno = getStudentData($student->id,$parentphonefield);
        $name    = $student->firstname.' '.$student->lastname;
        $section = $entry['section'];

        $score   = $entry['score'];
        $maxscore= $entry['maxscore']; // always valid now

        $totalattempted = 0;
        //COURSE LEVEL ATTENDANCE log code by chandrika
            $isPresent = getCourseAttendance($courseid, $student->id);
            $attendanceText = $isPresent ? 'Present' : 'Absent';
        $acts='';

        foreach($activities as $act){
            $grade = getGradeByActivity($student->id,$courseid,$act['module'],$act['instance'],$vpl);
            if($grade===NULL || $grade<0){
                $grade='NG';
            } else {
                $totalattempted++;
                $grade = round($grade,2);
            }
            $acts .= '<td>'.$grade.'</td>';
        }

        $html .= '<tr>
            <td>'.$rank.'</td>
            <td>'.$rollno.'</td>
            <td class="stdname"><span>'.$name.'</span><input type="hidden" class="parentno" value="'.$parentno.'" /></td>
            <td>'.$section.'</td>
            <td>'.$totalActivitiesCount.'</td>
            <td>'.$totalattempted.'</td>
            '.$acts.'
            <td>'.$score.'/'.$maxscore.'</td>
            <td>'.$attendanceText.'</td>
        </tr>';

        $rank++;
        $recordsCount++;
    }

    $html.='</tbody>';

    return $html.'<p id="records">'.$recordsCount.'</p>';
}

// student attendace log code by chandrika
function getCourseAttendance($courseid, $userid) {
    global $DB;

    return $DB->record_exists(
        'webinar_attendance',
        [
            'cid' => $courseid,
            'userid' => $userid,
            'attendance' => 1
        ]
    );
}

//new feature for student activities group wise report

function get_students_activity_wise_report($courseid, $topicid, $stdsection)
{
    global $DB;
    $sectionfield = $DB->get_field('user_info_field', 'id', ['shortname' => 'section']);
    $context = context_course::instance($courseid);
    $students = get_role_users(5, $context);

    $vpl  = $DB->get_field('modules', 'id', ['name' => 'vpl']);
    $quiz = $DB->get_field('modules', 'id', ['name' => 'quiz']);

    $activities = getAllActivitiesByTopicAndCourse($courseid, $topicid, $vpl, $quiz);

    // Header
    $html = '<thead><tr>
                <th class="header">Roll No</th>
                <th class="header">Name</th>
                <th class="header">Group Name</th>
                <th class="header">Section</th>';

    foreach ($activities as $act) {
        $type = ($act['module'] == $vpl) ? 'Lab' : 'Quiz';
        $html .= '<th class="header">' . $type . '</th>';
    }
    $html .= '<th class="header">Total Marks</th>
             
              </tr></thead><tbody class="grade-info">';

    $showall = empty($stdsection);

    // === FIRST PASS: Collect all students + calculate correct group totals ===
    $sorted_students = [];
    $group_activity_totals = [];

    foreach ($students as $student) {
        $rollno  = $student->username;
        $section = getStudentData($student->id, $sectionfield);
        $name    = $student->firstname . ' ' . $student->lastname;

        $group = $DB->get_field('user', 'city', ['id' => $student->id]);
        $group = trim($group);
        $group_key = $group ? $group : 'zzz_no_group';

        if ($showall || $section == $stdsection) {
            $total_marks = 0;
            $grades = [];

            foreach ($activities as $i => $act) {
                $grade = getGradeByActivity($student->id, $courseid, $act['module'], $act['instance'], $vpl);
                $display = ($grade == -1 || $grade === null) ? '--' : round($grade, 2);
                if ($grade !== -1 && $grade !== null) {
                    $total_marks += $grade;

                    // Add to group total
                    if ($group && $group !== 'zzz_no_group') {
                        if (!isset($group_activity_totals[$group])) {
                            $group_activity_totals[$group] = array_fill(0, count($activities), 0);
                        }
                        $group_activity_totals[$group][$i] += $grade;
                    }
                }
                $grades[] = $display;
            }

            $sorted_students[] = [
                'rollno' => $rollno,
                'name'   => $name,
                'group'  => $group,
                'group_key' => $group_key,
                'section' => $section,
                'grades' => $grades,
                'total_marks' => $total_marks
            ];
        }
    }

    usort($sorted_students, fn($a, $b) => strcmp($a['group_key'], $b['group_key']));

    $current_group = '';

    foreach ($sorted_students as $data) {
        $group = $data['group'] ?: '--';
        $group_display = $group !== '--' 
            ? '<strong style="color:#e74c3c;">' . htmlspecialchars($group) . '</strong>' 
            : '--';

        // === GROUP SUMMARY ROW ===
        if ($current_group && $current_group !== $group && $current_group !== 'zzz_no_group') {
            $group_total = array_sum($group_activity_totals[$current_group]);

            $html .= '<tr>
                        <td style="text-align:right; padding-right:20px;">
                            Summary Of Group ' . htmlspecialchars($current_group) . '
                        </td><td></td><td></td><td></td>';

            foreach ($activities as $i => $act) {
                $sum = round($group_activity_totals[$current_group][$i] ?? 0, 2);
                $bg = ($act['module'] == $vpl) ? '#f39c12' : '#3498db';
                $html .= '<td style="background:' . $bg . ' !important; color:white !important;">' . 
                         ($sum > 0 ? $sum : '--') . '</td>';
            }

            $html .= '<td style="background:#27ae60 !important; color:white !important; font-weight:bold;">' . round($group_total, 2) . '</td>
                      
                      </tr>';
        }

        // === STUDENT ROW ===
        $acts = '';
        foreach ($data['grades'] as $g) {
            $acts .= '<td>' . $g . '</td>';
        }

        // Correct Group Score - from real group total
        $group_score = ($group !== '--' && $group !== 'zzz_no_group')
            ? round(array_sum($group_activity_totals[$group] ?? []), 2)
            : '--';

        $html .= '<tr>
                    <td>' . $data['rollno'] . '</td>
                    <td class="stdname"><span>' . $data['name'] . '</span></td>
                    <td>' . $group_display . '</td>
                    <td>' . $data['section'] . '</td>'
                    . $acts .
                    '<td style="font-weight:bold;">' . round($data['total_marks'], 2) . '</td>
                    
                  </tr>';

        $current_group = $group;
    }

    // Final group summary
    if ($current_group && $current_group !== 'zzz_no_group') {
        $group_total = array_sum($group_activity_totals[$current_group]);

        $html .= '<tr>
                    <td style="text-align:right; padding-right:20px;">
                        Summary Of Group ' . htmlspecialchars($current_group) . '
                    </td><td></td><td></td><td></td>';

        foreach ($activities as $i => $act) {
            $sum = round($group_activity_totals[$current_group][$i] ?? 0, 2);
            $bg = ($act['module'] == $vpl) ? '#f39c12' : '#3498db';
            $html .= '<td style="background:' . $bg . ' !important; color:white !important;">' . 
                     ($sum > 0 ? $sum : '--') . '</td>';
        }

        $html .= '<td style="background:#27ae60 !important; color:white !important; font-weight:bold;">' . round($group_total, 2) . '</td>
                  
                  </tr>';
    }

    $html .= '</tbody>';
    return $html;
}


function get_students_activity_info1($courseid,$topicid,$stdsection)
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
    $assign = $DB->get_field('modules', 'id', array('name'=>'assign'));
    $html='<thead>
                                <tr>
                                   <!-- <th class="header" style="text-align:center">Course</th>-->
                                    <th class="header">Roll No</th>
                                    <th class="header">Name</th>
                                    <th class="header">Section</th>
                                    <th class="header">Phone</th>
                                    <th class="header">Email</th>
                                   
                                    <th class="header">Total</th>
                                    <th style="text-align:center" class="header">Graded</th>
                                    ';

    $activities=getAllActivitiesByTopicAndCourse($courseid,$topicid,$vpl,$quiz,$assign);
    //        echo "Assign module id: ".$assign."<br>";
// echo "<pre>";
// print_r($activities);
// echo "</pre>";
	//var_dump($activities);
    $labcount=0;$qcount=0;
    for($i=0;$i<count($activities);$i++){
        if($vpl==$activities[$i]['module']) {
            //var_dump($activities[$i]);

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
            //$onlineatt=getOnlineAtt($rollno,$curdate);
            //$html.='<td>' . $onlineatt.'</td>';
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
            // $html.='<td>' . $onlineatt.'</td>';
            $html.=$total.$totalattacts.$acts;
            $html.='</tr>';

        }//end of student section check

    }//end of student object

    $html.='</tbody>';

    return $html;

}
function getOnlineAtt($stdroll,$curdate){
    global $DB;
    return '--';
    //$sql = "select * FROM mdl_webinar_attendance where  curdate='".$curdate."'";

    //$result = $DB->get_records_sql($sql,null);
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
function getCourseTodayPerformance($courseid,$stdsection,$date){

    global $DB;

    $html='';
    /*if((date("D",time())=='Tue')||(date("D",time())=='Thu')||(date("D",time())=='Fri')||(date("D",time())=='Sun')){
        return array();
        exit(0);
    }*/

    // $courseTime=getCourseTimings($courseid,date("D",time()));
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    // if($courseTime){
    //     $from=strtotime($courseTime[0]);
    //     $to=strtotime($courseTime[1]);
    // }else{
    //     $html.='<tr><td colspan="9">No Records Found</td></tr>';
    // }

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
        $performance=TotalMeanGradeToday($courseid,$student->id,$date);
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
function TotalMeanGradeToday($courseid,$studentId,$date){
    global $DB;

    
// to get only currentdate performance code
    // $courseTime=getCourseTimings($courseid,date("D",time()));
    // if($courseTime){
    //     $from=strtotime($courseTime[0]);
    //     $slot_end=strtotime($courseTime[1]);
    // }else{
    //     return array();
    //     exit(0);
    // }

    // to get selected date performance code
    $t1="1.00";
$t2="23.00";
$d1 = $date . ' ' . $t1;
   $d2= $date . ' ' . $t2;
//    var_dump($d1);
 $from=strtotime($d1);
        $slot_end=strtotime($d2);
    $courseReport=array();
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));

    $sql="SELECT *
	    FROM mdl_course_modules
	    WHERE course = '".$courseid."' AND (completionexpected between '".$from."' and '".$slot_end."') AND module IN($vpl,$quiz)";
        //  var_dump($sql);
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


function getOverallCourseReport($courseid){
    global $USER,$DB;
    //echo $courseid;
    
    $maxactivities="SELECT count(distinct a.id) as total FROM mdl_user_vpl_grades_view as a
    where a.course='".$courseid."' /* and id in(696,705,740,760,772,773,789,790,824,825,831,832,845,846,864,862,898,928,930,1013,1012,1025,1096,1097,1098,1128,1129,1327,1315,1316,1297,1270,1260,1234)*/ group by a.userid order by total desc limit 1";
    
        $totact=$DB->get_record_sql($maxactivities, null);
    //var_dump($totact);
     $getCategoriessql="SELECT DISTINCT 
    c.username AS HallTicketNo,
    c.id,
    c.section,
    c.year,
    c.phone1,
    c.fullname AS studentname,
    b.tgrade AS totalscore,
    b.total AS totalactivities,
    CONCAT(b.total, ' / ', '".$totact->total."') AS attemptedactivities,
    ROUND((b.tgrade / '".$totact->total."'), 2) AS Percentile,
    b.student_rank AS studentrank,
    b.activity_ids AS activityids,
    b.totalsubmissions
FROM (
    SELECT 
        a.userid,
        ROUND(SUM(a.grade), 0) AS tgrade,
        COUNT(a.id) AS total,
        SUM(a.submissions) AS totalsubmissions,
        GROUP_CONCAT(a.id ORDER BY a.dategraded ASC) AS activity_ids,
        MIN(a.dategraded) AS first_submission_time,
        RANK() OVER (ORDER BY SUM(a.grade) DESC, MIN(a.dategraded) ASC) AS student_rank
    FROM mdl_user_vpl_grades_view AS a
    WHERE a.course = '".$courseid."'
    GROUP BY a.userid
) AS b
LEFT JOIN mdl_complete_user_view AS c ON b.userid = c.id

-- Only users assigned to this particular course's context
INNER JOIN mdl_context ctx ON ctx.instanceid = '".$courseid."' AND ctx.contextlevel = 50
INNER JOIN mdl_role_assignments AS ra ON c.id = ra.userid AND ra.contextid = ctx.id
INNER JOIN mdl_role AS r ON ra.roleid = r.id

WHERE r.shortname = 'student'
ORDER BY ROUND((b.tgrade / '".$totact->total."'), 2) DESC;
";
   // echo $getCategoriessql;
        $results=$DB->get_records_sql($getCategoriessql, null);
    $sno=1;
   // var_dump($results);
    if( count($results)>0){
        foreach ($results as $res) {
            $userid = trim($res->id); // Ensure user ID is clean
            $hallticketno = trim($res->hallticketno); // Unique Hall Ticket No
        
            // Ensure unique Hall Ticket No
            if (!isset($students[$hallticketno])) {
                $students[$hallticketno] = [
                    'id' => $userid,
                    'hallticketno' => $hallticketno,
                    'studentname' => $res->studentname,
                    'totalscore' => is_null($res->totalscore) ? 0 : $res->totalscore,
                    'attemptedactivities' => is_null($res->attemptedactivities) ? 0 : $res->attemptedactivities,
                    'percentile' => is_null($res->percentile) ? 0 : $res->percentile,
                    'section' => $res->section,
                    'activityids' => [],
                    'totaltimespent' => 0,
                    'totalsubmissions' => $res->totalsubmissions,
                    'first_submission_time' => strtotime($res->first_submission_time) // Convert to timestamp
                ];
            }
        
            // Process Activity IDs and calculate total time spent
            if (!empty($res->activityids)) {
                $activityIds = array_unique(explode(',', $res->activityids)); // Ensure uniqueness
                foreach ($activityIds as $activityId) {
                    $activityId = trim($activityId);
                    if (!in_array($activityId, $students[$hallticketno]['activityids'])) {
                        $students[$hallticketno]['activityids'][] = $activityId;
                        $students[$hallticketno]['totaltimespent'] += timespent_on_activity($activityId, $userid);
                    }
                }
            }
        }
        
        // Convert activity IDs back to comma-separated strings
        foreach ($students as &$student) {
            $student['activityids'] = implode(',', $student['activityids']);
        }
        
        // Sorting: Rank by Total Score > Less Time Spent > Earliest Submission
        usort($students, function ($a, $b) {
            // Step 1: Compare scores (higher is better)
            if ($b['totalscore'] == $a['totalscore']) {
                
                // Step 2: Compare time taken (lower is better)
                if (floatval($a['totaltimespent']) == floatval($b['totaltimespent'])) {
                    
                    // Step 3: Compare first submission time (earlier is better)
                    //return strcmp($a['studentname'], $b['studentname']); // alphabetical order
                    return strtotime($a['totalsubmissions']) - strtotime($b['totalsubmissions']);
                }
                return floatval($a['totaltimespent']) <=> floatval($b['totaltimespent']);
            }
            return $b['totalscore'] <=> $a['totalscore'];
        });
        
        
        // Assign rank after sorting
        $rank = 1;
        foreach ($students as &$student) {
            $student['rank'] = $rank++;
        }
        
        
        
     
        
        foreach ($students as $student1) {
            $html .= '<tr>
                        <td>' . $student1['rank'] . '</td>
                        <td>' . $student1['hallticketno'] . '</td>
                        <td>' . $student1['studentname'] . '</td>
                         <td>' . $student1['section'] . '</td>
                     
                             <td>' . $student1['totalsubmissions'] . '</td>
                        <td>' . $student1['totaltimespent'] . '</td>
                           <td>' . $student1['attemptedactivities'] . '</td>
                        <td>' . $student1['totalscore'] . '</td>
                        <td>' . $student1['percentile'] . '</td>
                   
                      </tr>';
        }
        
    }
    if(!$html){
        $html.='<tr><td colspan="9">No Records Found</td></tr>';
    }
    return $html;
    }


    function getTodaysCourseReport($courseid){
        global $USER,$DB;
        //echo $courseid;
        
        $maxactivities="SELECT count(m.id) as total FROM mdl_course_modules m JOIN mdl_activity_status_tsl a ON m.id = a.activityid WHERE m.module = 25 and m.course=".$courseid." AND  DATE(FROM_UNIXTIME(a.activity_start_time)) = CURDATE()";
        
            $totact=$DB->get_record_sql($maxactivities, null);
        //var_dump($totact);
         $getCategoriessql="SELECT DISTINCT 
    c.username AS HallTicketNo,
    c.id,
    c.section,
    c.year,
    c.phone1,
    c.fullname AS studentname,
    b.tgrade AS totalscore,
    b.total AS totalactivities,
    CONCAT(b.total, ' / ', '".$totact->total."') AS attemptedactivities,
    ROUND((b.tgrade / '".$totact->total."'), 2) AS Percentile,
    b.student_rank AS studentrank,
    b.activity_ids AS activityids,
    b.totalsubmissions,
    b.avg_submission_time as avgsubtime
FROM (
    SELECT 
        a.userid,
        ROUND(SUM(a.grade), 0) AS tgrade,
        COUNT(a.id) AS total,
        SUM(a.submissions) AS totalsubmissions,
        GROUP_CONCAT(a.id ORDER BY a.dategraded ASC) AS activity_ids,
        ROUND(AVG(a.dategraded),0) AS avg_submission_time,
        RANK() OVER (ORDER BY SUM(a.grade) DESC, MIN(a.dategraded) ASC) AS student_rank
    FROM mdl_user_vpl_grades_view AS a
    WHERE a.course = '".$courseid."' 
      AND a.id IN (
          SELECT m.id 
          FROM mdl_course_modules m 
          JOIN mdl_activity_status_tsl a ON m.id = a.activityid 
          WHERE m.module = 25 
            AND DATE(FROM_UNIXTIME(a.activity_start_time)) = CURDATE() 
      )
    GROUP BY a.userid
) AS b
-- Join student info
LEFT JOIN mdl_complete_user_view AS c ON b.userid = c.id

-- Join context for the specific course and match role assignments
INNER JOIN mdl_context ctx ON ctx.instanceid = '".$courseid."' AND ctx.contextlevel = 50
INNER JOIN mdl_role_assignments ra ON ra.userid = c.id AND ra.contextid = ctx.id
INNER JOIN mdl_role r ON r.id = ra.roleid

WHERE r.shortname = 'student'
ORDER BY ROUND((b.tgrade / '".$totact->total."'), 2) DESC
";
      // echo $getCategoriessql;
            $results=$DB->get_records_sql($getCategoriessql, null);
        $sno=1;
       // var_dump($results);
        if( count($results)>0){
            foreach ($results as $res) {
                $userid = trim($res->id); // Ensure user ID is clean
                $hallticketno = trim($res->hallticketno); // Unique Hall Ticket No
            
                // Ensure unique Hall Ticket No
                if (!isset($students[$hallticketno])) {
                    $students[$hallticketno] = [
                        'id' => $userid,
                        'hallticketno' => $hallticketno,
                        'studentname' => $res->studentname,
                        'totalscore' => is_null($res->totalscore) ? 0 : $res->totalscore,
                        'attemptedactivities' => is_null($res->attemptedactivities) ? 0 : $res->attemptedactivities,
                        'percentile' => is_null($res->percentile) ? 0 : $res->percentile,
                        'section' => $res->section,
                        'activityids' => [],
                        'totaltimespent' => 0,
                        'totalsubmissions' => $res->totalsubmissions,
                        'avgsubtime' => $res->avgsubtime // Convert to timestamp
                    ];
                }
            
                // Process Activity IDs and calculate total time spent
                if (!empty($res->activityids)) {
                    $activityIds = array_unique(explode(',', $res->activityids)); // Ensure uniqueness
                    foreach ($activityIds as $activityId) {
                        $activityId = trim($activityId);
                        if (!in_array($activityId, $students[$hallticketno]['activityids'])) {
                            $students[$hallticketno]['activityids'][] = $activityId;
                            $students[$hallticketno]['totaltimespent'] += timespent_on_activity($activityId, $userid);
                        }
                    }
                }
            }
            
            // Convert activity IDs back to comma-separated strings
            foreach ($students as &$student) {
                $student['activityids'] = implode(',', $student['activityids']);
            }
            
            // Sorting: Rank by Total Score  > Earliest Submission > Less Time Spent > lesser Submissions > alphabetical
            usort($students, function ($a, $b) {
                // Step 1: Compare scores (higher is better)
                if ($b['totalscore'] == $a['totalscore']) {
                    
                    // Step 2: Compare time taken (earlier is better)
                   if (floatval($a['totaltimespent']) == floatval($b['totaltimespent'])) {
                        
                        // Step 3: Compare first submission time (lower is better)
                        //if (floatval($a['avgsubtime']) == floatval($b['avgsubtime'])) {
                                // Step 4: Compare total Submissions (lesser is better) 
                            if (floatval($a['totalsubmissions']) == floatval($b['totalsubmissions'])) {
                                 // Step 5: Compare Names (alphabetical order)
                                return strcmp($a['studentname'], $b['studentname']); // alphabetical order
                            }
                            return floatval($a['totalsubmissions']) <=> floatval($b['totalsubmissions']);
                       // }
                       // return floatval($a['avgsubtime']) <=> floatval($b['avgsubtime']);
                    }
                   return floatval($a['totaltimespent']) <=> floatval($b['totaltimespent']);
                }
                return $b['totalscore'] <=> $a['totalscore'];
            });
            
            
            // Assign rank after sorting
            $rank = 1;
            foreach ($students as &$student) {
                $student['rank'] = $rank++;
            }
            
            
            
         //d-m-y<td>' . date("d-m-y H:i:s", $student1['avgsubtime']).'</td>
            
            foreach ($students as $student1) {
                $html .= '<tr>
                             <td>' . $student1['rank'] . '</td>
                            <td>' . $student1['hallticketno'] . '</td>
                            <td>' . $student1['studentname'] . '</td>
                            <td>' . $student1['section'] . '</td>
                            
                            
                            <td>' . $student1['totalsubmissions']. '</td>
                            <td>' . $student1['totaltimespent']*60 . '</td>
                            <td>' . $student1['attemptedactivities'] .'</td>
                            <td>' . $student1['totalscore'] . ' / '. ($totact->total)*100 .'</td>
                            <td>' . $student1['percentile'] . '</td>
                            
                          </tr>';
            }
            
        }
        if(!$html){
            $html.='<tr><td colspan="9">No Records Found</td></tr>';
        }
        return $html;
        }



    function get_courselist($course,$topicdisplayflag)
    {
        global $CFG;
        $topicdisplayflag=isset($topicdisplayflag)?$topicdisplayflag:1;
        $html = "<table class='generaltable search-table' id='cours' width='100%'>
                <thead>
                <tr>
                     <th style='width: 10%;'>Select</th>
                   <th style='width: 25%;'>Topics</th>
                   <th style='width: 20%;'>Status</th>
                   <th style='width: 45%;'>Activities</th>
                </tr>
                </thead>
                <tbody id='cbody'>";
    
        $ehtml=$html;
        $completedhtml='';/*variable created by mahesh */
        $modinfo = get_fast_modinfo($course);
        $mods = $modinfo->get_cms();
        $sections = $modinfo->get_section_info_all();
    
        $arr = array();
        $main_array = get_sections($sections);
        // Activity loop
        $arr = get_activities($mods, $main_array);
        $arr1=get_activities_with_names($mods, $main_array);
        //var_dump($arr);
        $ptop = '';
        $com=0;$tot=0;
        $ht='';
        $count=0;
        $che=0;$activitynames='';
        foreach ($mods as $mod) {
            $top = $main_array[$mod->section];
            if ($main_array[$mod->section] == "")
                continue;
            if ($ptop != $mod->section) {
                $activitycounter=0;$completioncounter=0;
    $i=1;
    
                $activitynames="<div id='act' ><div class='act-row'>";
    
     foreach ($arr1[$mod->section] as $modsec) {
                    // var_dump($modsec);
                $len=count($arr1[$mod->section]);//know length of activities array in a section
                    $src=$CFG->wwwroot.'/pix/'.$modsec['modname'].'.jpeg';
                    $activitynames.="<div class='act-cell'><img src=$src alt=".$modsec['modname']." title=".$modsec['modname']." />&emsp;".$modsec['actname']."</div>";
    
                if($i%2==0 && $len!=$i){
                     $activitynames.="</div><div class='act-row other-rows'>";
                 }
               $i++;
                }
                if($i%2==0 ){
                    $activitynames.="</div></div>";
                }
                else
                $activitynames.="</div></div>";
                $i=0;
    
                foreach ($arr[$mod->section] as $modsec) {
                    $activitycounter++;
                    $com+=(int)$modsec['completion'];
                    $tot+=(int)$modsec['count'];
                    $ht .= "<span  >".$modsec['completion'] .' of ' . $modsec['count']."</span>";
                    $src=$CFG->wwwroot.'/pix/'.$modsec['modname'].'.jpeg';
                    $ht .="&emsp;<img src=$src alt=".$modsec['modname']." title=".$modsec['modname']." />";
                    $ht .= "&emsp;&emsp;";
                    if($modsec['completion']==$modsec['count'])
                        $completioncounter++;
    
                }
    
                if($completioncounter==$activitycounter){
                    $classvar='seccompleted';
                    $sta='disabled="true"';
                    //print_r("completed");
                    $sel='';
    
    
    
                }
                else{
                    $classvar='';
                    $sta='';
                    if($che==0)
                    {
                     $sel='checked';
                        $che++;
                    }
                    else{
                        $sel='';
                    }
    
                    //print_r("not completed");
                }
                /*code modified by mahesh -- start*/
                if($classvar){
                    if($topicdisplayflag!=1){
    
    
                    $completedhtml.='';
                    $completedhtml .= html_writer::start_tag('tr', array('class' => $course->id.' '.$classvar));
                    $completedhtml .= html_writer::start_tag('td');
                    $completedhtml .= "<input type='radio' name='topics'  class='rdo' $sel $sta value ='$course->id-$mod->section'/>";
                    $completedhtml .= html_writer::end_tag('td');
                    $completedhtml .= html_writer::start_tag('td');
                    $completedhtml .= "<span >".$top."</span>";
                    $completedhtml .= html_writer::end_tag('td');
                    $completedhtml .= html_writer::start_tag('td');
                    $completedhtml .=$ht;
                    $completedhtml .= html_writer::end_tag('td');
                    $completedhtml .= html_writer::start_tag('td');
                    $completedhtml .=$activitynames;
    
                    $completedhtml .= html_writer::end_tag('td');
                    $activitynames='';
                    $ht='';
                    $completedhtml .= html_writer::end_tag('td');
                    $completedhtml .= html_writer::end_tag('tr');
                        $count++;
                    }else{
                        $ht='';
                    }
                }
                else{
                    if($topicdisplayflag!=0){
    
                    $html .= html_writer::start_tag('tr', array('class' => $course->id.' '.$classvar));
                    $html .= html_writer::start_tag('td');
                    $html .= "<input type='radio' name='topics'  class='rdo' $sel $sta value ='$course->id-$mod->section'/>";
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::start_tag('td');
                    $html .= "<span >".$top."</span>";
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::start_tag('td');
                    $html .=$ht;
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::start_tag('td');
                    $html .=$activitynames;
    
                    $html .= html_writer::end_tag('td');
                    $activitynames='';
                    $ht='';
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::end_tag('tr');
                        $count++;
                    }else{
                        $ht='';
                    }
                }
                /*code modified by mahesh -- end*/
    
    
            }
            $ptop = $mod->section;
        }
        $html.=$completedhtml;//adding closed sections at the end
        $html.="</tbody></table>";
        $html.="<p class='tabres'>($count) results found</p>";
        $sta=$com.','.$tot;
        if($count==0){
            $html=$ehtml."<tr><td colspan='4' style='text-align:center'><div class='nores'><h4> No Chapters found for this course</h4></div></td>
            </tr><tr><td colspan='4' style='padding: 15px 0px  !important; '></td></tr>
            <tr><td colspan='4' style='padding: 15px 0px  !important; ' ></td></tr>
            <tr><td colspan='4' style='padding: 15px 0px  !important; ' ></td></tr>
            <tr><td colspan='4' style='padding: 15px 0px  !important; '></td></tr></tbody></table>";
        }
        $html.="<input type='hidden' id='cstatus' value='$sta'/>";
        return $html;
    
    }
    
    /*
     * to arrage section names store in the section index
     */
    function get_sections($sections)
    {
        $main_array = array();
        $arr = array();
        $main_array = array();
        foreach ($sections as $sec) {
            $main_array[$sec->id] = $sec->name;
        }
        return $main_array;
    }
    
    
    /*
     * get module vise activity count along with completed activities count
     * $mods modules list in a couse
     * $main_array is sections array index represents section id and value at index is section name
     */
    
    function get_activities($mods, $main_array)
    {
        $arr = array();
    
    
        foreach ($mods as $mod) {
            if($mod->module!=1 &&$mod->module!=7) {
                //var_dump(get_string($mod->modname));
                //    var_dump($mod);
    
                if ($main_array[$mod->section] == "")
                    continue;
                //here is the code-----------------------------------------------------------------------------------------------------------------------
                if(get_string($mod->modname) == "Feedback"){
                    echo "";
                }
                else {
                    if (array_key_exists($mod->section, $arr)) {
                        $count = $arr[$mod->section][get_string($mod->modname)]['count'];
                        $comp = (int)$arr[$mod->section][get_string($mod->modname)]['completion'] + (int)getActStatus($mod->id);
                        $arr[$mod->section][get_string($mod->modname)] = array("modname" => get_string($mod->modname), "completion" => $comp, 'count' => ++$count);
                    } else {
                        $arr[$mod->section][get_string($mod->modname)] = array("modname" => get_string($mod->modname), "completion" => getActStatus($mod->id), "count" => 1);
                    }
                }
            }//end of module!=1
        }
    
        return $arr;
    }
    
    
    // new code
    function get_activities_with_names($mods, $main_array)
    {
        $arr = array();
    
    
        foreach ($mods as $mod) {
            if($mod->module!=1 &&$mod->module!=7) {
                //var_dump($mod->name);
                if ($main_array[$mod->section] == "")
                    continue;
                //Here is the code----------------------------------------------------------------------------------------------------------------------------------
                if($mod->modname == "feedback"){
                    echo "";
                }
                else {
                    $arr1[$mod->section][$mod->id] = array("actid" => $mod->id, "name" => $mod->modname, "modname" => get_string($mod->modname), "actname" => $mod->name);
                }
    
            }//end of module!=1
        }
    
        return $arr1;
    }
    
if (isset($_POST['mid'])) {
    $mid = $_POST['mid'];
    $courseid = (int)$_POST['selected_course'];
    $topicid = (int)$_POST['selected_topic'];
    $stdsection = $_POST['selected_section'];
    $topicname=$_POST['ttopicname'];
    $date = $_POST['date'];
    switch ($mid) {
        case 3:
            echo getCourseActivities($courseid);
            break;
        case 10:
            echo getStudentSectionsByCourse($courseid);
            break;
        case 12:
            $topicid = $_POST['selected_topic'];
            $stdsection = $_POST['selected_section'];
            $topicname=$_POST['ttopicname'];
            echo get_students_activity_summary($courseid, $topicid, $stdsection,$topicname);
            break;
        case 4:
            echo getTopicsByCourse($courseid);
            break;
        case 11:
            echo get_students_activity_info($courseid,$topicid,$stdsection);
            break;
        case 13:
            echo get_students_activity_info_report($courseid,$topicid,$stdsection);
            break;
        case 21: 
            echo getCourseTodayPerformance($courseid,$stdsection,$date);
            break;
        case 27: 
            echo getOverallCourseReport($courseid);
            break;
        case 28: 
            echo getTodaysCourseReport($courseid);
                break;
         case 30:
            echo get_students_activity_wise_report($courseid,$topicid,$stdsection);
            break; 
    }
    
}
?>

