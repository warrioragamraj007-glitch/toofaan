<?php

/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/tconnect_class.php');
require_once(dirname(__FILE__).'/tconnect_class_dom.php');

$id       = required_param('id', PARAM_INT); // course_module ID, or
$groupid  = required_param('groupid', PARAM_INT);
$sesskey  = required_param('sesskey', PARAM_ALPHANUM);


global $CFG, $USER, $DB, $PAGE;

if (! $cm = get_coursemodule_from_id('teleconnect', $id)) {
    error('Course Module ID was incorrect');
}

$cond = array('id' => $cm->course);
if (! $course = $DB->get_record('course', $cond)) {
    error('Course is misconfigured');
}

$cond = array('id' => $cm->instance);
if (! $teleconnect = $DB->get_record('teleconnect', $cond)) {
    error('Course module is incorrect');
}

require_login($course, true, $cm);

// Check if the user's email is the Connect Pro user's login
$usrobj = new stdClass();
$usrobj = clone($USER);
$usrobj->username = set_tusername($usrobj->username, $usrobj->email);

//var_dump($usrobj);
$usrcanjoin = false;

$context = context_module::instance($cm->id);

// If separate groups is enabled, check if the user is a part of the selected group
if (NOGROUPS != $cm->groupmode) {

    $usrgroups = groups_get_user_groups($cm->course, $usrobj->id);
    $usrgroups = $usrgroups[0]; // Just want groups and not groupings

    $group_exists = false !== array_search($groupid, $usrgroups);
    $aag          = has_capability('moodle/site:accessallgroups', $context);

    if ($group_exists || $aag) {
        $usrcanjoin = true;
    }
} else {
    $usrcanjoin = true;
}

/// Set page global
$url = new moodle_url('/mod/teleconnect/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($teleconnect->name));
$PAGE->set_heading($course->fullname);

// user has to be in a group
if ($usrcanjoin and confirm_sesskey($sesskey)) {

    $usrprincipal = 0;
    $validuser    = true;

    // Get the meeting sco-id
    $param        = array('instanceid' => $cm->instance, 'groupid' => $groupid);
    $meetingscoid = $DB->get_field('teleconnect_meeting_groups', 'meetingscoid', $param);

    $tconnect = tconnect_login();

    // Check if the meeting still exists in the shared folder of the tele server
    $meetfldscoid = tconnect_get_folder($tconnect, 'meetings');
    $filter       = array('filter-sco-id' => $meetingscoid);
    $meeting      = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

    if (!empty($meeting)) {
        $meeting = current($meeting);
    } else {

        /* Check if the module instance has a user associated with it
           if so, then check the user's tele connect folder for existince of the meeting */
        if (!empty($teleconnect->userid)) {
            $username     = get_tconnect_username($teleconnect->userid);
            $meetfldscoid = tconnect_get_user_folder_sco_id($tconnect, $username);
            $meeting      = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

            if (!empty($meeting)) {
                $meeting = current($meeting);
            }

        }
    }



//var_dump(tconnect_user_exists($tconnect, $usrobj));
    if (!($usrprincipal = tconnect_user_exists($tconnect, $usrobj))) {
        if (!($usrprincipal = tconnect_create_user($tconnect, $usrobj))) {
            // DEBUG
            print_object("error creating user");
            print_object($tconnect->_xmlresponse);
            $validuser = false;
        }
    }
    //$usrprincipal = tconnect_user_exists($tconnect, $usrobj);
    //$usrprincipal = tconnect_create_user($tconnect, $usrobj);

    // Check the user's capabilities and assign them the tele Role
    if (!empty($meetingscoid) and !empty($usrprincipal) and !empty($meeting)) {
        if (has_capability('mod/teleconnect:meetinghost', $context, $usrobj->id, false)) {
            if (tconnect_check_user_perm($tconnect, $usrprincipal, $meetingscoid, tele_HOST, true)) {
                //DEBUG
//                 echo 'host';
//                 die();
            } else {
                //DEBUG
                print_object('error assign user tele host role');
                print_object($tconnect->_xmlrequest);
                print_object($tconnect->_xmlresponse);
                $validuser = false;
            }
        } elseif (has_capability('mod/teleconnect:meetingpresenter', $context, $usrobj->id, false)) {
            if (tconnect_check_user_perm($tconnect, $usrprincipal, $meetingscoid, tele_PRESENTER, true)) {
                //DEBUG
//                 echo 'presenter';
//                 die();
            } else {
                //DEBUG
                print_object('error assign user tele presenter role');
                print_object($tconnect->_xmlrequest);
                print_object($tconnect->_xmlresponse);
                $validuser = false;
            }
        } elseif (has_capability('mod/teleconnect:meetingparticipant', $context, $usrobj->id, false)) {
            if (tconnect_check_user_perm($tconnect, $usrprincipal, $meetingscoid, tele_PARTICIPANT, true)) {
                //DEBUG
//                 echo 'participant';
//                 die();
            } else {
                //DEBUG
                print_object('error assign user tele particpant role');
                print_object($tconnect->_xmlrequest);
                print_object($tconnect->_xmlresponse);
                $validuser = false;
            }
        } else {
            // Check if meeting is public and allow them to join
            if ($teleconnect->meetingpublic) {
                // if for a public meeting the user does not not have either of presenter or participant capabilities then give
                // the user the participant role for the meeting
                tconnect_check_user_perm($tconnect, $usrprincipal, $meetingscoid, tele_PARTICIPANT, true);
                $validuser = true;
            } else {
                $validuser = false;
            }
        }
    } else {
        $validuser = false;
        notice(get_string('unableretrdetails', 'teleconnect'), $url);
    }

    tconnect_logout($tconnect);

    // User is either valid or invalid, if valid redirect user to the meeting url
    if (empty($validuser)) {
        notice(get_string('notparticipant', 'teleconnect'), $url);
    } else {

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

        // add_to_log($course->id, 'teleconnect', 'join meeting',
        //            "join.php?id=$cm->id&groupid=$groupid&sesskey=$sesskey",
        //            "Joined $teleconnect->name meeting", $cm->id);

        // Trigger an event for joining a meeting.
        $params = array(
            'relateduserid' => $USER->id,
            'courseid' => $course->id,
            'context' => context_module::instance($id),
        );
        $event = \mod_teleconnect\event\teleconnect_join_meeting::create($params);
        $event->trigger();


        /*redirect($protocol . $CFG->teleconnect_meethost . $port. $meeting->url . '?action=login&login='.$usrobj->email.'&password=Tele123$&session=' . $tconnect->get_cookie());*/

        redirect($protocol . $CFG->teleconnect_meethost . $port
                 . $meeting->url
                 . '?session=' . $tconnect->get_cookie());
    }
} else {
    notice(get_string('usergrouprequired', 'teleconnect'), $url);
}
