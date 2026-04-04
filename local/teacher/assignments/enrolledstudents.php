<?php

require_once dirname(__FILE__).'/../../../config.php';
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/local/watchlist/lib.php");
require_once('../testcenter/testcenterutil.php');
// $PAGE->requires->js('/theme/universo/javascript/jquery-2.1.0.min.js');

// srivardhin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // grade submission for students
    $method =$_POST['method'];
    if($method == '1') {
    $studentId = $_POST['student_id'];
    $assignId = $_POST['assignid'];
    $grade = $_POST['grade'];
    $currentTime = time();
   
    // $grader = "5";
    $aitemid = getitemid($assignId); 
    //  var_dump($aitemid);
    // Check if the grade already exists
    $checkQuery = "SELECT * FROM mdl_assign_grades WHERE userid = '$studentId' AND assignment = '$assignId'";
    $result = $DB->get_records_sql($checkQuery);

    if ($result) {
        // If the grade exists, update it
        $updateQuery = "UPDATE mdl_assign_grades SET grade = '$grade', timemodified = '$currentTime' WHERE userid = '$studentId' AND assignment = '$assignId'";
        $res = $DB->execute($updateQuery);
    } else {
        // If the grade does not exist, insert a new one
        $insertQuery = "INSERT INTO mdl_assign_grades (userid, assignment, grade, timecreated, timemodified) VALUES ('$studentId', '$assignId', '$grade', '$currentTime', '$currentTime')";
        $res = $DB->execute($insertQuery);
    }
    if ($aitemid) {
        // If the itemid exists, update it. updating final grades
        $updateQuery = "UPDATE mdl_grade_grades SET finalgrade= '$grade', timemodified = '$currentTime' WHERE userid = '$studentId' AND itemid = '$aitemid'";
    //    var_dump($updateQuery);
        $res = $DB->execute($updateQuery);

    }
    if ($res) {
        echo "Grade submitted successfully.";
    } else {
        echo "Error submitting grade.";
    }
    exit;
}
if($method == '2') {
    // feeback comments for students
    $studentId = $_POST['student_id'];
    $assignId = $_POST['assignid'];
    $feedback = $_POST['feedback'];
     $commentformat = "1";
    
    $ugradeid = getGradeid($studentId,$assignId);   
    // echo $ugradeid;
    // echo $feedback;
     // Check if the feedback already exists
    $checkQuery = "SELECT * FROM mdl_assignfeedback_comments WHERE grade = '$ugradeid' AND assignment = '$assignId'";
    $result = $DB->get_records_sql($checkQuery);

    if ($result) {
        // If the feedback exists, update it
        $updateQuery = "UPDATE mdl_assignfeedback_comments SET commenttext = '$feedback' WHERE grade = '$ugradeid' AND assignment = '$assignId'";
        $res = $DB->execute($updateQuery);
    } else {
        // If the feedback does not exist, insert a new one
        $insertQuery = "INSERT INTO mdl_assignfeedback_comments ( assignment, grade,commenttext) VALUES ('$assignId', '$ugradeid', '$feedback')";
        $res = $DB->execute($insertQuery);
    }

    if ($res) {
        echo "feedback submitted successfully.";
    } else {
        echo "feedback submitting grade.";
    }
    exit;
}
}



$params = explode("-", $_POST['topics']);
$id=(int)$params[0];
$cid=$id;
//retriving course id from url
//$cid = required_param('cid', PARAM_INT);
if(!$cid){
$cid = required_param('cid', PARAM_INT);
}

$secname = optional_param('secname', 'All',PARAM_TEXT);

if($secname=="0")
{
    $secname='All';
}
// echo '<button id="downloadXlsx">Download  XLSX</button>';


$assignid =optional_param('instanceid',0,PARAM_INT);
$actid =optional_param('actid',0,PARAM_INT);
//$course=get_course($cid);
//echo $actid;
$context = context_course::instance($cid);
$students = get_role_users(5 , $context);//getting all the students from a course level
$loggedinusers=get_loggedin_users_by_section($secname);

$rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
$sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
// srivardhin
function getGradeid($userid,$assignid){
    global $DB;
   
    $sql="SELECT id FROM `mdl_assign_grades` where userid='$userid' and assignment='$assignid'";
    // echo $sql;
    $assignres=$DB->get_record_sql($sql);
    if($assignres){
        $gradeid=$assignres->id;
    }
    return $gradeid; 
}
function getitemid($assignid){
    global $DB;
   
    $sql="SELECT id FROM `mdl_grade_items` where iteminstance='$assignid' and itemmodule='assign'";
    // echo $sql;
    $assignres=$DB->get_record_sql($sql);
    if($assignres){
        $itemid=$assignres->id;
    }
    return $itemid; 
}
function getsubmissionfiles($userid,$assignid){
    global $DB;
   
     $sql="
     SELECT 
    f.id, 
    f.contenthash, 
    f.filename, 
    f.filepath, 
    f.mimetype, 
    f.filesize,
    f.component,
    f.contextid,
    f.itemid,
    f.filearea
FROM 
    mdl_files f
JOIN 
    mdl_assign_submission s ON f.itemid = s.id
WHERE 
    s.assignment = '$assignid'
    AND s.userid = '$userid'
    AND f.component = 'assignsubmission_file'
    AND f.filearea = 'submission_files'
    and f.mimetype != 'null'
    AND s.timemodified = (
        SELECT MAX(s2.timemodified)
        FROM mdl_assign_submission s2
        WHERE s2.assignment = s.assignment
        AND s2.userid = s.userid
    );

     ";
    $assignres=$DB->get_record_sql($sql);
    if($assignres){
        $contenthash=$assignres->contenthash;
        $contextid = $assignres->contextid;
          $component = $assignres->component;
         $filearea = $assignres->filearea;
         $itemid = $assignres->itemid;
        $filename = $assignres->filename;
       $filepath = $assignres->filepath;
    }
    return array( $contenthash,$contextid, $component,$filearea , $itemid,$filename, $filepath);
}
function getUserAssignGrade($userid,$assignid){
    global $DB;
    $usergrade=$gradetime='--';
    $sql="SELECT * FROM `mdl_assign_grades` where userid='$userid' and assignment='$assignid'";
    //echo $sql;echo '<br/>';
    $assignres=$DB->get_record_sql($sql);
    //var_dump($assignres->grade);
    if($assignres){
        $usergrade=round($assignres->grade,2);
        $gradetime=subdate($assignres->timemodified);
    }
    return array($usergrade,$gradetime);
}

function getUserAssignSubtime($userid,$assignid){
    global $DB;
    $usersubtime=array('--','--');
    $sql="SELECT * FROM `mdl_assign_submission` where userid='$userid' and assignment='$assignid'";
    //echo $sql;echo '<br/>';
    $assignres=$DB->get_record_sql($sql);
    //var_dump($assignres);
    if($assignres){
        $usersubtime=array($assignres->status,$assignres->timemodified);
    }
    return $usersubtime;
}
function getUserAssignLockFlag($userid,$assignid){
    global $DB;
    $userlockflag=0;
    $sql="SELECT * FROM `mdl_assign_user_flags` where userid='$userid' and assignment='$assignid'";
    //echo $sql;echo '<br/>';
    $assignres=$DB->get_record_sql($sql);
    //var_dump($assignres);
    if($assignres){
        $userlockflag=$assignres->locked;
    }
    return $userlockflag;
}
function getUserAssignComments($userid,$assignid){
    global $DB;
    $teacherfeedback='--';

    $sql="SELECT commenttext FROM `mdl_assignfeedback_comments` where grade = (select id FROM `mdl_assign_grades` where userid='$userid' AND assignment='$assignid')";
    $assignres=$DB->get_record_sql($sql);
    //echo $sql;
    //var_dump($assignres);
    if($assignres){
        $teacherfeedback=$assignres->commenttext;
    }
    return $teacherfeedback;

}

function getUseronlinetext($userid,$assignid){
    global $DB;
    $onlinetext='--';

    $sql="SELECT onlinetext FROM `mdl_assignsubmission_onlinetext` where submission = (select id FROM `mdl_assign_submission` where userid='$userid' AND assignment='$assignid')";
    $res=$DB->get_record_sql($sql);
    //echo $sql;
    //var_dump($assignres);
    if($res){
        $onlinetext=$res->onlinetext;
    }
    return $onlinetext;

}
function subdate($timesec){
    return date("d-m-y  h:i A",$timesec);
}

	if($secname=='All'||$secnmae=='0')
	{
	$section_flag=0;
	}
	else{
	$section_flag=1;
	}
	$display_flag=1;


                //displaying enrolled students
                echo '<div class="repo" style="">
                    <div id="container" style="padding-bottom:30px;" >


                        <table id="myTable"  class="CSSTableGenerator table table-hover course-list-table tablesorter" >
                            <thead>
                            <tr>
                                <th  class="header" style="text-align:center;width: 7%">Status</th>
                                <th class="header">Roll No</th>
                                <th class="header">Full Name</th>
                                <th class="header" style="text-align:center;width: 8%">Section</th>
                                <th class="header" style="width: 14%">Last Submission</th>
                                <th class="header" style="text-align:center;width: 12%">Grade(%)</th>
                                 <th class="header" style="text-align:center">Feedback Comments</th>
                                 <th class="header" style="text-align:center">Student Submission files</th>
                                 <th class="header" style="text-align:center">Student online text</th>
                            </tr>
                            </thead>
                            <tbody >';

                            $statusImag='flag-red-icon.png';//not submitted and not graded
                            $statusNum=0;
                            $grade='--';

			                $usersubcount='--';
                            $loggedinstudents=0;
                            $watchCount=0;
                            $subCount=0;
                            $gradeCount=0;
                            $starCount=0;

                        foreach($students as $student){

                            $rollnumber=''; $stu_section='';$statusImag='flag-red-icon.png';//not submitted and not graded
                            $subtime='--';$teacherfeedback='--';$statusNum=0;$subtimesec=0;$gradetime='--';$statusMsg="Not Submitted";
                            if($CFG->optimize){
                                $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$student->id."' AND `fieldid` ='".$rollfield."'";
                                $roll=$DB->get_record_sql($sql);
                                $rollnumber=$roll->data;

                                $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$student->id."' AND `fieldid` ='".$sectionfield."'";
                                $sec=$DB->get_record_sql($sql);
                                $stu_section=$sec->data;
                            }else{
                                if(get_complete_user_data(id,$student->id)->profile['rollno']){
                                    $rollnumber=get_complete_user_data(id,$student->id)->profile['rollno'];
                                }
                                else{
                                    $rollnumber='';
                                }
                                if(get_complete_user_data(id,$student->id)->profile['section']){
                                    $stu_section=get_complete_user_data(id,$student->id)->profile['section'];
                                }
                                else{
                                    $stu_section='';
                                }
                            }



				if($section_flag){
	
				if(($secname==$stu_section))
				{
				$display_flag=1;
				}
				else{
				$display_flag=0;
				}
				}


   	if($display_flag){



       // $userparticipation=$DB->get_field('userinfo_tsl', 'loginstatus', array('userid' => $student->id));
       // if(($userparticipation==2)||($userparticipation==4)){

            //code to check whether the student is watchlisted or not
           /* $watchliststatus=getStatus($student->id,$cid);
            if($watchliststatus){
                $watchlist_icon='eye-24-512.png';$watchCount++;
            }
            else{
                $watchlist_icon='unwatch-512.png';
            }*/
        if($assignid){
            $fgrade=getUserAssignGrade($student->id,$assignid);
            $grade=$fgrade[0];
            $gradetime=$fgrade[1];
            $submission=getUserAssignSubtime($student->id,$assignid);
            $lockflag=getUserAssignLockFlag($student->id,$assignid);
            $teacherfeedback=getUserAssignComments($student->id,$assignid);
            $onlinetext= getUseronlinetext($student->id,$assignid);
            $teacherfeedback=(strlen($teacherfeedback)>0)?$teacherfeedback:'--';
            
            // echo $student->firstname;
            //var_dump($submission);echo '<br/>';
        //    $ugradeid = getGradeid($student->id,$assignid);
        //    echo $ugradeid;
        }


        $lockflagclass="fa-unlock-alt";
        if($lockflag){
            $lockflagclass="fa-lock";
        }
        if($submission[0]=='submitted'){
            $statusImag='flag-orange-icon.png';
            $statusMsg="Submitted";
            $subtime=subdate($submission[1]);
            $subtimesec=$submission[1];
            $subCount++;
            $statusNum=1;
        }
        //echo $student->firstname.'$'.$grade.'$$$$'.(is_numeric($grade)).'<br/>';

        if(is_numeric($grade)){
            $statusImag='flag-green-icon.png';
            $statusMsg="Graded";
            $gradeCount++;
            $statusNum=2;
            if($grade==100){
                $statusImag='green-star.png';
                $statusMsg="Green Stared";
                $starCount++;
                $statusNum=3;
            }
        }

        $tfeedback=$commentmore='';
                $otext=$more='';
        if((strlen(strip_tags($teacherfeedback))<30)){
            $tfeedback=strip_tags($teacherfeedback);
        }else{
            $tfeedback=substr(strip_tags($teacherfeedback), 0, 30);
            $commentmore='<span title="show comment" data-uid="'.$student->id.'" data-uname="'.$student->firstname.' '.$student->lastname .'"  class="comments">more</span>';
        }
        if((strlen(strip_tags($onlinetext))<30)){
            $otext=strip_tags($onlinetext);
            // var_dump($otext);
        }else{
            $otext=substr(strip_tags($onlinetext), 0, 30);
            // var_dump($otext);
            $more='<span title="show Onlinetext" data-uid="'.$student->id.'" data-uname="'.$student->firstname.' '.$student->lastname .'"  class="onlinetext">more</span>';
        }
        $file =  getsubmissionfiles($student->id,$assignid);
$contenthash=$file[0];
        $contextid =$file[1];
          $component = $file[2];
         $filearea = $file[3];
         $itemid =$file[4];
        $filename = $file[5];
       $filepath = $file[6];
       $file_url = "{$CFG->wwwroot}/pluginfile.php/{$contextid}/{$component}/{$filearea}/{$itemid}{$filepath}{$filename}";
        // $ugradeid = getGradeid($userid,$assignid);
        //var_dump($teacherfeedback);
        // sort($rollnumber);
// var_dump()
            $loggedinstudents++;
                            echo '<tr>
                                <td style="width: 7%"><span  style="display:none">'.$statusNum.'</span>
                                <img title="'.$statusMsg.'" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$statusImag.'" width="  16px" /></td>';
                                // foreach ($rollnumber as $rollnumbers) {

                               echo ' <td><span  style="display:none">'.$rollnumber.'</span>'.$rollnumber.'</td>';
                                // }
                             echo '   <td><span  style="display:none">'.$student->firstname.' '.$student->lastname.'</span>'.$student->firstname.' '.$student->lastname.'</td>
                                <td style="text-align:center"><span  style="display:none">'.$stu_section.'</span>'.$stu_section.'</td>
                                <td style="text-align:left"><span style="display:none">'.$subtimesec.'</span>
                                <div class="subdiv" style="width:100%;text-align:center">
                                <span class="actbtns" >('.$subtime.')</span>
                                <span class="actbtns-l" >
                                <i title="Prevent Submissions" id="'.$student->id.'" class="lockunlock fa fa-unlock-alt unlock'.$student->id.'';
        if($lockflag==0){echo ' lockshow';}else{echo ' lockhide';}
        echo '" data-actid="'.$actid.'" data-lockflag="0"></i>
                                <i title="Allow Submissions" id="'.$student->id.'" class="lockunlock fa fa-lock lock'.$student->id.'';
        if($lockflag==1){echo ' lockshow';}else{echo ' lockhide';}
        echo '" data-actid="'.$actid.'" data-lockflag="1"></i>
                                </span>
                                </div>
                                </td>

                                <td style="text-align:left">
                                <span style="display:none">'.$grade.'</span>
                                <div class="gradediv" style="width:100%">
                                <div  style="width:50%;float:left">
                                <div class="gradeactbtns grade" >'.$grade.'</div>


                                </div>
                                <div  style="width:50%;float:left">
                                 <div class="evaluate" title="Evaluate Submission">';
                                 if ($submission[0] == 'submitted') {
                           echo'     <a href="javascript:void(0)" onclick="editGrade(this, ' . $student->id . ', \'' . $grade . '\', \'' . $assignid . '\')" style="cursor:pointer">
            ';

                                // if(!is_numeric($grade)){
                                //         echo '<i title="Evaluate" class="fa fa-pencil-square-o disabled" aria-hidden="true"></i>';
                                // }else{
                                //     echo '<i title="Re-evaluate" class="fa fa-pencil-square" aria-hidden="true"></i>';
                                // }

                                // if ($submission[0] == 'submitted') {
                                    if (!is_numeric($grade)) {
                                        echo '<i title="Evaluate" class="fa fa-pencil-square-o" aria-hidden="true"></i>';
                                    } else {
                                        echo '<i title="Re-evaluate" class="fa fa-pencil-square" aria-hidden="true"></i>';
                                    }
                                    echo '</a>';
                                } else {
                                    // Handle the case when submission is not 'submitted'
                                    echo '<i title="no submission" class="fa fa-pencil-square-o" style=" color: #ccc; cursor: not-allowed; " aria-hidden="true"></i>';
                                }
                                
                               echo '</div>
                                </div>

                                <div class="gradetime gradeactbtns">('.$gradetime.')</div>
                                </div>
</td>
				<!--<td><span class="watchlist-status'.$student->id.'" style="display:none">'.$watchliststatus.'</span>
				<img data-ref="'.$watchliststatus.'" id="'.$student->id.'" class="watchlist" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$watchlist_icon.'" width="  16px"/></td>-->

                                <td><span class="current-feedback">'.$tfeedback.'</span>'.$commentmore.'
    <div class="comment'.$student->id.'" style="display:none">'.$teacherfeedback.'</div>
    <div class="fa fa-pencil-square" style="margin-left:30%" onclick="editFeedback(this, ' . $student->id . ', \'' . $tfeedback . '\', \'' . $assignid . '\')" title="Edit feedback"></div> </td>
       <td>   <a href="'.$file_url.'" target="_blank">'.$filename.'</a>
</td>
<td><span class="online_text">'.$otext.'</span>'.$more.'
    <div class="online'.$student->id.'" style="display:none">'.$onlinetext.'</div>
    </td>
                            </tr>';
       // }//if students are logged in $small = substr($teacherfeedback, 0, 100);
			}
                        }

                        echo '</tbody>
                                </table>
				
                            </div>
                                <div id="scrollable"></div>
                            </div>';
echo '<span style="display:none" id="loggedinusers">'.$loggedinstudents.'</span>';
echo '<span style="display:none" id="watchCount">'.$watchCount.'</span>';
echo '<span style="display:none" id="statusstopdate">'.$acivitystopdate.'</span>';

echo '<span style="display:none" id="subCount">'.($subCount-$gradeCount).'</span>';
echo '<span style="display:none" id="gradeCount">'.($gradeCount-$starCount).'</span>';
echo '<span style="display:none" id="cstarCount">'.($starCount-$redstarCount).'</span>';
echo '<span style="display:none" id="crstarCount">'.($loggedinstudents-($subCount)).'</span>';
?>
<!-- <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-2.js"></script> -->

<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/xlsx.full.min.js"></script>

<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery-2.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.datatable.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.tablesorter.js"></script>
<script>
    console.log('XLSX:', XLSX);
console.log('XLSX.utils:', XLSX?.utils);
console.log('XLSX.utils.table_to_sheet:', XLSX?.utils?.table_to_sheet);

    function editGrade(element, studentId, currentGrade, assignId) {
    var row = element.closest('.gradediv');
    var gradeDiv = row.querySelector('.gradeactbtns.grade');
    var evaluateIcon = row.querySelector('.evaluate i');
    
    // Hide the evaluate icon
    if (evaluateIcon) {
        evaluateIcon.style.display = 'none';
    }
    
    // Make the grade editable
    gradeDiv.innerHTML = '<input type="text" id="grade_input_' + studentId + '" value="' + currentGrade + '" style="width: 70px;"oninput="validategrade(this)">' +
                         '<div class="fa fa-check" onclick="submitGrade(' + studentId + ', \'' + assignId + '\')" style="cursor: pointer; margin-left: 5px;" title="Save grade"></div>';
        
       // input grade when enter key is clicked 
var gradeInput = document.getElementById('grade_input_' + studentId);

// Add an event listener for the 'keydown' event
gradeInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        submitGrade(studentId, assignId);
    }
});
                        }

                        function validategrade(input) {
    var value = input.value;
    if (value === "") return;
    if (!/^\d+$/.test(value)) {
        // Remove non-numeric characters
        input.value = value.replace(/\D/g, '');
    } else if (parseInt(value) < 0) {
        input.value = 0;
    } else if (parseInt(value) > 100) {
        input.value = 100;
    }
}
function editFeedback(iconElement, studentId, currentFeedback, assignId) {
    var feedbackDiv = iconElement.previousElementSibling;
    var currentFeedbackSpan = iconElement.previousElementSibling.previousElementSibling; 
    // Toggle between view and edit mode
    if (feedbackDiv.style.display === 'none') {
        
        feedbackDiv.style.display = 'block';
        currentFeedbackSpan.style.display = 'none';

        
        iconElement.style.display = 'none';

        // Replace feedback content with an input field
        feedbackDiv.innerHTML = '<input type="text" id="feedback_input_' + studentId + '" value="' + currentFeedback + '" style="width: 200px;">' +
                                '<div class="fa fa-check" onclick="submitFeedback('+ studentId + ', \'' + assignId + '\')" style="cursor: pointer; margin-left: 5px;" title="Submit feedback"></div>';
   
// input feedback when enter key is clicked 
var feedbackInput = document.getElementById('feedback_input_' + studentId);

// Add an event listener for the 'keydown' event
feedbackInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        submitFeedback(studentId, assignId);
    }
});
                            } else {
        feedbackDiv.style.display = 'none';
        currentFeedbackSpan.style.display = 'block';

        // Show the edit icon
        iconElement.style.display = 'block';
    }
}
        function submitGrade(studentId, assignid) {
            var grade = $j('#grade_input_' + studentId).val();
            var baseUrl='<?php echo $CFG->wwwroot; ?>';
//    alert(grade);
            $j.ajax({
                url: baseUrl+"/local/teacher/assignments/enrolledstudents.php",
                type: 'POST',
                data: {
                    student_id: studentId,
                    assignid: assignid,
                    grade: grade,
                    method:"1"
                },
                success: function(response) {
                    // Update the grade on the page
                    // $j('#grade_display_' + studentId).text(grade);
                    // alert(response); // Display the response message
                    $j("#refresh").click();
                },
                error: function(xhr, status, error) {
                   
                    alert('Error submitting grade: ' + error);
                   
                }
            });
        }
        // feedback 
        function submitFeedback(studentId, assignid) {
    var feedback = $j('#feedback_input_' + studentId).val();

    $j.ajax({
        url: baseUrl+"/local/teacher/assignments/enrolledstudents.php",
        type: 'POST',
        data: {
            student_id: studentId,
            assignid: assignid,
            feedback: feedback,
             method:"2"
            
        },
        success: function(response) {
           
            // alert(response); // Display the response message
            // location.reload();
            $j("#refresh").click();
        },
        error: function(xhr, status, error) {
            // Handle the error
            alert('Error submitting feedback: ' + error);
        }
    });
}
// $(document).ready(function() {
//     // Check if download was requested
//     if (localStorage.getItem('downloadRequested') === 'true') {
//         localStorage.removeItem('downloadRequested');  // Clean up local storage
        
//         var $table = $('#myTable')[0];  // Direct reference to the original table
//         if ($table) {
//             try {
//                 var ws = XLSX.utils.table_to_sheet($table);
//                 var wb = XLSX.utils.book_new();
//                 XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
//                 XLSX.writeFile(wb, 'test.xlsx');
//             } catch (e) {
//                 console.error(e);
//             }
//         } else {
//             alert("Table not found");
//         }
//     }
// });

    </script>
    <script>
        //  var $j= jQuery.noConflict();
//     $j(document).ready(function() {
//         $j("#sxls").click(function() {
//     var $table = $j('#myTable')[0];  // Direct reference to the original table
//     if ($table) {
//         try {
//             var ws = XLSX.utils.table_to_sheet($table);
//             var wb = XLSX.utils.book_new();
//             XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
//             XLSX.writeFile(wb, 'test.xlsx');
//         } catch (e) {
//             console.error(e);
//         }
//     } else {
//         alert("Table not found");
//     }
// });
//         });
</script>
<script>
//     document.addEventListener('DOMContentLoaded', function() {
//     // Function to get URL parameter value by name
//     function getParameterByName(name) {
//         const urlParams = new URLSearchParams(window.location.search);
//         return urlParams.get(name);
//     }

//     // Check if the 'download' parameter is set to '1'
//     if (getParameterByName('download') === '1') {
//         // Find the button and click it
//         const downloadButton = document.getElementById('downloadXlsx');
//         if (downloadButton) {
//             downloadButton.click();
//         }
//     }
// });
// document.getElementById('downloadXlsx').addEventListener('click', function() {
//     // Get the table element
//     // var table = document.getElementById('myTable');
    
//     // // Convert the table to a worksheet
//     // var ws = XLSX.utils.table_to_sheet(table);
    
//     // // Create a new workbook and append the worksheet
//     // var wb = XLSX.utils.book_new();
//     // XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    
//     // // Generate the XLSX file and trigger the download
//     // XLSX.writeFile(wb, 'table_data.xlsx');



//     // clone
//     var coursename = "<?php echo $coursename; ?>";
//     var assignname = "<?php echo $assignname; ?>";
//     var rowCount = $('#myTable tbody tr').length;

//     if (rowCount > 0) {
//         // Clone the original table
//         var $clonedTable = $('#myTable').clone();

//         // Define the indices of the columns you want to keep (zero-based)
//         var columnsToKeep = [1, 2, 3, 5]; // Example: Keep specific columns

//         // Function to remove unwanted columns
//         function removeUnwantedColumns(row, columnsToKeep) {
//             var $cells = $(row).children();
//             $cells.each(function(index) {
//                 if (columnsToKeep.indexOf(index) === -1) {
//                     $(this).remove();
//                 }
//             });
//         }

//         // Remove unwanted columns from the cloned table
//         $clonedTable.find('thead tr').each(function() {
//             removeUnwantedColumns(this, columnsToKeep);
//         });
//         $clonedTable.find('.gradetime').remove(); // Remove gradetime elements
//         $clonedTable.find('tbody tr').each(function() {
//             removeUnwantedColumns(this, columnsToKeep);
//         });

//         // Remove hidden spans from the cloned table
//         $clonedTable.find('span[style*="display:none"]').remove();
// // console.log($clonedTable);
//         // Create a temporary element to hold the filtered table
//         // var $tempDiv = $j('<div></div>').append($clonedTable);

//         // Convert the filtered table to a worksheet (use the cloned table inside $tempDiv)
//         var ws = XLSX.utils.table_to_sheet($clonedTable[0]);
// console.log(ws);
//         // Create a new workbook and add the worksheet
//         var wb = XLSX.utils.book_new();
//         XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

//         // Generate the filename
//         var filename = coursename + '-' + assignname + '-report-' + new Date().toISOString().split('T')[0] + '.xlsx';

//         // Export the workbook as an .xlsx file
//         XLSX.writeFile(wb, filename);

//         // Remove the temporary element
//         // $tempDiv.remove();
//     } else {
//         alert("No records found");
//     }
// });
</script>
