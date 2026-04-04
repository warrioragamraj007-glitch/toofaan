<?php
/**
 * this will display all participants of a quiz with submissions
 */

require_once('../../../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/local/watchlist/lib.php");
require_once('testcenterutil.php');

$params = explode("-", $_POST['topics']);
$id=(int)$params[0];
$cid=$id;    // courseid
//retriving course id from url

if(!$cid){
    $cid = required_param('cid', PARAM_INT);
    $qid = required_param('actid', PARAM_INT);      //quizid
}
$secname = optional_param('secname', 'All',PARAM_TEXT);
$activityName=optional_param('actname', 'All',PARAM_TEXT);
//$course=get_course($cid);
$subcount=0;
$starCount=0;
$loggedinstudents=0;
$userImages=array();
$userImagesThmb=array();
$userNames=array();
$userIdWithSubTime=array();
$imgcnt=0;
$context = context_course::instance($cid);
$students = get_role_users(5 , $context);//getting all the students from a course level
$loggedinusers=get_loggedin_users_by_section($secname);

if($secname=='All'||$secnmae=='0')
{
    $section_flag=0;
}
else{
    $section_flag=1;
}
$display_flag=1;



//displaying enrolled students


$statusImag='flag-red-icon.png';//not submitted and not graded
$grade='--';
$subtime='--';

$rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
$sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));

foreach($students as $student){

    $rollnumber=''; $stu_section='';
    $rollnumber=getTCStudentData($student->id,$rollfield);
    $stu_section=getTCStudentData($student->id,$sectionfield);

    if($section_flag){

        if(($secname==$stu_section))
        {
            $display_flag=1;
        }
        else{
            $display_flag=0;
        }
    }
    //code to check whether the student is watchlisted or not
    $watchliststatus=getStatus($student->id,$cid);
    if($watchliststatus){
        $watchlist_icon='eye-24-512.png';
    }
    else{
        $watchlist_icon='unwatch-512.png';
    }

    $result=get_user_quiz_grade($qid,$student->id);


    if($display_flag){
        $userparticipation=$DB->get_field('userinfo_tsl', 'loginstatus', array('userid' => $student->id));
        if(($userparticipation==2)||($userparticipation==4)){

            $loggedinstudents++;
            if ($result['grade'] == '--') {
                $statusImag = 'flag-red-icon.png';$statusNum=0;



            } else {
                $subcount++;
                $statusImag = 'flag-green-icon.png';$statusNum=2;
                if((round($result['grade'],2)==100) && $watchliststatus){
                    $statusImag = 'green-star.png';
                    $starCount++;
                    $userImages[$imgcnt]=$OUTPUT->user_picture($student,array('size'=>420, 'alttext'=>$student->firstname.' ' .$student->lastname, 'link'=>false));
                    $uimg=$OUTPUT->user_picture($student,array('size'=>420, 'alttext'=>$student->firstname.' ' .$student->lastname, 'link'=>false));
                    $userNames[$imgcnt]=$student->firstname.' ' .$student->lastname;

                    $userImagesThmb[$imgcnt++]=$OUTPUT->user_picture($student,array('size'=>50, 'alttext'=>$student->firstname.' ' .$student->lastname,'title'=>'Name:', 'link'=>false));
                    $timg=$OUTPUT->user_picture($student,array('size'=>50, 'alttext'=>$student->firstname.' ' .$student->lastname,'title'=>'Name:', 'link'=>false));
                    $userIdWithSubTime[$result['timemodified']]=array('uid'=>$student->id,'uname'=>$student->firstname.' ' .$student->lastname,'uimg'=>$uimg,'timg'=>$timg);

                }


            }//if grade is available
        }//end logged or not checking if
    }//end of display flag

}

echo '
                            </div>
                                <div id="scrollable"></div>
                            </div>';

//print_r($userIdWithSubTime);
//echo '<div></div>';
$faststudents=$userIdWithSubTime;
ksort($faststudents);

//var_dump($userIdWithSubTime);
krsort($userIdWithSubTime);
//var_dump($userIdWithSubTime);

foreach($faststudents as $uiwst){
    //var_dump($uiwst);
}
//print_r($userIdWithSubTime);
echo ' <div class="wrapper">';

if(count($userIdWithSubTime)){

    echo '<div class="connected-carousels">
                <div class="stage" style="background-color: #ffffff;width: 420px !important;height: 470px !important;">
                    <div class="carousel carousel-stage" style="height: 450px !important;">
                        <ul>';
    foreach($userIdWithSubTime as $uiwst){
        echo '<li><div>'.$uiwst['uimg'].'</div><div class="image-caption" style="text-align:center">'.$uiwst['uname'].'</div></li>';
    }
    echo ' </ul>
                    </div>';
    echo '</div>

                <div class="navigation" style="width: 95%;margin-top:15px !important;">
                    <a href="#" class="prev prev-navigation">&lsaquo;</a>
                    <a href="#" class="next next-navigation">&rsaquo;</a>
                    <div class="carousel carousel-navigation" style="width: 98% !important;">
                        <ul>';
    foreach($userIdWithSubTime as $uiwst){
        echo '<li>'.$uiwst['timg'].'</li>';
    }
    echo '  </ul>
                        </div>
                    </div>
                </div>';
}
else{
    echo '<div style="font-size: 22px;font-weight: bold;padding: 100px;">No Red Stars Till Now..</div>';
}
echo '</div>';
echo '<span style="display:none" id="subCount">'.($subcount-$subcount).'</span>';
echo '<span style="display:none" id="gradeCount">'.($subcount-$starCount).'</span>';
echo '<span style="display:none" id="acivitystatus">'.$acivitystatus.'</span>';
echo '<span style="display:none" id="loggedinusers">'.$loggedinstudents.'</span>';
echo '<span style="display:none" id="statusstopdate">'.$acivitystopdate.'</span>';
echo '<span style="display:none" id="cstarCount">'.$starCount.'</span>';
?>
