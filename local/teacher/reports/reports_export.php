<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/local/watchlist/lib.php');
class custom_export_report_db{


    function getStudentsByCourse($courseId)
    {


        $courses = get_courses();
        $context = context_course::instance($courseId);//course id
        $students = get_role_users(5, $context); //student context
        return   $students;
    }
    function studnetsBySubmission($students,$courseid,$activityId,$submissionType)
    {

      global $DB;
        $ss=array();

        foreach ($students as $student)

        {
            $sub="notsubmitted";
            $getvplinstance=$DB->get_field_sql("SELECT `instance`
FROM `mdl_course_modules`
WHERE `id` ='$activityId'
AND `course` ='$courseid'");

            $submissions=$DB->get_fieldset_sql("SELECT  `datesubmitted`
FROM `mdl_vpl_submissions`
WHERE `vpl` ='$activityId'
AND `userid` ='$student->id'");
//var_dump($submissions);
            if(!empty( $submissions))
            {
                $sub="submitted";
            }
            if(strcasecmp($submissionType,$sub)==0)
            {
                $std= new stdClass;
                $std->id=$student->id;

                $ss[$student->id]=$std;
            }


        }

        return $ss;
    }
    function getGradeByActivity($students,$courseId,$actvalues,$rank,$course_name,    $category_name, $section_name)
    {
        global $DB, $CFG;
        $actValues = explode("@", $actvalues);
        $actValues=array_filter( $actValues);
        $act_name="";
if(empty($category_name))
$category_name="All";
if(empty($section_name))
$section_name="All";
        if(empty(count(  $actValues))){

        }
        else {

            /******************GETTING NAMES OF ACTIVITIES *********************/
            $i = 0;
            foreach ($actValues as $act) {
                $i = $i + 1;
                $current_act = explode("-", $act);

                $act_name1 = $DB->get_field_sql("SELECT `name`
FROM `mdl_$current_act[1]`
WHERE `id` ='$current_act[0]'");

                if ($i == 1) {
                    $act_name = $act_name1;
                } else {
                    $act_name = $act_name . ":" . $act_name1;
                }
            }

            $result = array();

            //    var_dump($students);
            foreach ($students as $student) {

                $final_grade = -1;
                $total_activities = count($actValues);
                $userobj = get_complete_user_data(id, $student->id);
                $watchlist = getStatus($student->id, $courseId);
                if (empty($watchlist)) {
                    $watchlist = 0;
                }
                $i = 0;
                $att = "";
                foreach ($actValues as $act) {
                    $current_act = explode("-", $act);
                    /******** ATTENDENACE START **************
                     */

                    if (count($actValues) == 1) {

                        $modtypeid= $DB->get_field_sql("SELECT `id`
   FROM `mdl_modules`
   WHERE `name`='$current_act[1]'");

                        $act_id=$DB->get_field_sql("SELECT `id`
FROM `mdl_course_modules`
WHERE `instance` ='$current_act[0]' AND `module` ='$modtypeid'");

                        $sql = 'SELECT id FROM `mdl_std_activity_attend_tsl`
							WHERE `cid` =' . $courseId . '	AND `studentid` =' . $student->id . '
							AND `aid` =' . $act_id;

                        if (count($DB->get_records_sql($sql, null))) {
                            $att = 'ABSENT';
                        } else {
                            $att = 'PRESENT';
                        }


                    }


                    /**************ATTENDENCE END ************/
                    $grading_info = grade_get_grades($courseId, 'mod', $current_act[1], $current_act[0], $student->id);

                    $item = $grading_info->items[0];

                    $gradeI = $item->grades[$student->id];


                    $grade1 = $gradeI->grade;

                    if (empty($grade1)) {
                        //echo  $grade1 ."".$userobj->firstname.'</br>';
                    } else {

                        $i = $i + 1;
                    }
                    $final_grade = $final_grade + $grade1;


                }

                if ($i >= 1) {
                    $final_grade = $final_grade + 1;
                }

                if ($final_grade > 0) {

                    $final_grade = $final_grade / $total_activities;
                    $final_grade = round($final_grade, 2);
                }
                $grade = $final_grade;
//echo  $grade1 ."".$userobj->firstname.'</br>';

//echo $total_activities;
                if (empty($att) || $att == "PRESENT") {
                    $att = "NG";
                }
                if (empty($userobj->profile['rollno']))
                    $userobj->profile['rollno'] = "--";

                if (empty($userobj->firstname))
                    $userobj->firstname = "--";


                if (empty($userobj->profile['eamcetrank']))
                    $userobj->profile['eamcetrank'] = "--";
                if (empty($userobj->profile['dept']))
                    $userobj->profile['dept'] = "--";
                if (empty($userobj->profile['section']))
                    $userobj->profile['section'] = "--";
                /*******************geting students only based on rank
                 **************************************************/

                if ($rank == "") {//if he didnt select rank
                    if (
                        $grade >= 0
                    ) {

                        // $result['a']="hii";

                        $result[$student->id] = array(
                            'id' => $student->id,
                            'rollno' => $userobj->profile['rollno'],
                            'firstname' => $userobj->firstname,
                            'grade' => $grade,
                            'gt' => "graded",
                            'rank' => $userobj->profile['eamcetrank'],

                            'dept' => $userobj->profile['dept'],
                            'section' => $userobj->profile['section'],

                            'watchlist' => $watchlist,
                            'coursename' => $course_name,
                            'catname' => $category_name,
                            'topicname' => $section_name,

                            'actname' => $act_name);

                    } else {


                        $result[$student->id] = array(
                            'id' => $student->id,
                            'rollno' => $userobj->profile['rollno'],

                            'firstname' => $userobj->firstname,
                            'grade' => $att,
                            'gt' => "notgraded",

                            'rank' => $userobj->profile['eamcetrank'],
                            'dept' => $userobj->profile['dept'],
                            'section' => $userobj->profile['section'],

                            'watchlist' => $watchlist,
                            'coursename' => $course_name,
                            'catname' => $category_name,
                            'topicname' => $section_name,
                            'actname' => $act_name);

                    }


                } else //selects a rank range
                {
                    $rankArr = explode(":", $rank);
                    //     echo "1".$rankArr[0];
                    for ($i = 1; $i < count($rankArr); $i++) {
                        //echo "Range".$rankArr[$i];
                        $rankRange = explode("-", $rankArr[$i]);
                        $r = $userobj->profile['eamcetrank'];
                        if ($rankRange[1] == 'above') {
                            $con = $r >= $rankRange[0];
                        } else {
                            $con = ($r >= $rankRange[0]) && ($r <= $rankRange[1]);
                        }
                        if ($con) // true
                        {
                            if ($grade >= 0) {

                                // $result['a']="hii";

                                $result[$student->id] = array(
                                    'id' => $student->id,
                                    'rollno' => $userobj->profile['rollno'],
                                    'firstname' => $userobj->firstname,
                                    'grade' => $grade,
                                    'gt' => "graded",
                                    'rank' => $userobj->profile['eamcetrank'],

                                    'dept' => $userobj->profile['dept'],
                                    'section' => $userobj->profile['section'],

                                    'watchlist' => $watchlist,
                                    'coursename' => $course_name,
                                    'catname' => $category_name,
                                    'topicname' => $section_name,
                                    'actname' => $act_name);


                            } else {
//$result['b']="hii";


                                $result[$student->id] = array(
                                    'id' => $student->id,
                                    'rollno' => $userobj->profile['rollno'],

                                    'firstname' => $userobj->firstname,
                                    'grade' => $att,

                                    'gt' => "notgraded",
                                    'rank' => $userobj->profile['eamcetrank'],
                                    'dept' => $userobj->profile['dept'],
                                    'section' => $userobj->profile['section'],

                                    'watchlist' => $watchlist,
                                    'coursename' => $course_name,
                                    'catname' => $category_name,
                                    'topicname' => $section_name,
                                    'actname' => $act_name);

                            }


                        }
                    }
                }
            }

        }


        return $result;
    }
   /*----------------------------------------------------------------------------render end--------------------------------*/
    function renderOutput($resultObj,$dept,$section,$grade_type,$watchlist)
    {
        //echo "Grade Type ".$grade_type;
        //echo "section ".$section;
        global $CFG;
        $result="";
        foreach($resultObj as $student)
        {
            $stdid=$student['id'];
           //Client didnt select anything
            if(empty($dept)&&empty($grade_type)&&empty($section)&&$watchlist=='undefined'){


                $result[$stdid] = array('rollno' => $student['rollno'],

                    'firstname' => $student['firstname'],
                    'grade' => $student['grade'],


                    'rank' => $student['rank'],
                    'dept' => $student['dept'],
                    'section' =>$student['section'],

                    'watchlist' => $student['watchlist'],
                    'coursename'  => $student['coursename'],
                    'catname'  => $student['catname'],
                    'topicname'  =>$student['topicname'],
                    'actname'=>$student['actname']);

            }

            // client selected only department

            if($dept!=''&&empty($grade_type)&&empty($section)&&$watchlist=='undefined'){

                if(strcasecmp($student['dept'] ,$dept)==0)
                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            if(empty($dept)&&(!empty($grade_type))&&empty($section)&&$watchlist=='undefined'){
                if( strcasecmp($student['gt'] ,$grade_type)==0)
                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
// SELECTED ONLY WATCHLIST

            if(empty($dept)&&(empty($grade_type))&&empty($section)&&$watchlist!='undefined'){
                if($student['watchlist']==$watchlist)
                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }

// SELECTED ONLY section

            if(empty($dept)&&(empty($grade_type))&&!empty($section)&&$watchlist=='undefined'){
                if(strcasecmp($student['section'] ,$section)==0)

                {

                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }


            /**************** IF HE SELECTS DEPT,GRADE TYPE,section AND WATCHLIST
             ************************************/
            if((!empty($dept))&&(!(empty($grade_type)))&&(!empty($section))&&$watchlist!='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&
                    ($student['watchlist']==$watchlist)&&(
                        strcasecmp($student['section'] ,$section)==0)&&
                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT,GRADE TYPE
             ************************************/
            if((!empty($dept))&&(!(empty($grade_type)))&&(empty($section))&&$watchlist=='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&

                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT,GRADE TYPE,section
             ************************************/
            if((!empty($dept))&&(!(empty($grade_type)))&&(!empty($section))&&$watchlist=='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&(
                        strcasecmp($student['section'] ,$section)==0)&&
                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT AND section
             ************************************/
            if((!empty($dept))&&(empty($grade_type))&&(!empty($section))&&$watchlist=='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&

                    (strcasecmp($student['section'] ,$section)==0))

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT AND WATCHLIST
             ************************************/
            if((!empty($dept))&&(empty($grade_type))&&empty($section)&&$watchlist!='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&(
                        $watchlist==$student['watchlist']))

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT,GRADE TYPE AND WATCHLIST
             ************************************/
            if((!empty($dept))&&(!(empty($grade_type)))&&empty($section)&&$watchlist!='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&
                    ($student['watchlist']==$watchlist)&&
                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS GRADE TYPE,section AND WATCHLIST
             ************************************/
            if((empty($dept))&&(!(empty($grade_type)))&&(!empty($section))&&$watchlist!='undefined'){

                if(
                    ($student['watchlist']==$watchlist)&&(
                        strcasecmp($student['section'] ,$section)==0)&&
                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }

            /**************** IF HE SELECTS GRADE TYPE AND WATCHLIST
             ************************************/
            if((empty($dept))&&(!(empty($grade_type)))&&(empty($section))&&$watchlist!='undefined'){

                if(
                    ($student['watchlist']==$watchlist)&&
                    strcasecmp($student['gt'] ,$grade_type)==0)

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS section AND WATCHLIST
             ************************************/
            if((empty($dept))&&(empty($grade_type))&&(!empty($section))&&$watchlist!='undefined'){

                if(
                    $student['watchlist']==$watchlist&&(
                        strcasecmp($student['section'] ,$section)==0))

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
            /**************** IF HE SELECTS DEPT,section AND WATCHLIST
             ************************************/
            if((!empty($dept))&&(empty($grade_type))&&(!empty($section))&&$watchlist!='undefined'){

                if(
                    strcasecmp($student['dept'] ,$dept)==0&&
                    ($student['watchlist']==$watchlist)&&(
                        strcasecmp($student['section'] ,$section)==0))

                {


                    $result[$stdid] = array('rollno' => $student['rollno'],

                        'firstname' => $student['firstname'],
                        'grade' => $student['grade'],


                        'rank' => $student['rank'],
                        'dept' => $student['dept'],
                        'section' =>$student['section'],

                        'watchlist' => $student['watchlist'],
                        'coursename'  => $student['coursename'],
                        'catname'  => $student['catname'],
                        'topicname'  =>$student['topicname'],
                        'actname'=>$student['actname']);
                }
            }
        }
        return    $result;
    }
}
//$repObj=new custom_export_report_db();
//$studnets=$repObj->getStudentsByCourse("19");
//$resultArr=$repObj->getGradeByActivity($studnets,"19","33-quiz","","abx","cde","zyz");
