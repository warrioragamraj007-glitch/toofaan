<?php
/**

 * this is the file contains  the methods where we can perform all ajax calls from test center.

 */

require_once('../../../config.php');
// require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');
require_once("../../../enrol/locallib.php");
require_once("../../watchlist/lib.php");

$secid = optional_param('secid','-1', PARAM_INT);
$cid = optional_param('cid', '-1',PARAM_INT);
$uid=optional_param('uid', '-1',PARAM_INT);//retrieve user id
$mid=optional_param('mid', '-1',PARAM_INT);//retrieve method id
$aid=optional_param('aid', '-1',PARAM_INT);//retrieve method id
$sid=optional_param('sid', '-1',PARAM_INT);//retrieve student id
$section=optional_param('stusec', 'All',PARAM_TEXT);//retrieve secname
$am_type_name=optional_param('mname', 'x',PARAM_TEXT);
//retriving sections and activities based on course

//print_r($aid);
//print_r($am_type_name);

$f1=optional_param('f1', '-1',PARAM_INT);//retrieve feedback1
$f2=optional_param('f2', '-1',PARAM_INT);//retrieve feedback2
$cyear=optional_param('cyear', '-1',PARAM_INT);//retrieve course year


            switch($mid){
                case 1: section_activities($secid,$cid);break;
                case 2: set_activity_status($aid);break;
                case 3: set_activity_completiondate($aid);break;
                case 4: get_activity_status();break;
				case 5: get_student_sections($cid);break;
				case 6: add_student_to_watchlist($cid,$uid);break;
				case 7: get_loggedin_users_by_section($section);break;
				case 8: get_user_quiz_grade($aid,$uid);break;
				case 9: get_user_quiz_instance($aid);break;
				case 10: get_itemid_from_grade_table($aid,$am_type_name,$cid);break;
				case 11: get_course_absenties($cid);break;
				case 12: set_course_absenties($cid,$aid);break;
				case 13: getCntofAbsentActivities($sid,$cid);break;
				case 14: getStudentAttandanceofActivity($sid,$aid,$cid);break;
				case 15: reset_all_loggedin_users($cid);break;
				case 16: set_activity_stop_status($aid);break;
				case 17: set_activity_close_status($aid);break;
				case 18: delete_previousday_closedtests();break;
				case 19: getActivityTypeIds(); break;
				case 20: save_sonet_feedback($cid,$secid,$f1,$f2,$uid);break;
				case 21: save_sonet_topic($cid,$secid,$cyear);break;
				case 22: checkYesterdayFeedback($uid,$cid,$secid);break;

            }

				$activityTypeIds=getActivityTypeIds();

            function section_activities($secid,$cid){
                $course=get_course($cid);
                $modinfo = get_fast_modinfo($course);
                $mods = $modinfo->get_cms();
                $sections = $modinfo->get_section_info_all();
                $sec_array = get_sections($sections);
                $arr = array();
                $cnt=0;

            //preparing an array which contains sections and activities
                foreach ($mods as $mod) {
                    $arr[$cnt++]=array('secid'=>$mod->section,'modid'=>$mod->id,'modname'=>$mod->name,'modcontent'=>$mod->content);
                    //print_r($mod->name);
                }

            //returns the all activities associated to perticular section in a course
                function get_activities($sectionid,$arr)
                {
                    $cnt=0;
                    $sec_activity_array = array();
                    for($i=0;$i<count($arr);$i++) {
                        if($arr[$i]['secid']==$sectionid){
                            $sec_activity_array[$cnt] = array('modid'=>$arr[$i]['modid'],'modname'=>$arr[$i]['modname'],'modcontent'=>$arr[$i]['modcontent']);
                            $cnt++;
                        }

                    }
                    return $sec_activity_array;
                }

            // Get all course sections in a array
                function get_sections($sections)
                {   $cnt=0;
                    $sec_array = array();
                    foreach ($sections as $sec) {
                        $sec_array[$cnt++] = array('secid'=>$sec->id,'secname'=>$sec->name);
                    }
                    return $sec_array;
                }


                $activities=get_activities($secid,$arr);
                $html='';global $CFG;
                for($i=0;$i<count($activities);$i++) {
                    $html .= '<tr >
                                <td ><span class="mod' . $activities[$i]['modid'] . '">' . ($i + 1) . '</span></td>
                                <td ><span class="mod' . $activities[$i]['modid'] . '">' . $activities[$i]['modname'] . '</span></td>
                                <td ><span class="mod' . $activities[$i]['modid'] . '">' . $activities[$i]['modcontent'] . '</span></td>
                                <td >
                                <button class="showhide" id="show" value=' . $activities[$i]['modid'] . '>
                                <img  alt="start" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/start.png" width="16px"/></button>
                                <button class="showhide" id="hide" value=' . $activities[$i]['modid'] . '>
                                <img  alt="stop" src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/stop.png" width="16px"/></button>
                                </td>
                            </tr>';
                }
                echo $html;
            }//end of section_activities() function



            function set_activity_status($id){
                global $DB;
                $timenow = time();
                $activity_status = new stdClass();
                $activity_status->activityid     = $id;
				$activity_status->activity_start_time   =$timenow;
				$activity_status->status=1;
                //print_r($activity_status);
                try {

					if($DB->get_field('activity_status_tsl', 'id', array('activityid' => $id))){
						$activity_status->id=$DB->get_field('activity_status_tsl', 'id', array('activityid' => $id));
						$DB->update_record_raw('activity_status_tsl', $activity_status, false);

					}
					else{
						$DB->insert_record_raw('activity_status_tsl', $activity_status, false);
					}

					//echo 'executed'.$activity_start_time;
					$activity_start_time = $DB->get_field('activity_status_tsl', 'activity_start_time', array('activityid' => $id));
					echo  userdate($activity_start_time);


                } catch (dml_write_exception $e) {
                    // During a race condition we can fail to find the data, then it appears.
                    // If we still can't find it, rethrow the exception.
                    $activity_status_time = $DB->get_field('activity_status_tsl', 'activity_start_time', array('activityid' => $id));
                    if ($activity_status_time === false) {
                        throw $e;
                        //return 0;
                    }

                }
            }//end of set activity status



			//set_activity_stop_status logic
			function set_activity_stop_status($id){
				global $DB;
				$timenow = time();
				$activity_status = new stdClass();
				$activity_status->activityid     = $id;
				$activity_status->activity_stop_time   =$timenow;
				$activity_status->status=0;
				//print_r($activity_status);
				try {
					if($DB->get_field('activity_status_tsl', 'id', array('activityid' => $id))){
						$activity_status->id=$DB->get_field('activity_status_tsl', 'id', array('activityid' => $id));
						$DB->update_record_raw('activity_status_tsl', $activity_status, false);
					}
					$activity_stop_time = $DB->get_field('activity_status_tsl', 'activity_stop_time', array('activityid' => $id));
					echo  userdate($activity_stop_time);

				} catch (dml_write_exception $e) {
					// During a race condition we can fail to find the data, then it appears.
					// If we still can't find it, rethrow the exception.
					$activity_status_time = $DB->get_field('activity_status_tsl', 'activity_stop_time', array('activityid' => $id));
					if ($activity_status_time === false) {
						//throw $e;
						return 0;
					}

				}
			}//end of set activity status

			//set_activity_close_status logic
			function set_activity_close_status($id){
				global $DB;
				$timenow = time();
				$activity_status = new stdClass();
				$activity_status->activityid     = $id;
				$activity_status->activity_close_time   =$timenow;
				$activity_status->status=2;
				//print_r($activity_status);
				try {
					if($DB->get_field('activity_status_tsl', 'id', array('activityid' => $id))){
						$activity_status->id=$DB->get_field('activity_status', 'id', array('activityid' => $id));
						$DB->update_record_raw('activity_status', $activity_status, false);
					}
					$activity_close_time = $DB->get_field('activity_status', 'activity_close_time', array('activityid' => $id));
					echo  userdate($activity_close_time);

				} catch (dml_write_exception $e) {
					// During a race condition we can fail to find the data, then it appears.
					// If we still can't find it, rethrow the exception.
					$activity_status_time = $DB->get_field('activity_status', 'activity_close_time', array('activityid' => $id));
					if ($activity_status_time === false) {
						//throw $e;
						return 0;
					}

				}
			}//end of set activity status








            function get_activity_status(){
                global $DB;
                $timenow = time();
				$seccount=0;
				$cactivitiesArray=array();
				$result=$DB->get_records('activity_status_tsl', null);
				foreach($result as $res){

					$cactivitiesArray[$seccount++]=array("aid"=>$res->activityid,"status"=>$res->status,"start"=>userdate($res->activity_start_time),
						"stop"=>userdate($res->activity_stop_time),"close"=>userdate($res->activity_close_time));

					//print_r("<br/>");
				}
				echo json_encode($cactivitiesArray);


            }//end of get activity status

            function set_activity_completiondate($aid){
                global $DB;
                $timenow = time();
                $activity_status_completiondate = new stdClass();
                $activity_status_completiondate->id     = $aid;
                $activity_status_completiondate->completionexpected =$timenow;
                //print_r($activity_status);
                try {
                    if($DB->get_field('course_modules', 'id', array('id' => $aid))){
                        $DB->update_record_raw('course_modules', $activity_status_completiondate, false);
						set_activity_close_status($aid);
						//$DB->delete_records('activity_status', array('activityid'=>$aid));
                    }

                    //echo 'executed';

                } catch (dml_write_exception $e) {
                    // During a race condition we can fail to find the data, then it appears.
                    // If we still can't find it, rethrow the exception.
                    $activity_status_time = $DB->get_field('course_modules', 'completionexpected', array('id' => $aid));
                    if ($activity_status_time === false) {
                        //throw $e;
                        return 0;
                    }

                }
            }//end of set activity status

		//get students section information based on course
		function get_student_sections($cid){
		$context = context_course::instance($cid);
		$students = get_role_users(5 , $context);//getting all the students from a course level


		$stuarr=array();$stcnt=0;
		foreach($students as $student){
		if(get_complete_user_data(id,$student->id)->profile['section']){
		$stu_section=get_complete_user_data(id,$student->id)->profile['section'];
		$stuarr[$stcnt++]=array('stusec'=>$stu_section,'stid'=>$student->id);
		}
		}

		$ss=array_count_values(array_column($stuarr, 'stusec'));
		ksort($ss);

		$stu_sec_info=array();$seccount=0;
		foreach( $ss as $key => $value)
		{
		$stu_sec_info[$seccount++]=array("secname"=>$key,"seccount"=>$value);
		}
		return $stu_sec_info;//json_encode($stu_sec_info);

		}

		function add_student_to_watchlist($cid,$uid)
		{
			$status=getStatus($uid,$cid);
				if($status)
				$status=0;
				else
				$status=1;
			echo updateStatus($status,$uid,$cid);
		}


		function get_loggedin_users_by_section($section)
		{
		global $DB;
		$params = array();
		$params['loginstatus'] = 2;
		$params['logoutstatus'] = 4;
		//$section="C";
		if($section=='All'){
		//echo "section=".$section;
		$csql = "SELECT count(*)  FROM {userinfo_tsl} u WHERE u.loginstatus = :loginstatus OR u.loginstatus =:logoutstatus";
		}
		else{
			$params['studentsection'] = $section;
			$csql = "SELECT count(*)  FROM {userinfo_tsl} u WHERE u.loginstatus = :loginstatus OR u.loginstatus =:logoutstatus AND u.studentsection = :studentsection";
		}

			    
			   
		 $usercount = $DB->count_records_sql($csql,$params);
			
		return $usercount;
		}


		function get_user_quiz_grade($qid,$uid)
		{
				global $DB;
				$params = array();$result=array();
				$quizinstance=get_user_quiz_instance($qid);

				if($quizinstance['instance']){
				$params['quizid'] = $quizinstance['instance'];$params['userid'] = $uid;
				$qsql = "SELECT q.grade,q.timemodified,q.subip FROM {quiz_grades} q WHERE q.quiz = :quizid AND q.userid = :userid";
				$userquizgrade = $DB->get_record_sql($qsql,$params);
				if($userquizgrade){
				$result['grade']=$userquizgrade->grade;
				$result['timemodified']=$userquizgrade->timemodified;
				$result['subip']=$userquizgrade->subip;
				return $result;
				}
				else{
				$result['grade']='--';
				$result['timemodified']='--';
				$result['subip']='--';
				return $result;
				}
				}//if quiz instance is available
				else
				{
				$result['grade']='--';
				$result['timemodified']='--';
				$result['subip']='--';
				return $result;//no instance
				}
										
		}
		function get_user_quiz_instance($qid)
		{
				global $DB;
				$params = array();$result=array();
				$params['quizid'] = $qid;
				$qsql = "SELECT cm.instance FROM {course_modules} cm WHERE cm.id = :quizid";
				$userquizgrade = $DB->get_record_sql($qsql,$params);
				if($userquizgrade){
				$result['instance']=$userquizgrade->instance;
				
				return $result;
				}
				else{
				$result['instance']=0;
				return $result;
				}
										
		}

		function get_itemid_from_grade_table($aid,$am_type_name,$cid)
		{
				global $DB;
				$params = array();$result=array();
				$params['iteminstance'] = $aid;
				$params['itemmodule'] = $am_type_name;
				$params['courseid'] = $cid;
				$qsql = 
		"SELECT g.id FROM {grade_items} g WHERE g.iteminstance=:iteminstance AND g.itemmodule=:itemmodule AND g.courseid=:courseid";
				$grade_itemid = $DB->get_record_sql($qsql,$params);
				//var_dump($grade_itemid);
				if($grade_itemid){
				$result['id']=$grade_itemid->id;
				//print_r($result['id']);
				return $result['id'];
				}
				else{
				$result['id']=0;
				//print_r($result['0']);
				
				return $result['id'];
				}
										
		}


			function get_all_loggedin_users($section)
			{
				global $DB;
				$params = array();
				$params['loginstatus'] = 2;
				$params['logoutstatus'] = 4;
				//$section="C";
				if($section=='All'){
			//echo "section=".$section;
					$csql = "SELECT u.userid  FROM {userinfo_tsl} u WHERE u.loginstatus = :loginstatus  OR u.loginstatus =:logoutstatus";
				}
				else{
					$params['studentsection'] = $section;
					$csql = "SELECT u.userid  FROM {userinfo_tsl} u WHERE u.loginstatus = :loginstatus  OR u.loginstatus =:logoutstatus AND u.studentsection = :studentsection";
				}



				$usercount = $DB->get_records_sql($csql,$params);

				return $usercount;
			}


			//reset all logged in users
			function reset_all_loggedin_users($cid)
			{
				global $DB;
				$context = context_course::instance($cid);
				$students = get_role_users(5 , $context);
				$studentids='';
				$stdflag=0;
				foreach($students as $student){
					if($stdflag==0){
						$stdflag=1;
						$studentids=$student->id;
					}else{
						$studentids=$studentids.','.$student->id;
					}

				}
				$dsql="DELETE FROM mdl_sessions WHERE userid in (SELECT userid FROM mdl_userinfo_tsl WHERE userid IN (".$studentids."))";
				//$dsql1="DELETE FROM mdl_sessions WHERE userid in (SELECT userid FROM mdl_userinfo_tsl )";
				$rsql="UPDATE  mdl_userinfo_tsl   SET loginstatus = 0  where userid in (".$studentids.")";
				$result1=$DB->execute($dsql,null);
				$result2=$DB->execute($rsql,null);
				$result3=delete_previousday_closedtests();
				return ($result1&&$result2&&$result3);
			}


			function get_course_absenties($cid){

				$context = context_course::instance($cid);
				$enrolledStudents = get_role_users(5 , $context);//getting all the students from a course level
				$loggedinusers=get_all_loggedin_users('All');


				$logstuarr=array();$cnt=0;
				foreach($loggedinusers as $logstudent){

					$logstuarr[$cnt++]=array('stid'=>$logstudent->userid);

				}
				$lgss=array_column($logstuarr, 'stid');
				//print_r($lgss);

				$absented_Students=array();$stcnt=0;
				foreach($enrolledStudents as $student){
					if(in_array($student->id, $lgss)){}
					else{
						$absented_Students[$stcnt++]=array('stid'=>$student->id);
					}
				}
				$absented_Students=array_column($absented_Students,'stid');
				return $absented_Students;

			}

			function set_course_absenties($cid,$aid){
				global $USER,$DB;
				$absenties=get_course_absenties($cid);

				$timenow = time();
				$student_attendance = new stdClass();
				$student_attendance->aid     = $aid;
				$student_attendance->cid     = $cid;
				$student_attendance->teacherid=$USER->id;
				$student_attendance->datecreatedat=$timenow;
				$student_attendance->studentid=0;

				for($i=0;$i<count($absenties);$i++){
					$student_attendance->studentid=$absenties[$i];
					//print_r($student_attendance);
					try {

							$DB->insert_record_raw('std_activity_attend_tsl', $student_attendance, false);


					} catch (dml_write_exception $e) {
						// During a race condition we can fail to find the data, then it appears.
						// If we still can't find it, rethrow the exception.
							print_r($e);
					}
				}//end of for loop
			}//end of set course absenties


			function getCntofAbsentActivities($sid,$cid){
				global $DB;
				$date=strtotime('today');
				$datenow=strtotime('now');

				$sql= 'SELECT id FROM `mdl_std_activity_attend_tsl`
				WHERE `cid` ='.$cid.'	AND `studentid` ='.$sid.'
				AND `datecreatedat` BETWEEN '.$date.' AND '.$datenow;

				$csql= 'SELECT id FROM mdl_course_modules
				WHERE course ='.$cid.'
				AND completionexpected BETWEEN '.$date.' AND '.$datenow;
				//$DB->get_records_sql($csql,$params);
				$absent_activities=count($DB->get_records_sql($sql, null));
				$completed_activities=count($DB->get_records_sql($csql, null));

				if($completed_activities){
					if($absent_activities==$completed_activities){
						return  'ABSENT';
					}
					else{
						return  'PRESENT';
					}
				}
				else{
					return '--';
				}
			}

			function getStudentAttandanceofActivity($sid,$aid,$cid)
			{
				global $DB;

				$completedstatus=$DB->get_field('course_modules', 'completionexpected', array('course' => $cid,'id'=>$aid));


				if($completedstatus){
					$sql = 'SELECT id FROM `mdl_std_activity_attend_tsl`
							WHERE `cid` =' . $cid . '	AND `studentid` =' . $sid . '
							AND `aid` =' . $aid;

					if (count($DB->get_records_sql($sql, null))) {
						return 'ABSENT';
					} else {
						return 'PRESENT';
					}
				}
				else{
						return 'NOT STARTED';
				}


			}

			function delete_previousday_closedtests(){
				global $DB;
				$today=strtotime("today");
				$status=2;
				$sql="DELETE  FROM `mdl_activity_status_tsl` WHERE `activity_close_time`< $today and `status`=$status";
				echo $DB->execute($sql);

			}


			function getActivityTypeIds(){
				global $DB;
				$activity_typeids=array();
				$sql='SELECT id, name FROM `mdl_modules` ';
				$resultset=$DB->get_records_sql($sql, null);
				foreach ($resultset as $res) {

					$activity_typeids[$res->name]=$res->id;
				}
				return $activity_typeids;
			}

			function getTCStudentData($userid,$fieldid){
				global $DB;
				$sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$fieldid."'";
				$fielddata=$DB->get_record_sql($sql);
				$studata=$fielddata->data;
				return $studata;
			}

/* sonet functionality start*/
/* sonet feedback */
			function save_sonet_feedback($cid,$secid,$f1,$f2,$userid){
				global $DB;
				$timenow = time();

				if(!$cid || !$secid || !$f1 || !$f2){
					echo "your feedback has not saved. pls try again";
					exit(0);
				}

				$sonet_feedback = new stdClass();
				$sonet_feedback->cid     = $cid;
				$sonet_feedback->secid   =$secid;
				$sonet_feedback->updatedon   =$timenow;
				$sonet_feedback->userid   =$userid;
				$sonet_feedback->rating1=$f1;
				$sonet_feedback->rating2=$f2;
				//print_r($activity_status);
				$sql="SELECT `id` FROM `mdl_sonet_feedback` WHERE `userid` ='".$userid."' AND `cid` ='".$cid."'  AND `secid` ='".$secid."'";
				$feedbackres=$DB->get_record_sql($sql);
				$feedbackid=$feedbackres->id;

				try {

					if($feedbackid){
						echo "you have already given your feedback";
						//$sonet_feedback->id=$feedbackid;
						//$DB->update_record_raw('sonet_feedback', $sonet_feedback, false);

					}
					else{
						$DB->insert_record_raw('sonet_feedback', $sonet_feedback, false);
						echo "your feedback has been saved";
					}




				} catch (dml_write_exception $e) {
					// During a race condition we can fail to find the data, then it appears.
					// If we still can't find it, rethrow the exception.
						throw $e;
					echo "your feedback has not saved. pls try again";

				}
			}//end of save_sonet_feedback
/* sonet feedback */

/* save_sonet_topic  */
		function save_sonet_topic($cid,$secid,$cyear){
			global $DB;
			$timenow = time();
			$sonet_topic = new stdClass();
			$sonet_topic->cid     = $cid;
			$sonet_topic->secid   =$secid;
			$sonet_topic->cyear   =$cyear;
			$sonet_topic->topicname   =$DB->get_field('course_sections', 'name', array('course' => $cid,'id'=>$secid));
			$sonet_topic->takenon=date("y-m-d",time());
			$sonet_topic->updatedon   =$timenow;

			//print_r($activity_status);
			$sql="SELECT `id` FROM `mdl_sonet_topic` WHERE `cid` ='".$cid."'  AND `secid` ='".$secid."' AND cyear='".$cyear."'";
			$feedbackres=$DB->get_record_sql($sql);
			$feedbackid=$feedbackres->id;

			try {

				if($feedbackid){
					return 1;
				}
				else{
					$DB->insert_record_raw('sonet_topic', $sonet_topic, false);
					return 1;
				}

			} catch (dml_write_exception $e) {
				// During a race condition we can fail to find the data, then it appears.
				// If we still can't find it, rethrow the exception.
				throw $e;
				return 0;

			}
		}//end of save_sonet_topic
/* save_sonet_topic */
		function checkAskedForFeedback($cid,$secid){
			global $DB;
			$sql="SELECT `id` FROM `mdl_sonet_topic` WHERE `cid` ='".$cid."'  AND `secid` ='".$secid."'";
			$feedbackres=$DB->get_record_sql($sql);
			if($feedbackres){
				return $feedbackres->id;
			}else{
				return 0;
			}

		}//end of checkAskedForFeedback($cid,$secid)
		function checkYesterdayFeedback($uid,$cid,$secid){

			global $CFG,$DB;
			if($cid&&$secid){
				$coursename=$DB->get_field('course', 'fullname', array('id' => $cid));
				echo json_encode(array('cid'=>$cid,'secid'=>$secid,'cname'=>$coursename,'takenon'=>date("y-m-d",time())));
				exit(0);
			}
			$sonetcourses=$CFG->sonetcids;
			$checkfeedbackssql="SELECT * FROM `mdl_sonet_topic` WHERE cid IN (".implode(",",$sonetcourses).") ORDER BY `mdl_sonet_topic`.`id` DESC LIMIT 1 ";
			$feedbackcheckRes=$DB->get_record_sql($checkfeedbackssql);
			if($feedbackcheckRes){

				$nofeedback=$DB->get_field('sonet_feedback','id',array('cid'=>$feedbackcheckRes->cid,'secid'=>$feedbackcheckRes->secid,'userid'=>$uid));
				if(!$nofeedback){
					$globalcid=$feedbackcheckRes->cid;
					$globalsecid=$feedbackcheckRes->secid;
					$coursename=$DB->get_field('course', 'fullname', array('id' => $globalcid));
					$takenon=$feedbackcheckRes->takenon;

				}

			}
			if($globalcid&&$globalsecid){
				echo json_encode(array('cid'=>$globalcid,'secid'=>$globalsecid,'cname'=>$coursename,'takenon'=>$takenon));
			}else{
				echo json_encode(array());
			}


		}//checkYesterdayFeedback($uid)

/* sonet functionality end*/






?>


