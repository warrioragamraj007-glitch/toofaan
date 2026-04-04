<?php

require_once dirname(__FILE__).'/../../../config.php';
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/local/watchlist/lib.php");
require_once('../testcenter/testcenterutil.php');

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

$assignid =optional_param('instanceid',0,PARAM_INT);
$actid =optional_param('actid',0,PARAM_INT);
//$course=get_course($cid);
$context = context_course::instance($cid);
$students = get_role_users(5 , $context);//getting all the students from a course level
$loggedinusers=get_loggedin_users_by_section($secname);

$rollfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'rollno'));
$sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));


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
            $teacherfeedback=(strlen($teacherfeedback)>0)?$teacherfeedback:'--';
            //echo $student->firstname;
            //var_dump($submission);echo '<br/>';
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
        if((strlen(strip_tags($teacherfeedback))<30)){
            $tfeedback=strip_tags($teacherfeedback);
        }else{
            $tfeedback=substr(strip_tags($teacherfeedback), 0, 30);
            $commentmore='<span title="show comment" data-uid="'.$student->id.'" data-uname="'.$student->firstname.' '.$student->lastname .'"  class="comments">more</span>';
        }

        //var_dump($teacherfeedback);
            $loggedinstudents++;
                            echo '<tr>
                                <td style="width: 7%"><span  style="display:none">'.$statusNum.'</span>
                                <img title="'.$statusMsg.'" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$statusImag.'" width="  16px" /></td>
                                <td><span  style="display:none">'.$rollnumber.'</span>'.$rollnumber.'</td>
                                <td><span  style="display:none">'.$student->firstname.' '.$student->lastname.'</span>'.$student->firstname.' '.$student->lastname.'</td>
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


                                </div><div  style="width:50%;float:left">
                                 <div class="evaluate" title="Evaluate Submission">
                                <a  href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$actid.'&action=grade&rownum=0&userid='.$student->id.'" style="cursor:pointer" target="_blank">
                                ';

                                if(!is_numeric($grade)){
                                    echo '<i title="Evaluate" class="fa fa-pencil-square-o" aria-hidden="true"></i>';
                                }else{
                                    echo '<i title="Re-evaluate" class="fa fa-pencil-square" aria-hidden="true"></i>';
                                }


                               echo '</a></div>
                                </div>

                                <div class="gradetime gradeactbtns">('.$gradetime.')</div>
                                </div>
                                </td>

				<!--<td><span class="watchlist-status'.$student->id.'" style="display:none">'.$watchliststatus.'</span>
				<img data-ref="'.$watchliststatus.'" id="'.$student->id.'" class="watchlist" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/'.$watchlist_icon.'" width="  16px"/></td>-->

                                <td><span >'.$tfeedback.'</span>'.$commentmore.'
                                <div class=" comment'.$student->id.'" style="display:none">'.$teacherfeedback.'</div></td>
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

