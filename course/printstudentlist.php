
<title>
 Exam Students List
</title>
<?php

require_once('../config.php');
require_once('lib.php');

$cid = optional_param('id', 0, PARAM_INT); // Course id.
if(isset($_POST['cid'])){
    $cid = $_POST["cid"];
}
//var_dump($id);
//exit(0);
if ($cid) {
    $pageparams = array('id' => $cid);
} 

$PAGE->set_url('/course/viewexamdetails.php', $pageparams);
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
	$html=$html."<option value='A'>A</option>";
$html=$html."<option value='B'>B</option>";
    for($i=0;$i<count($ss);$i++){
        $html=$html."<option value='c".$ss[$i]."'>".$ss[$i]."</option>";
    }
    
    return $html;
}

//echo $OUTPUT->header();
//echo "<h3>Setup Exam : {$course->fullname}</h3>";
echo "<center id='hideinprint'><h3> ".$course->fullname." Exam Students List  ";
?>
<select name="rooms" id="rooms" onchange="myFunction()" style="padding: 6px;font-size: 14px;text-transform: uppercase;">
<?php echo get_student_roomnos_by_course($cid); ?>
</select>
<div id="pageHeader" style="display:none;">
<h3> <?php echo $course->fullname ?> Exam Students List</h3>
</div>

<input type="button" class="button" value="Print Students List" onClick="javascript:printDiv()" /></h3></center>
<?php
$context = context_course::instance($cid);
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
echo '<table style="display:none;">';
echo '<tr><th>HTNO</th><th>Name</th><th>Room NO</th><th>Exam</th></tr>';

foreach($students as $student){
    if($student->id){
        //var_dump($student);
        echo '<tr><td>'.$student->username.'</td>
        <td>'.ucfirst($student->firstname).'</td>
        <td>'.$student->id.'</td>
        <td>'.$course->fullname.'</td>
        
        </tr>';//<td>'.getStudentData($student->id,$ExamPCField).'</td>
    }
}
echo '</table>';



?>



 <script type="text/javascript">




 function printDiv()
   {
    window.print();
   }

   const alldivs = document.getElementsByClassName('droppable2');
   function myFunction() {
    var x = document.getElementById("rooms").value;
    //alert(x);
    if(x=='all'){
        const divs = document.getElementsByClassName('droppable2');

        var lengthOfArray=divs.length;
        for (var i=0; i<lengthOfArray;i++){
            divs[i].style.display='table-row';
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
        //alert(lengthOfArray);
        for (var i=0; i<lengthOfArray;i++){
            divs[i].style.display='table-row';
        }
    }
    
 }
 

   
 </script>
 <style type="text/css" >
 @media print{ 
     #preview{ height:100%;overflow:visible;font-size:16px;}
     thead {display: table-header-group;}
     table { page-break-after:auto }
  tr    { page-break-inside:avoid; page-break-after:auto }
  td    { page-break-inside:avoid; page-break-after:auto }
  
 #hideinprint{
     display:none;
 }
 
 table {
        border: solid #000 !important;
        border-width: 1px 0 0 1px !important;
    }
    th, td {
        border: solid #000 !important;
        border-width: 0 1px 1px 0 !important;
        font-size:14px;
    }
   
  }

 }



 #customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}



#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color:#012951;
  color: white;
}

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

  
   <div id="preview" style="width:100%; margin:auto;">


    <?php  



echo "<table id='customers' style='font-size:12px;font: caption;width:100%; margin:auto;'>";
echo "<thead><tr><th>Roll No</th><th>Student Name</th><th>Room No</th><th>PC No</th><th>Exam</th></tr></thead>";//<th>IP Address</th>

    foreach($students as $student){
      
           $roomclass=getStudentROOM($student->id,$roomno);
           $place=getStudentROOM($student->id,$ExamPCField);
        ?>
             <tr class='droppable2 <?php echo "c".$roomclass?>   <?php echo substr($roomclass,0,1) ?>'> 
<td style='width:10%;'> <?php echo strtoupper($student->username); ?> </td> 
<td style='width:25%;'> <?php echo $student->firstname; ?> </td>
               <td style='width:5%;'> <?php echo  $roomclass; ?></td> 
                              <td style='width:5%;'> <?php echo  $place; ?></td> 
                       <td style='width:20%;'><?php echo $course->fullname ?></td>
               <!--<td style='width:20%;'> <?php //echo getStudentData($student->id,$ExamPCField); ?> </td>-->
               </tr>
              
          
        <?php   
         
         
        }

        echo "</table>";


      ?>

    

     </div>


<?php

//echo $OUTPUT->footer();
?>