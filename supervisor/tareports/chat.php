
<?php

require_once(dirname(__FILE__) . '/../../config.php');

//$CFG->dataroot."/webrtc_data";
   $id=optional_param('token', '-1',PARAM_INT);//retrieve method id
 global $DB;
$uri=$_SERVER["REQUEST_URI"] ;
$info=split("=",$uri);
$token=$info[1];
$html="";
$token_info=split("-",$token);
$agentId=$token_info[0];
$studentId=$token_info[1];
$activityId=$token_info[1];
$user_obj=get_complete_user_data('id', $agentId);
$agent_name=$user_obj->firstname;
$user_obj=get_complete_user_data('id', $studentId);
$student_name=$user_obj->firstname;
$activityId=$token_info[2];
$user_obj=get_complete_user_data('id', $studentId);
$student_name=$user_obj->firstname;

$sql="SELECT * FROM `mdl_call_metadata`   WHERE  `token` ='$token'  AND `answered_time` IS NOT NULL  AND `hangout_time` IS NOT NULL";

$call=$DB->get_record_sql($sql);
//echo $call;
$date1 = $call->answered_time;

			$date2 = $call->hangout_time;
			$seconds =$date2- $date1;
			//$mins = floor( $diff ) ; 
 $hours = floor($seconds / 3600);
 $mins = floor(($seconds - ($hours*3600)) / 60);

			$secs = floor($seconds % 60);

$date=$mins."mins ".$secs."secs";
/**************getting activity name *******************/
            $result=$DB->get_field_sql("SELECT `instance`
                                        FROM `mdl_course_modules`
                                        WHERE `id` ='$activityId'");
            $actname=$DB->get_field_sql("SELECT `name`
                                        FROM `mdl_vpl`
                                        WHERE `id` ='$result'");


$filepath=$CFG->dataroot."/webrtc_data/".$agentId."/".$token.".txt";


$file_content= file_get_contents($filepath);

//fclose($myfile);

//$html= $html.'<div class="yui3-skin-sam chant-page">';
//$html= $html. "<div class='span12' style='margin-top: 20px;'>";
/*$html.=$html. '<div style="float:left;width: 100% ;margin-bottom:5px"><table id="filt"  style= "margin-bottom: 0px !important" class="generaltable generalbox quizreviewsummary">
<tbody><tr>
		<th class="cell" scope="row"><b>Agent :</b></th><td id="agentname" class="cell">'.$agent_name.'</td>
<th class="cell" scope="row"><b>Caller :</b></th><td id="scour" class="cell">'.$student_name.'</td>
<th class="cell" scope="row"><b>Activity :</b></th><td id="actvityname" class="cell">'.$actname.'</td>
<th class="cell" scope="row"><b>Duration :</b></th><td id="actvityname" class="cell">'.$date.'</td>
</tr>

</tbody></table></div>';*/
$html.=" <div style='width:99%;background-color: rgb(1, 41, 81);color:white;text-align:center; padding: 2px;font-size: 14px;'><span ><b>Chat Transcript</span></b></div>";
$html.='<div style="width: 99% ; padding: 2px;
    border: 2px solid rgb(1, 41, 81) !important;overflow-y: auto;
height: 300px;">';
if(empty($file_content))
$html.="There is no chat ";
else
$html.= $file_content;

$html.="<center style='margin-top:3%'><span style='padding:5px 10px;background-color:#d7d5d5' ></span> Agent <span style='margin-left:3%;padding:5px 10px;background-color:#dbedfe;' ></span> Student </center>";
$html.='</div>';
echo $html;
?>
