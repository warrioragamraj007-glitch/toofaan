<?php

require_once(dirname(__FILE__).'/../../config.php');
  $id=optional_param('id', '-1',PARAM_INT);//retrieve method id
    $cid=optional_param('cid', '-1',PARAM_INT);//retrieve method id
    $aid=optional_param('aid', '0',PARAM_INT);//retrieve method id
    $agentid=optional_param('agent', '0',PARAM_INT);//retrieve method id
    $state=optional_param('state', '0',PARAM_INT);//retrieve method id


        switch($id) {
            case 1:
               get_course_activities($cid);
                break;
           case 2:
               get_ta_reports($cid,$aid);
                break;
           case 3:
               get_ta_agent_reports($agentid, $aid,$state);
                break;
            case 4:
               get_agents_current_status ($cid,$aid);
                break;
             }
/********************************************************************************************
 ******************* GETTING ACTIVITIES OF A COURSE *************************************
 * ******************************************************************************/
function get_course_activities($cid)
{
	global $DB, $CFG;
	$html="";
	$cousre_moudles=$DB->get_records('course_modules', array('course'=>$cid));
	$html.=html_writer::start_tag("select",array('class'=>'activity','id'=>'activities'));
	//ADIING SELECT ONE OPTION
	$html.=html_writer::start_tag("option",array('value'=>"0"));
	$html.="Select Activity";
	$html.=html_writer::end_tag("option");
	foreach($cousre_moudles as $act)
	{

		if($act->module=='28')//only for labs
{
		//getting moulde type like quiz or vpl ect
		$act_type=$DB->get_record('modules', array('id'=>$act->module));//getting activity type based module
		$act_instace=$DB->get_record('course_modules', array('id'=>$act->id ));//getting activity instace based on id
		$act_name=$DB->get_record_sql("SELECT * 
		FROM  `mdl_$act_type->name` WHERE `id`='$act_instace->instance'  ");
		$html.=html_writer::start_tag("option",array('value'=>$act->id));
		$html.=$act_name->name;
		$html.=html_writer::end_tag("option");
}
	}
	$html.=html_writer::end_tag("select");
	echo $html;	
}
/*************************************************************************************************
 * ************************* getting reports of all based on course/activity *****
 * *************************************************************************************/
function  get_ta_reports($cid,$aid){
	   global $DB;
	// getting all the agents of a course
	$context = get_context_instance(CONTEXT_COURSE, $cid) ;
	$agents = get_role_users(4 , $context);
	$html="
<table  id='rowclick' class='table table-hover course-list-table '>
		<thead><tr><th>Assistant</th><th>Calls Answered</th><th>Calls Ignored</th><th>Rating</th></tr></thead></tr>    </thead><tbody>"; 
	foreach($agents as $agent)
	{  
		$total_calls=0;
		$calls_ignored=0;
		$calls_answered=0;
			$ratingg=0;
			if ($cid)
			{
					$calls=$DB->get_records('call_metadata', array('course'=>$cid,'agent_id'=>$agent->id));
					//getting rating 
					$sql="SELECT AVG( `rating` ) as  `rating` 
					                FROM `mdl_tagent_rating` r
					                WHERE r.`agentid` =$agent->id AND r.`course` =$cid
					                ORDER BY AVG( `rating` ) DESC";
			}
			if($aid)
			{
			
					$calls=$DB->get_records('call_metadata', array('course'=>$cid,'agent_id'=>$agent->id,'activity'=>$aid));
					$sql="SELECT AVG( `rating` ) as  `rating` 
					                FROM `mdl_tagent_rating` r
					                WHERE r.`agentid` =$agent->id AND r.`activity` =$aid
					              ORDER BY AVG( `rating` ) DESC";
			}
	// Counting calls Answred/Ignore 
			foreach($calls as $call)
			{
				if($call->answered_time)
				{
				$calls_answered=$calls_answered+1;
				$total_calls=$total_calls+1;
				}
				if($call->ignore_time||$call->ignore||$call->reason)
				{
				$total_calls=$total_calls+1;
				$calls_ignored=$calls_ignored+1;
				}
			}
		// getting rating of a agent
		$rating=$DB->get_record_sql( $sql);
		if(empty($rating->rating))
		{
		
		}
		else{
			
			$r=round(($rating->rating)* 2)/2;
			$r=$r*10;
	 $ratingg="<div class='stars'><input type='radio' name='star' class='star-".$r. "' /><span></span>
			</div>";
			
			
			
			
			
			
			
		}
		
		$agentObj= get_complete_user_data(id, $agent->id);
		$html.="<tr id='".$agent->id."' class='".$total_calls."'>";
		$html.="<td><a href='' title='View All Calls'>".$agentObj->firstname."".$agentObj->lastname."</a></td>";
		$html.="<td>".$calls_answered."</td>";
		$html.="<td>".$calls_ignored."</td>";
		$html.="<td>".$ratingg."</td>";
		$html.="</tr>";
		}
		$html.="</tbody></table> ";
		echo $html;	
}
/*************************************************************************************************
 * ************************* GETTING REPORTS BASED ON ACTIVITY OR STATE OF CALL FOR PATICULAR AGENT  *****
 * *************************************************************************************/
function   get_ta_agent_reports($agentid, $aid,$state){
		 global $DB;
	
		  $html="<table  id='rowclick' class='table table-hover course-list-table '><thead><tr><th>Assistant</th><th>Student</th><th>Call  Start</th><th>Call  Duration</th><th> Ended By</th><th>Rating</th><th>Ignore Reason</th><th>Chat</th></thead></tr>"   ;      
          	$calls_sql="SELECT *
		  	FROM `mdl_call_metadata` r
		  	WHERE r.`agent_id` =$agentid  ";
		
		  if($aid)
		  {
		  	$calls_sql="SELECT *
		  	FROM `mdl_call_metadata` r
		  	WHERE r.`agent_id` =$agentid  AND r.`activity` =$aid ";
		  	
		  //	$calls=$DB->get_records('call_metadata', array('agent_id'=>$agentid,'activity'=>$aid));
		
		  }
		  
		  if($state)
		  {
		  	if($state==1)
		  	{
		  	
		  	$calls_sql= $calls_sql."AND ignored IS NOT NULL  ";
		  
		  }
		  else if($state==2){
		  	$calls_sql=$calls_sql."AND `answered_time` IS NOT NULL  AND `hangout_time` IS NOT NULL ";
		  	
		  }
		  else if($state==3){
		  	$calls_sql=$calls_sql."AND `answered_time` IS  NULL  AND ignored IS  NULL ";
		  }
	else{
		
	}}
	
	$calls=$DB->get_records_sql($calls_sql);
	
	
			foreach($calls as $call)
		{
		
			$chat=3;
			$hangup_by= get_complete_user_data(id, $call->hangup_by);
			
			if(	$hangup_by->firstname){
				$hangout=$hangup_by->firstname;
			
			}
			else{
			
				if ($call->ignore_reason)
				{
					$chat=1;
				}
				else{
						
					$hangout="Dropped";
					$chat=0;
					
				}
			}
			$html.="<tr id='".$call->token."'  class='".$chat."' >";
if($hangout=="Dropped")
{
$mins=0;
$secs=0;
}
else
{
			$date1 = $call->answered_time;
			$date2 = $call->hangout_time;
			$seconds =$date2- $date1;
			//$mins = floor( $diff ) ; 
 $hours = floor($seconds / 3600);
 $mins = floor(($seconds - ($hours*3600)) / 60);

			$secs = floor($seconds % 60);

			//$secs=round($secs,2)*100;
			}
			$agentObj= get_complete_user_data(id, $call->agent_id);
			$html.="<td>".$agentObj->firstname." ".$agentObj->lastname."</td>";
			$studObj= get_complete_user_data(id, $call->caller_id);
			$html.="<td>".$studObj->firstname." ".$studObj->lastname."</td>";
			$html.="<td>".userdate($call->created_time)."</td>";
			$html.="<td>".$mins."mins ".$secs."secs"."</td>";
			
			$html.="<td>".$hangout."</td>";
			$rating=$DB->get_record('tagent_rating', array('token'=>$call->token));
			if(empty($rating->rating))
				$ratingg="Not Rated";
			else
			{
				$r=round(($rating->rating)* 2)/2;
			$r=$r*10;
	 $ratingg="<div class='stars'><input type='radio' name='star' class='star-".$r. "' /><span></span>
			</div>";
			
			}
			$html.="<td>".$ratingg."</td>";
			if($call->ignore_reason){
				$reason1=$call->ignore_reason;
				//getting reason 
			
			$ress=$DB->get_record('ignore_reason', array('reasonid'=>$reason1));
				
				$reason=$ress->reason;
			}
			else{
				$reason="--";
			}
			$html.="<td>".	$reason."</td>";
			
			$html.="<td><a href='' title='View Chat Transit' id='chat' class='1'> Chat</td>";
			$html.="</tr>";
		}
		$html.="</table>";
		echo $html;	

}
/*************************************************************************************************
 * ************************* SHOWING AGENTS STATUS TO TEACHER ******************************************
 * *************************************************************************************/
function get_agents_current_status ($cid,$aid)
{
	global $DB;
	//getting all the agents of a course 
	$context = get_context_instance(CONTEXT_COURSE, $cid) ;
	$agents = get_role_users(4 , $context);
	$agentName='';
	$studentName="--";
	$status="--";
	$calls_answred=0;
	$calls_ignored=0;
	
	$html="<table  id='rowclick' class='teacheragents-table course-list-table table table-hover tablesorter'>
			<thead><tr><th>Assistant</th>
			<th>Logged In</th>
			<th>Status</th>
			<th>Student</th>
			<th>Answered</th>
			<th> Ignored</th>
			</thead></tr>"   ;
	
	
	foreach ($agents as $agent )
	{
		$calls_answred=0;
		$calls_ignored=0;
		$agentName;
		$studentName="--";
		$status="--";
		$agentId=$agent->id;
		
		//getting name of agent
		$agentObj= get_complete_user_data(id, $agentId);
		$agentName=$agentObj->firstname." ".$agentObj->lastname;
		
		$status_info=$DB->get_record('teacher_assistant_tsl', array('userid'=>$agentId));
		$satusId= $status_info->status;
				if($satusId!=-4 && $status){

			//getting status def
		    $status_def=$DB->get_record('tagent_status_def', array('id'=>$satusId));
			$status= $status_def->status;
			//getting current logged of a user
			$login_info=$DB->get_record('user', array('id'=>$agentId));
         	$loggedin_time=userdate($login_info->currentlogin);
			$status_updated_time=userdate($status_info->time);
		}else{
			$loggedin_time="--";
			$status="NOT AVAILABLE";
			
		}
		
		
		if($satusId==4)//means agent is on call
		{
		//retriving the call information 
	$calls_sql="SELECT *
		  	FROM `mdl_call_metadata` r
		  	WHERE r.`agent_id` =$agentId  AND r.`call_status` =1 AND  `answered_time` IS NOT NULL ";
		//$call_info=$DB->get_record('call_metadata', array('agent_id'=>$agentId,'call_status'=>1));
$call_info=$DB->get_record_sql($calls_sql);
		$studentId=$call_info->caller_id;
		$activityId=$call_info->activity;
		//getting name of student 
		$stuObj= get_complete_user_data(id, $studentId);
		$studentName=$stuObj->firstname." ".$stuObj->lastname;
		//getting activity name 
		$result=$DB->get_field_sql("SELECT `instance`
				FROM `mdl_course_modules`
				WHERE `id` ='$activityId'");
		$activityName=$DB->get_field_sql("SELECT `name`
				FROM `mdl_vpl`
				WHERE `id` ='$result'");
		}
		if($satusId==3)//means agent is steppedout
		{
			$status=$status."(".$status_updated_time.")";
		}
		
		$calls=$DB->get_records('call_metadata', array('agent_id'=>$agentId,'call_status'=>0,'activity'=>$aid));
		
		foreach ($calls as $call)
		{
			
	      if($call->answered_time)
			{
		       $adate = usergetdate($call->answered_time);
				$answredDate=$adate['mday']."-".$adate['mon']."-".$adate['year'];
	            $todays_date=date('d-m-Y',time());
			//	echo "yes ".strcmp($answredDate,$todays_date)."Today:".$todays_date."ANS".$answredDate."<br>";
				if(strcmp($answredDate,$todays_date)>=0)
				{
					$calls_answred=$calls_answred+1;
				}
			}
			
			if($call->ignore_time)
			{
				$todays_date=date('d-m-Y',time());
				$idate = usergetdate($call->ignore_time);
				$ignoreDate=$idate['mday']."-".$idate['mon']."-".$idate['year'];
			    if(strcmp($ignoreDate,$todays_date)>=0)
			    {
				$calls_ignored=$calls_ignored+1;
			    }
			}
	
					
		}
      	//Rendering RESULTS 
		$html.="<tr>";
		$html.="<td>".ucwords($agentName)."</td>";
	    $html.="<td>".$loggedin_time."</td>";
		$html.="<td>".strtoupper($status)."</td>";
       	$html.="<td>".$studentName."</td>";
        $html.="<td>".$calls_answred."</td>";
		$html.="<td>".$calls_ignored."</td>";
		$html.="</tr>";
		
		}
		$html.="</table>";
		echo $html;
	}
	


     
        	
        
