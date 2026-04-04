<?php
/**
 * Created by PhpStorm.
 * User: tele
 * Date: 21/12/15
 * Time: 11:58 AM
 */

/*adobe connect*/
require_once($CFG->dirroot.'/mod/teleconnect/locallib.php');
require_once($CFG->dirroot.'/mod/teleconnect/tconnect_class.php');
require_once($CFG->dirroot.'/mod/teleconnect/tconnect_class_dom.php');
require_once($CFG->dirroot.'/mod/teleconnect/lib.php');

/*adobe connect*/


function getTeacherTeleUrl($activityid,$activity){

    $aid = $activityid;
    $groupid=0;
    $loginstatus=0;
    global $CFG, $USER, $DB;

    //var_dump("im here");


    if (!$cm = get_coursemodule_from_id('teleconnect', $aid)) {
        error('Course Module ID was incorrect');
    }

    $cond = array('id' => $cm->course);
    if (!$course = $DB->get_record('course', $cond)) {
        error('Course is misconfigured');
    }

    $cond = array('id' => $cm->instance);
    if (!$teleconnect = $DB->get_record('teleconnect', $cond)) {
        error('Course module is incorrect');
    }//echo 'im hersade';
    //var_dump($activity);//echo '</p>';
    $usrobj = new stdClass();
    $usrobj = clone($USER);

// var_dump($usrobj); exit(0);
	//var_dump($usrobj->email);

    // user has to be in a group
    if (confirm_sesskey(sesskey())) {

        $usrprincipal = 0;
        $validuser = true;


        // Get the meeting sco-id
        $param = array('instanceid' => $cm->instance, 'groupid' => $groupid);
        $meetingscoid = $DB->get_field('teleconnect_meeting_groups', 'meetingscoid', $param);


        $tconnect = tconnect_login();

//echo '</p>';
        // Check if the meeting still exists in the shared folder of the Adobe server
        $meetfldscoid = tconnect_get_folder($tconnect, 'meetings');
        $filter = array('filter-sco-id' => $meetingscoid);
        $meeting = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);
        //var_dump($meetfldscoid);

//var_dump($teleconnect->userid);
//echo '</p>';
        if (!empty($meeting)) {

            foreach($meeting as $meet){

              $str1 = str_replace(' ', '', $activity);
		$str2 = str_replace(' ', '', $meet->name);
                if(strcasecmp($str1,$str2)==0) {
                    $cmeeting=$meet;
                }
            }
            $meeting = $cmeeting;


        }

	return 'https://ngitone.adobeconnect.com:443'.$meeting->url;
}
}


function getTeleUrl($activityid,$activity){
    $aid = $activityid;
    $groupid=0;
    $loginstatus=0;
    global $CFG, $USER, $DB;

    //var_dump("im here");

    if (!$cm = get_coursemodule_from_id('teleconnect', $aid)) {
        error('Course Module ID was incorrect');
    }

    $cond = array('id' => $cm->course);
    if (!$course = $DB->get_record('course', $cond)) {
        error('Course is misconfigured');
    }

    $cond = array('id' => $cm->instance);
    if (!$teleconnect = $DB->get_record('teleconnect', $cond)) {
        error('Course module is incorrect');
    }//echo 'im hersade';
    //var_dump($activity);//echo '</p>';
    $usrobj = new stdClass();
    $usrobj = clone($USER);
//var_dump($usrobj); exit(0);
	//var_dump($usrobj->email);
    //$usrobj->username = set_username($usrobj->username, $usrobj->email);
    //var_dump($usrobj->username);
    //$context = context_module::instance($cm->id);
    // user has to be in a group
    if (confirm_sesskey(sesskey())) {

        $usrprincipal = 0;
        $validuser = true;


        // Get the meeting sco-id
        $param = array('instanceid' => $cm->instance, 'groupid' => $groupid);
        $meetingscoid = $DB->get_field('teleconnect_meeting_groups', 'meetingscoid', $param);


        $tconnect = tconnect_login();

//echo '</p>';
        // Check if the meeting still exists in the shared folder of the Adobe server
        $meetfldscoid = tconnect_get_folder($tconnect, 'meetings');
        $filter = array('filter-sco-id' => $meetingscoid);
        $meeting = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);
        //var_dump($meetfldscoid);

//var_dump($teleconnect->userid);
//echo '</p>';
        if (!empty($meeting)) {

            foreach($meeting as $meet){

              $str1 = str_replace(' ', '', $activity);
		$str2 = str_replace(' ', '', $meet->name);
                if(strcasecmp($str1,$str2)==0) {
                    $cmeeting=$meet;
                }
            }
            $meeting = $cmeeting;


        } else {

            /* Check if the module instance has a user associated with it
               if so, then check the user's adobe connect folder for existince of the meeting */
//var_dump($teleconnect->userid);
//echo '</p>';

             if (false){//||!empty($teleconnect->userid)) {
  //             echo "get username";
//var_dump($teleconnect->userid);
		return '';

		 $username = get_connect_username($teleconnect->userid);
                //if(!$username){echo "You are not a adobe user";}
	//	echo '</p>';
		$meetfldscoid = tconnect_get_user_folder_sco_id($tconnect, $username);
                $meeting = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

                if (!empty($meeting)) {
                    $meeting = current($meeting);
                }

            }
        }



//echo '</p>';
///////////////////////////////////////////////single login//////////////////////////////////////////////////////
        $logdata = array('action' => 'report-meeting-attendance','sco-id' => $meeting->scoid, 'filter-login' => $usrobj->email, 'filter-date-end' => null);
//echo '</p>';
        $tconnect->create_request($logdata);

        if ($tconnect->call_success()) {


            $xmlres = $tconnect->_xmlresponse;
            $parsexml = xml_parser_create();
            xml_parse_into_struct($parsexml, $xmlres, $xmlvals, $xmlindex);
            //xml_parser_free($p);
            if((count($xmlindex['DATE-END']))==(count($xmlindex['DATE-CREATED']))){
                /*echo '<pre class="xdebug-var-dump" dir="ltr"><small>Status : </small> <font color="#4e9a06">Not Logged In</font>
</pre>';*/
            }
            else{
                /*echo '<pre class="xdebug-var-dump" dir="ltr"><small>Status : </small> <font color="#4e9a06">Logged In</font>
</pre>';*/
                $loginstatus=1;
            }



        } else {
            if (user_has_role_assignment($USER->id, 3)){

            }else{
                echo '<pre class="xdebug-var-dump" dir="ltr"><small>Refresh Page</small> <font color="#4e9a06">Again</font>
</pre>';

	return;
            }

        }

	//if student already logged in
	  if($loginstatus==1){
            return null;
           }
	// return 'https://ngitone.adobeconnect.com:443'.$meeting->url;
    return 'https://kmitone.adobeconnect.com:443'.$meeting->url;

//////////////////////////////////////////////////////single login/////////////////////////////////////////

        //var_dump($meeting);



        $param = array('name' => 'teleconnect_admin_login');
        $adminid= $DB->get_field('config', 'value', $param);
        $param = array('name' => 'teleconnect_admin_password');
        $pwd     = $DB->get_field('config', 'value', $param);

        $seminarid=1112052800;
        $sessionname='www1_888';
        $quota=100;$starttime=1;$endtime=2;


        tconnect_logout($tconnect);
        $protocol = 'http://';
        $https = false;
        $login = $usrobj->username;

        if (isset($CFG->teleconnect_https) and (!empty($CFG->teleconnect_https))) {

            $protocol = 'https://';
            $https = true;
        }

        $tconnect = new tconnect_class_dom($CFG->teleconnect_host, $CFG->teleconnect_port,
            '', '', '', $https);

        $tconnect->request_http_header_login(1, $login);

        // Include the port number only if it is a port other than 80
        $port = '';

        if (!empty($CFG->teleconnect_port) and (80 != $CFG->teleconnect_port)) {
            $port = ':' . $CFG->teleconnect_port;
        }

        if (user_has_role_assignment($USER->id, 3)){
           //$teleconnecturl =$protocol . $CFG->teleconnect_meethost . $port. $meeting->url . '?action=login&login='.$adminid.'&password='.$pwd.'&session=' . $tconnect->get_cookie();
            $teleconnecturl =$protocol . $CFG->teleconnect_meethost . $port. $meeting->url;// . '?session=' . $tconnect->get_cookie();
        }
        else{
            //$teleconnecturl =$protocol . $CFG->teleconnect_meethost . $port. $meeting->url . '?action=login&login='.$usrobj->email.'&password=Tele123$&session=' . $tconnect->get_cookie();
            $teleconnecturl =$protocol . $CFG->teleconnect_meethost . $port. $meeting->url;// . '?session=' . $tconnect->get_cookie();

        }



       // https://meet95317649.teleconnect.com/api/xml?action=sco-session-seminar-list&sco-id=1111713766

      //  action=sco-session-seminar-list&sco-id=1111713766

        if($loginstatus==1){
            return null;
        }
        return $teleconnecturl;
    }//end of sesskey checking
}





