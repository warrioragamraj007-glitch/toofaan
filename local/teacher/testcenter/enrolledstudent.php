<?php
require_once('../../../config.php');
require_once("../../../enrol/locallib.php");
require_once('testcenterutil.php');
require_once("../../watchlist/lib.php");
$params = explode("-", $GET['topics']);
$id=(int)$params[0];
$courseid=$id;
//retriving course id from url
if(!$courseid){
$courseid = required_param('cid', PARAM_INT);
}
$secname = optional_param('secname', 'All',PARAM_TEXT);
$context = context_course::instance($courseid);
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
// ?>


<div class="repo">
                    <!-- <div id="container" style="padding-bottom:30px;"> -->
                   

                    <table id="myTable"  class="CSSTableGenerator table table-hover course-list-table tablesorter">
                            <thead>
                            <tr>
                                <th  class="header" style="text-align:center">Status</th>
                                <th class="header">Roll No</th>
                                <th class="header">Full Name</th>
                                <th class="header" style="text-align:center">Section</th>
                                <th class="header" >Last Submission</th>
                                <th class="header" style="text-align:center">Submissions</th>
                                <th class="header" style="text-align:center">Grade</th>
                                <th class="header" style="text-align:center">Watch</th>
                            </tr>
                            </thead>
                            <tbody >
<?php              
                                       $statusImag='flag-red-icon.png';   //not submitted and not graded
                                       $statusNum=0;
                                       $grade='--';
                                       $subtime='--';
                                       $usersubcount='--';
                                       $loggedinstudents=0;
                                       $watchCount=0;
           
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
            
           
                if($display_flag){
           
           
           
                   $userparticipation=$DB->get_field('userinfo_tsl', 'loginstatus', array('userid' => $student->id));
                   if(($userparticipation==2)||($userparticipation==4)){
           
                       //code to check whether the student is watchlisted or not
                       $watchliststatus=getStatus($student->id,$courseid);
                       if($watchliststatus){
                           $watchlist_icon='eye-24-512.png';$watchCount++;
                       }
                       else{
                           $watchlist_icon='unwatch-512.png';
                       }
                       $loggedinstudents++;
                               echo '<tr>
                                <td><span  style="display:none">'.$statusNum.'</span>
                                <img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$statusImag.'" width="  16px" /></td>
                                <td><a target="_blank" href=" ' . $CFG->wwwroot . '/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">'.$rollnumber.'</a></td>
                                <td><a target="_blank" href=" ' . $CFG->wwwroot . '/report/outline/user.php?id='.$student->id.'&course='.$courseid.'&mode=outline'.'">'.$student->firstname.' '.$student->lastname.'</a></td>
                                <td style="text-align:center">'.$stu_section.'</td>
                                <td>'.$subtime.'</td>
                                <td style="text-align:center">'.$usersubcount.'</td>
                                <td style="text-align:center">'.$grade.'</td>
				<td><span class="watchlist-status'.$student->id.'" style="display:none">'.$watchliststatus.'</span>
				<img data-ref="'.$watchliststatus.'" id="'.$student->id.'" class="watchlist" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$watchlist_icon.'" width="  16px"/></td>

                            </tr>'; 
                      }
                    }
                  }?>
                        </tbody>
</table>
                <!-- </div>/ -->
                </div>
                
<?php
echo '<span style="display:none" id="loggedinusers">'.$loggedinstudents.'</span>';
echo '<span style="display:none" id="watchCount">'.$watchCount.'</span>';
echo '<span style="display:none" id="statusstopdate">'.$acivitystopdate.'</span>';

  ?>