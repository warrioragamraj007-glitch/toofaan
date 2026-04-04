<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset  = optional_param('reset', null, PARAM_BOOL);
// var_dump("hello");
require_login();

function generate_string($input, $strength = 16) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}
// $serverlist = $_SESSION['serverlist'];
// global $DB;
// foreach ($serverlist as $ip) {
//     $parts = parse_url($ip);
//     $server = $parts['host'];

//       $sql = "INSERT INTO mdl_vpl_serverlist (ip_address) VALUES ('$server')";
//       $DB->execute($sql);
   
// }
//var_dump(user_has_role_assignment($USER->id, 3));
//teacher block
if (user_has_role_assignment($USER->id, 3)){
    global $USER;
    $url = new moodle_url('/local/teacher/dashboard.php');
    echo redirect($url);
//exit(0);
}



if (user_has_role_assignment($USER->id, 5)){

    global $USER,$DB;
    $userid=$USER->id;
    // var_dump("hello");
    // User Logged in with assigned ip / not checking and updating student pwd logic 
    if($CFG->strictexammode){
        // var_dump("hello");
	    $examipfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'examip'));
	    $currentip = getenv("REMOTE_ADDR");
	    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$examipfield."'";
	    $examip=$DB->get_record_sql($sql);
	    $examip=$examip->data;
	    if(strlen($examip)<3){//first time login sets the examip if examip is empty
		$sql="update `mdl_user_info_data` set `data`='".$currentip."' WHERE `userid` ='".$userid."' AND `fieldid` ='".$examipfield."'";
		$DB->execute($sql,null);
	    	$examip=$currentip;
	     }
	    $enrolledcourses = enrol_get_users_courses($USER->id);
	    $studentenrolledcourses=array();
	   
	    foreach ($enrolledcourses as $key => $value) {
		if (is_object($value)) {
		    $studentenrolledcourses[] = $value->id;
		}
	    }
	    
	    $_SESSION['examip']=$examip;
	    $p2=$DB->get_field('user', 'p2', array('id' => $USER->id)); // check passsword assigned or not
	    
	    if(  ($_SESSION['examip']!=getenv("REMOTE_ADDR"))){
		$url = new moodle_url('/local/student/unauthorised.php');
		echo redirect($url);
	    }


	    $permitted_chars = '123456789';
	    $newpasswordTXT = generate_string($permitted_chars, 7)."0";
        // var_dump($newpasswordTXT);
	    $password = MD5($newpasswordTXT);

	    $que1 = "update mdl_user set p1='".$p2."',p2='".$newpasswordTXT."',password='".$password."' WHERE id={$USER->id}";
	    $rs=$DB->execute($que1);
    }  //end of strict exam mode check
    

    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));

    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$sectionfield."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;

    $section=$studata;//get_complete_user_data(id,$userid)->profile['section'];

    $user_status = new stdClass();
	$user_status->loginstatus=2;
	$user_status->userid=$userid;
	$user_status->studentsection=$section;
    try {
                if($DB->get_field('userinfo_tsl', 'id', array('userid' => $userid))){
                     $user_status->id=$DB->get_field('userinfo_tsl', 'id', array('userid' => $userid));
                    echo $DB->update_record_raw('userinfo_tsl', $user_status, false);

        }
        else{
            echo $DB->insert_record_raw('userinfo_tsl', $user_status, false);

        }
        //echo 'executed';

    } catch (dml_write_exception $e) {
        // During a race condition we can fail to find the data, then it appears.
        // If we still can't find it, rethrow the exception.

            throw $e;

    }

    $url = new moodle_url('/local/student/dashboard.php');
    echo redirect($url);
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$strmymoodle = get_string('myhome');

if (empty($CFG->enabledashboard)) {
    // Dashboard is disabled, so the /my page shouldn't be displayed.
    $defaultpage = get_default_home_page();
    if ($defaultpage == HOMEPAGE_MYCOURSES) {
        // If default page is set to "My courses", redirect to it.
        redirect(new moodle_url('/my/courses.php'));
    } else {
        // Otherwise, raise an exception to inform the dashboard is disabled.
        throw new moodle_exception('error:dashboardisdisabled', 'my');
    }
}

if (isguestuser()) {  // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }

    $userid = null;
    $USER->editing = $edit = 0;  // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $strguest = get_string('guest');
    $pagetitle = "$strmymoodle ($strguest)";

} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $pagetitle = $strmymoodle;
}

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    throw new \moodle_exception('mymoodlesetup');
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/my/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)),
                    navigation_node::TYPE_SETTING);
        }
    }
}


// Toggle the editing state and switches
if (empty($CFG->forcedefaultmymoodle) && $PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            require_sesskey();
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PRIVATE)) {
                throw new \moodle_exception('reseterror', 'my');
            }
            redirect(new moodle_url('/my'));
        }
    } else if ($edit !== null) {             // Editing state was specified
        $USER->editing = $edit;       // Change editing state
    } else {                          // Editing state is in session
        if ($currentpage->userid) {   // It's a page we can edit, so load from session
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                throw new \moodle_exception('mymoodlesetup');
            }
            $context = context_user::instance($USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe
        }
    }

    // Add button for editing page
    $params = array('edit' => !$edit);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/my/index.php", array('edit' => 1, 'reset' => 1));

    if (!$currentpage->userid) {
        // viewing a system page -- let the user customise it
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/my/index.php", $params);
    $button = '';
    if (!$PAGE->theme->haseditswitch) {
        $button = $OUTPUT->single_button($url, $editstring);
    }
    $PAGE->set_button($resetbutton . $button);

} else {
    $USER->editing = $edit = 0;
}


echo $OUTPUT->header();

if (core_userfeedback::should_display_reminder()) {
    core_userfeedback::print_reminder_block();
}

echo $OUTPUT->addblockbutton('content');

echo $OUTPUT->custom_block_region('content');

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\dashboard_viewed::create($eventparams);
$event->trigger();
?>
<!-- srivardhin calendar -->
<style>
    .fitem.moreless-actions {
    display:none;
}
        </style>
