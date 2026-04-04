<?php

/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace teleconnect with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/tconnect_class.php');
require_once(dirname(__FILE__).'/tconnect_class_dom.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // teleconnect instance ID
$groupid = optional_param('group', 0, PARAM_INT);

global $CFG, $USER, $DB, $PAGE, $OUTPUT, $SESSION;

if ($id) {
    if (! $cm = get_coursemodule_from_id('teleconnect', $id)) {
        print_error('Course Module ID was incorrect');
    }

    $cond = array('id' => $cm->course);
    if (! $course = $DB->get_record('course', $cond)) {
        print_error('Course is misconfigured');
    }

    $cond = array('id' => $cm->instance);
    if (! $teleconnect = $DB->get_record('teleconnect', $cond)) {
        print_error('Course module is incorrect');
    }

} else if ($a) {

    $cond = array('id' => $a);
    if (! $teleconnect = $DB->get_record('teleconnect', $cond)) {
        print_error('Course module is incorrect');
    }

    $cond = array('id' => $teleconnect->course);
    if (! $course = $DB->get_record('course', $cond)) {
        print_error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('teleconnect', $teleconnect->id, $course->id)) {
        print_error('Course Module ID was incorrect');
    }

} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// Check for submitted data
if (($formdata = data_submitted($CFG->wwwroot . '/mod/teleconnect/view.php')) && confirm_sesskey()) {

    // Edit participants
    if (isset($formdata->participants)) {

        $cond = array('shortname' => 'teleconnectpresenter');
        $roleid = $DB->get_field('role', 'id', $cond);

        if (!empty($roleid)) {
            redirect("participants.php?id=$id&contextid={$context->id}&roleid=$roleid&groupid={$formdata->group}", '', 0);
        } else {
            $message = get_string('nopresenterrole', 'teleconnect');
            $OUTPUT->notification($message);
        }
    }
}


// Check if the user's email is the Connect Pro user's login
$usrobj = new stdClass();
$usrobj = clone($USER);

$usrobj->username = set_tusername($usrobj->username, $usrobj->email);

/// Print the page header
$url = new moodle_url('/mod/teleconnect/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($teleconnect->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$strteleconnects = get_string('modulenameplural', 'teleconnect');
$strteleconnect  = get_string('modulename', 'teleconnect');

$params = array('instanceid' => $cm->instance);
$sql = "SELECT meetingscoid ".
       "FROM {teleconnect_meeting_groups} amg ".
       "WHERE amg.instanceid = :instanceid ";

$meetscoids = $DB->get_records_sql($sql, $params);
$recording = array();

if (!empty($meetscoids)) {
    $recscoids = array();

    $tconnect = tconnect_login();

    // Get the forced recordings folder sco-id
    // Get recordings that are based off of the meeting
    $fldid = tconnect_get_folder($tconnect, 'forced-archives');
    foreach($meetscoids as $scoid) {

        $data = tconnect_get_recordings($tconnect, $fldid, $scoid->meetingscoid);

        if (!empty($data)) {
          // Store recordings in an array to be moved to the tele shared folder later on
          $recscoids = array_merge($recscoids, array_keys($data));

        }

    }

    // Move the meetings to the shared content folder
    if (!empty($recscoids)) {
        $recscoids = array_flip($recscoids);

        if (tconnect_move_to_shared($tconnect, $recscoids)) {
            // do nothing
        }
    }

    //Get the shared content folder sco-id
    // Create a list of recordings moved to the shared content folder
    $fldid = tconnect_get_folder($tconnect, 'content');
    foreach($meetscoids as $scoid) {

        // May need this later on
        $data = tconnect_get_recordings($tconnect, $fldid, $scoid->meetingscoid);

        if (!empty($data)) {
            $recording[] = $data;
        }

        $data2 = tconnect_get_recordings($tconnect, $scoid->meetingscoid, $scoid->meetingscoid);

        if (!empty($data2)) {
             $recording[] = $data2;
        }

    }


    // Clean up any duplciated meeting recordings.  Duplicated meeting recordings happen when the
    // recording settings on ACP server change between "publishing recording links in meeting folders" and
    // not "publishing recording links in meeting folders"
    $names = array();
    foreach ($recording as $key => $recordingarray) {

        foreach ($recordingarray as $key2 => $record) {


            if (!empty($names)) {

                if (!array_search($record->name, $names)) {

                    $names[] = $record->name;$params = array('instanceid' => $cm->instance);
$sql = "SELECT meetingscoid ".
       "FROM {teleconnect_meeting_groups} amg ".
       "WHERE amg.instanceid = :instanceid ";

$meetscoids = $DB->get_records_sql($sql, $params);
                } else {

                    unset($recording[$key][$key2]);
                }
            } else {

                $names[] = $record->name;
            }
        }
    }

    unset($names);


    // Check if the user exists and if not create the new user
    if (!($usrprincipal = tconnect_user_exists($tconnect, $usrobj))) {
        if (!($usrprincipal = tconnect_create_user($tconnect, $usrobj))) {
            // DEBUG
            debugging("error creating user", DEBUG_DEVELOPER);

//            print_object("error creating user");
//            print_object($tconnect->_xmlresponse);
            $validuser = false;
        }
    }

    // Check the user's capability and assign them view permissions to the recordings folder
    // if it's a public meeting give them permissions regardless
    if ($cm->groupmode) {


        if (has_capability('mod/teleconnect:meetingpresenter', $context, $usrobj->id) or
            has_capability('mod/teleconnect:meetingparticipant', $context, $usrobj->id)) {
            if (tconnect_assign_user_perm($tconnect, $usrprincipal, $fldid, tele_VIEW_ROLE)) {
                //DEBUG
                // echo 'true';
            } else {
                //DEBUG
                debugging("error assign user recording folder permissions", DEBUG_DEVELOPER);
//                print_object('error assign user recording folder permissions');
//                print_object($tconnect->_xmlrequest);
//                print_object($tconnect->_xmlresponse);
            }
        }
    } else {
        tconnect_assign_user_perm($tconnect, $usrprincipal, $fldid, tele_VIEW_ROLE);
    }

    tconnect_logout($tconnect);
}

// Log in the current user
$login = $usrobj->username;
$password  = $usrobj->username;
$https = false;

if (isset($CFG->teleconnect_https) and (!empty($CFG->teleconnect_https))) {
    $https = true;
}

$tconnect = new tconnect_class_dom($CFG->teleconnect_host, $CFG->teleconnect_port,
                                  '', '', '', $https);

$tconnect->request_http_header_login(1, $login);
$telesession = $tconnect->get_cookie();

// The batch of code below handles the display of Moodle groups
if ($cm->groupmode) {

    $querystring = array('id' => $cm->id);
    $url = new moodle_url('/mod/teleconnect/view.php', $querystring);

    // Retrieve a list of groups that the current user can see/manage
    $user_groups = groups_get_activity_allowed_groups($cm, $USER->id);

    if ($user_groups) {

        // Print groups selector drop down
        groups_print_activity_menu($cm, $url, false, true);


        // Retrieve the currently active group for the user's session
        $groupid = groups_get_activity_group($cm);

        /* Depending on the series of events groups_get_activity_group will
         * return a groupid value of  0 even if the user belongs to a group.
         * If the groupid is set to 0 then use the first group that the user
         * belongs to.
         */
        $aag = has_capability('moodle/site:accessallgroups', $context);

        if (0 == $groupid) {
            $groups = groups_get_user_groups($cm->course, $USER->id);
            $groups = current($groups);

            if (!empty($groups)) {

                $groupid = key($SESSION->activegroup[$cm->course]);
            } elseif ($aag) {
                /* If the user does not explicitely belong to any group
                 * check their capabilities to see if they have access
                 * to manage all groups; and if so display the first course
                 * group by default
                 */
                $groupid = key($user_groups);
            }
        }
    }
}


$tconnect = tconnect_login();

// Get the Meeting details
$cond = array('instanceid' => $teleconnect->id, 'groupid' => $groupid);
$scoid = $DB->get_field('teleconnect_meeting_groups', 'meetingscoid', $cond);

$meetfldscoid = tconnect_get_folder($tconnect, 'meetings');


$filter = array('filter-sco-id' => $scoid);

if (($meeting = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter))) {
    $meeting = current($meeting);
} else {

    /* First check if the module instance has a user associated with it
       if so, then check the user's tele connect folder for existince of the meeting */
    if (!empty($teleconnect->userid)) {
        $username     = get_tconnect_username($teleconnect->userid);
        $meetfldscoid = tconnect_get_user_folder_sco_id($tconnect, $username);
        $meeting      = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

        if (!empty($meeting)) {
            $meeting = current($meeting);
        }
    }

    // If meeting does not exist then display an error message
    if (empty($meeting)) {

        $message = get_string('nomeeting', 'teleconnect');
        echo $OUTPUT->notification($message);
        tconnect_logout($tconnect);
        die();
    }
}

tconnect_logout($tconnect);

$sesskey = !empty($usrobj->sesskey) ? $usrobj->sesskey : '';

$renderer = $PAGE->get_renderer('mod_teleconnect');

$meetingdetail = new stdClass();
$meetingdetail->name = html_entity_decode($meeting->name);

// Determine if the Meeting URL is to appear
if (has_capability('mod/teleconnect:meetingpresenter', $context) or
    has_capability('mod/teleconnect:meetinghost', $context)) {

    // Include the port number only if it is a port other than 80
    $port = '';

    if (!empty($CFG->teleconnect_port) and (80 != $CFG->teleconnect_port)) {
        $port = ':' . $CFG->teleconnect_port;
    }

    $protocol = 'http://';

    if ($https) {
        $protocol = 'https://';
    }

    $url = $protocol . $CFG->teleconnect_meethost . $port
           . $meeting->url;

    $meetingdetail->url = $url;


    $url = $protocol.$CFG->teleconnect_meethost.$port.'/admin/meeting/sco/info?principal-id='.
           $usrprincipal.'&amp;sco-id='.$scoid.'&amp;session='.$telesession;

    // Get the server meeting details link
    $meetingdetail->servermeetinginfo = $url;

} else {
    $meetingdetail->url = '';
    $meetingdetail->servermeetinginfo = '';
}

// Determine if the user has the permissions to assign perticipants
$meetingdetail->participants = false;

if (has_capability('mod/teleconnect:meetingpresenter', $context, $usrobj->id) or
    has_capability('mod/teleconnect:meetinghost', $context, $usrobj->id)){

    $meetingdetail->participants = true;
}

//  CONTRIB-2929 - remove date format and let Moodle decide the format
// Get the meeting start time
$time = userdate($teleconnect->starttime);
$meetingdetail->starttime = $time;

// Get the meeting end time
$time = userdate($teleconnect->endtime);
$meetingdetail->endtime = $time;

// Get the meeting intro text
$meetingdetail->intro = $teleconnect->intro;
$meetingdetail->introformat = $teleconnect->introformat;

echo $OUTPUT->box_start('generalbox', 'meetingsummary');

// If groups mode is enabled for the activity and the user belongs to a group
if (NOGROUPS != $cm->groupmode && 0 != $groupid) {

    echo $renderer->display_meeting_detail($meetingdetail, $id, $groupid);
} else if (NOGROUPS == $cm->groupmode) {

    // If groups mode is disabled
    echo $renderer->display_meeting_detail($meetingdetail, $id, $groupid);
} else {

    // If groups mode is enabled but the user is not in a group
    echo $renderer->display_no_groups_message();
}

echo $OUTPUT->box_end();

echo '<br />';

$showrecordings = false;
// Check if meeting is private, if so check the user's capability.  If public show recorded meetings
if (!$teleconnect->meetingpublic) {

    // Check capabilities
    if (has_capability('mod/teleconnect:meetingpresenter', $context, $usrobj->id) or
        has_capability('mod/teleconnect:meetingparticipant', $context, $usrobj->id)) {
        $showrecordings = true;
    }
} else {

    // Check group mode and group membership
    $showrecordings = true;
}

// Lastly check group mode and group membership
if (NOGROUPS != $cm->groupmode && 0 != $groupid) {
    $showrecordings = $showrecordings && true;
} elseif (NOGROUPS == $cm->groupmode) {
    $showrecording = $showrecordings && true;
} else {
    $showrecording = $showrecordings && false;
}

$recordings = $recording;

if ($showrecordings and !empty($recordings)) {
    echo $OUTPUT->box_start('generalbox', 'meetingsummary');

    // Echo the rendered HTML to the page
    echo $renderer->display_meeting_recording($recordings, $cm->id, $groupid, $scoid);

    echo $OUTPUT->box_end();
}




// add_to_log($course->id, 'teleconnect', 'view',
//            "view.php?id=$cm->id", "View {$teleconnect->name} details", $cm->id);

// Trigger an event for joining a meeting.
$params = array(
    'relateduserid' => $USER->id,
    'courseid' => $course->id,
    'context' => context_module::instance($cm->id)
);

// $event = \mod_teleconnect\event\teleconnect_view::create($params);
// $event->trigger();

/// Finish the page
echo $OUTPUT->footer();
