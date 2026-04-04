<title>
Elite Exam Students Exam Slips
</title>
<?php

require_once('../config.php');
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course id.
if(isset($_POST['cid'])){
    $id = $_POST["cid"];
}
function checkExamStarted($id){
    global $DB;
    $vpl=$DB->get_field('modules', 'id', array('name'=>'vpl'));
    $quiz=$DB->get_field('modules', 'id', array('name'=>'quiz'));
    $startedActivitiesSql = "SELECT * FROM `mdl_activity_status_tsl` WHERE `status` = 1 and activityid in (SELECT id FROM `mdl_course_modules` WHERE `course` = ".$id." AND `module` IN($vpl,$quiz))";
    $startedActivitiesRes=$DB->get_records_sql($startedActivitiesSql);
    return (count($startedActivitiesRes)>0);
}

//var_dump($id);
//exit(0);
if ($id) {
    $pageparams = array('id' => $id);
} 


$PAGE->set_url('/course/viewexamdetails.php', $pageparams);
if ($id) {
    // Editing course.
    if ($id == SITEID){
        // Don't allow editing of  'site course' using this from.
        print_error('cannoteditsiteform');
    }
    // Login to the course and retrieve also all fields defined by course format.
    $course = get_course($id);
    require_login($course);
    if (user_has_role_assignment($USER->id, 5) || user_has_role_assignment($USER->id, 3) ){
        redirect($CFG->wwwroot);
       
    }
}
else {
    require_login();
    if (user_has_role_assignment($USER->id, 5) || user_has_role_assignment($USER->id, 3) ){
        redirect($CFG->wwwroot);
       
    }
    print_error('needcoursecategroyid');
}

$PAGE->set_title($title);
$PAGE->set_heading($fullname);

function get_student_roomnos_by_course($courseid){
    global $DB;
    $context = context_course::instance($courseid);
    $students = get_role_users(5 , $context);//getting all the students from a course level
    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'roomno'));
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

    $html="<option value='0' >select room</option>";
    $html=$html."<option value='all'>All</option>";
    for($i=0;$i<count($ss);$i++){
        $html=$html."<option value='".$ss[$i]."'>".$ss[$i]."</option>";

    }
    return $html;
}

//echo $OUTPUT->header();
//echo "<h3>Setup Exam : {$course->fullname}</h3>";
echo "<center id='hideinprint'><h3> Seating Arrangement by ROOM & IP  ";
?>
<select name="rooms" id="rooms" onchange="myFunction()" style="padding: 6px;font-size: 14px;text-transform: uppercase;">
<?php echo get_student_roomnos_by_course($id); ?>
</select>


<input type="button" class="button"  value="Print Exam Slips" onClick="javascript:printDiv('preview')" /></h3></center>
<?php
$context = context_course::instance($id);
$students = get_role_users(5 , $context);//getting all the students from a course level

$totalenrolledStudents=count($students);
$teachers = get_role_users(3 , $context);//getting all the teachers from a course level
$totalenrolledteachers=count($teachers);

$ExamPCField=$DB->get_field('user_info_field', 'id', array('shortname'=>'exampc'));
$roomno=$DB->get_field('user_info_field', 'id', array('shortname'=>'roomno'));

function cmp($a, $b) {
    return strcmp($a->username, $b->username);
}

usort($students, "cmp");

function getStudentData($userid,$fieldid){
    global $DB;
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;
    return $studata;
}

function getStudentROOM($userid,$fieldid){
    global $DB;
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;
    return $studata;
}

function updateStudentData($userid,$fieldid,$value){
    global $DB;
    $sql="update `mdl_user_info_data` set `data`='".$value."' FROM  WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
    $result=$DB->execute($sql,null);
    return $result;
}

$permitted_chars = '0123456789';
 
function generate_string($input, $strength = 16) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}

function updateExamCourseID($cid){
    global $DB;
    $query = "update mdl_config set value={$cid} WHERE name='examcourseid'";

    $result=$DB->execute($query,null);
    return $result;
}
function randomPWD($userid){
    global $DB;
    $newpasswordTXT = generate_string($permitted_chars, 8);
    $password = MD5(CONCAT($newpasswordTXT, ''));
    $query = "update mdl_user set p1='',p2={$newpasswordTXT},password={$password} WHERE id={$userid}";
    $result=$DB->execute($query,null);
    return $result;
}
function getP2($userid){
    global $DB;
    return $DB->get_field('user', 'p2', array('id'=>$userid));
}
function getP1($userid){
    global $DB;
    return $DB->get_field('user', 'p1', array('id'=>$userid));
}


?>



 <script type="text/javascript">
 function printDiv(divName)
   {
     window.print();
   }

   const alldivs = document.getElementsByClassName('droppable2');
   function myFunction() {
    var x = document.getElementById("rooms").value;//alert(x);
    if(x=='all'){
        const divs = document.getElementsByClassName('droppable2');

        var lengthOfArray=divs.length;
        for (var i=0; i<lengthOfArray;i++){
            divs[i].style.display='inline-block';
        }
    }
    else{
     divs = document.getElementsByClassName('droppable2');
        var lengthOfArray=divs.length;
        for (var i=0; i<lengthOfArray;i++){
            divs[i].style.display='none';
        }

         divs = document.getElementsByClassName(x);
        var lengthOfArray=divs.length;
        for (var i=0; i<lengthOfArray;i++){
            divs[i].style.display='inline-block';
        }
    }
    
 }

   
 </script>
 <style type="text/css" media="print">
 @media print{ 
     
#preview{ height:100%;overflow:visible;}
 
 #hideinprint{
     display:none;
 }
 .password{display:block !important;}
 .password-fake{display:none !important;}
} 
 </style>
 
  <style>

  #my-list{

  padding: 10px;
  padding-left:15px;
  width:auto;
 margin:auto;
  }
 #my-list > li {
display: inline-block;
zoom:1;
*display:inline;
  }
 #my-list > li > a{
color: #666666;
text-decoration: none;
padding: 3px 8px;
 }
 .password{display:none;}
 .password-fake{display:block;}

 .button {
  background-color: #008CBA; /* blue */
  border: none;
  color: white;
  padding: 8px 16px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 14px;
  margin: 4px 2px;
  cursor: pointer;
}
 </style>

  
   <div id="preview" style="width:1000px; margin:auto;">
   <?php

   if(!checkExamStarted($id)){
                //echo "<p><br/>Exam slips cannot be shown now</p>";
                //exit(0);
    }
   ?>
 <ul id="my-list" >

    <?php  
    $si=1;
    //var_dump($students);
    foreach($students as $student){
       
           $roomclass=getStudentROOM($student->id,$roomno);

        ?>
             <li class="droppable2 <?php echo $roomclass?>"> 



            <div class="droppable2 <?php echo $roomclass?>"  style="border-color:#3300FF; border:solid #999999;  
            height:180px;width:300px;position:relative;margin:5px;display:none;" >
            <div style="float:right;position:absolute; bottom:20px;margin-left: 74%;" class="right">

            <?php


$userobj = get_complete_user_data(id, $student->id);


$timg=$OUTPUT->user_picture($userobj,array('size'=>75, 'alttext'=>$userobj->firstname.' ' .$userobj->lastname,'title'=>'Name:', 'link'=>false));

echo $timg;

?>

             <!--<img  style='height:60;width:60px;float: right;
position: absolute;
bottom: 0px;
left: 225px;
border: 1px solid #dbd7d7;' src="https://st2.depositphotos.com/2783505/8278/i/450/depositphotos_82784040-stock-photo-passport-picture-of-a-cool.jpg" >-->

            </div>
            <div style="padding:5px 10px;">
            <p style="color: #000;  font-size: 12px;
             padding-right:5px; font-weight:800;text-align:center;"><?php echo $course->fullname ?> Login</p>
             <table style="font-size:12px !important;font: caption;" >
             <tr> <td>EXAM URL: </td> <td> <?php echo $CFG->wwwroot ?> </td></tr>
             <tr> <td >NAME: </td> <td> <?php echo ucfirst(str_replace(" KMIT","",$student->firstname)); ?> </td></tr>
             <tr> <td >USERNAME: </td> <td> <?php echo $student->username; ?> </td></tr>
             
              <tr> <td>Room:</td> <td> <?php echo $roomclass; ?></td></tr><!-- Room id-->
              <tr> <td>PC NO: </td> <td> <?php echo getStudentData($student->id,$ExamPCField); ?> </td></tr>
              <tr> <td>OTP:</td> <td><span class="password-fake">*****</span> <span class="password"><?php echo getP2($student->id); ?></span></td></tr>
              
              
              </table>
            </div>
            
          </li>
        <?php   
         
         
        }

      ?>

      </ul>

     </div>


<?php

//echo $OUTPUT->footer();
?>