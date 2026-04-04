<?php // $Id: $

/**
 * The purpose of this file is to add a log entry when the user views a
 * recording
 *
 * @author  Your Name <adelamarre@remote-learner.net>
 * @version $Id: view.php,v 1.1.2.13 2011/05/09 21:41:28 adelamarre Exp $
 * @package mod/teleconnect
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/tconnect_class.php');
require_once(dirname(__FILE__).'/tconnect_class_dom.php');

$id         = required_param('id', PARAM_INT);
$groupid    = required_param('groupid', PARAM_INT);
$recscoid   = required_param('recording', PARAM_INT);

global $CFG, $USER, $DB;

// Do the usual Moodle setup
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

// ---------- //


// Get HTTPS setting
$https      = false;
$protocol   = 'http://';
if (isset($CFG->teleconnect_https) and (!empty($CFG->teleconnect_https))) {
    $https      = true;
    $protocol   = 'https://';
}

// Create a Connect Pro login session for this user
$usrobj = new stdClass();
$usrobj = clone($USER);
$login  = $usrobj->username = set_tusername($usrobj->username, $usrobj->email);

$params = array('instanceid' => $cm->instance, 'groupid' => $groupid);
$sql = "SELECT meetingscoid FROM {teleconnect_meeting_groups} amg WHERE ".
       "amg.instanceid = :instanceid AND amg.groupid = :groupid";

$meetscoid = $DB->get_record_sql($sql, $params);

// Get the Meeting recording details
$tconnect   = tconnect_login();
$recording  = array();
$fldid      = tconnect_get_folder($tconnect, 'content');
$usrcanjoin = false;
$context = context_module::instance($cm->id);
$data       = tconnect_get_recordings($tconnect, $fldid, $meetscoid->meetingscoid);

/// Set page global
$url = new moodle_url('/mod/teleconnect/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($teleconnect->name));
$PAGE->set_heading($course->fullname);

if (!empty($data) && array_key_exists($recscoid, $data)) {

    $recording = $data[$recscoid];
} else {

    // If at first you don't succeed ...
    $data2 = tconnect_get_recordings($tconnect, $meetscoid->meetingscoid, $meetscoid->meetingscoid);

    if (!empty($data2) && array_key_exists($recscoid, $data2)) {
        $recording = $data2[$recscoid];
    }
}

tconnect_logout($tconnect);

if (empty($recording) and confirm_sesskey()) {
    notify(get_string('errormeeting', 'teleconnect'));
    die();
}

// If separate groups is enabled, check if the user is a part of the selected group
if (NOGROUPS != $cm->groupmode) {
    $usrgroups = groups_get_user_groups($cm->course, $USER
    ->id);
    $usrgroups = $usrgroups[0]; // Just want groups and not groupings

    $group_exists = false !== array_search($groupid, $usrgroups);
    $aag          = has_capability('moodle/site:accessallgroups', $context);

    if ($group_exists || $aag) {
        $usrcanjoin = true;
    }
} else {
    $usrcanjoin = true;
}


if (!$usrcanjoin) {
    notice(get_string('usergrouprequired', 'teleconnect'), $url);
}

// add_to_log($course->id, 'teleconnect', 'view',
//            "view.php?id=$cm->id", "View recording {$teleconnect->name} details", $cm->id);

// Trigger an event for viewing a recording.
$params = array(
    'relateduserid' => $USER->id,
    'courseid' => $course->id,
    'context' => context_module::instance($id),
);
$event = \mod_teleconnect\event\teleconnect_view_recording::create($params);
$event->trigger();

// Include the port number only if it is a port other than 80
$port = '';

if (!empty($CFG->teleconnect_port) and (80 != $CFG->teleconnect_port)) {
    $port = ':' . $CFG->teleconnect_port;
}

$tconnect = new tconnect_class_dom($CFG->teleconnect_host, $CFG->teleconnect_port,
                                  '', '', '', $https);

$tconnect->request_http_header_login(1, $login);
$telesession = $tconnect->get_cookie();

redirect($protocol . $CFG->teleconnect_meethost . $port
                     . $recording->url . '?session=' . $tconnect->get_cookie());
