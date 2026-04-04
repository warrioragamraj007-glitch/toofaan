<?php
/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $PAGE;


//$PAGE->requires->js('/mod/teleconnect/teleconnect.js', true);

// $param = array('shortname' => 'teleconnectpresenter');
//     if (!$mrole = $DB->get_record('role', $param)) {

//         if ($rid = create_role(get_string('teleconnectpresenter', 'teleconnect'), 'teleconnectpresenter',
//                                get_string('teleconnectpresenterdescription', 'teleconnect'), 'teleconnectpresenter')) {

//             $mrole = new stdClass();
//             $mrole->id = $rid;
//             $result = $result && assign_capability('mod/teleconnect:meetingpresenter', CAP_ALLOW, $mrole->id, $sysctx->id);

//             set_role_contextlevels($mrole->id, $levels);
//         } else {
//             $result = false;
//         }
//     }

// $param = array('shortname' => 'teleconnectpresenter');
// $mrole = $DB->get_record('role', $param);

// if (!$mrole){
// 	// The commented out code is waiting for a fix for MDL-25709
//     $result = true;
//     $timenow = time();
//     $sysctx = context_system::instance();
//     $mrole = new stdClass();
//     $levels = array(CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE);

//     $param = array('shortname' => 'coursecreator');
//     $coursecreator = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     if (empty($coursecreator)) {
//         $param = array('archetype' => 'coursecreator');
//         $coursecreator = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     }
//     $coursecreatorrid = array_shift($coursecreator);

//     $param = array('shortname' =>'editingteacher');
//     $editingteacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     if (empty($editingteacher)) {
//         $param = array('archetype' => 'editingteacher');
//         $editingteacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     }
//     $editingteacherrid = array_shift($editingteacher);

//     $param = array('shortname' =>'teacher');
//     $teacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     if (empty($teacher)) {
//         $param = array('archetype' => 'teacher');
//         $teacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
//     }
//     $teacherrid = array_shift($teacher);

//     // Fully setup the Adobe Connect Presenter role.
//     $param = array('shortname' => 'teleconnectpresenter');
//     if (!$mrole = $DB->get_record('role', $param)) {

//         if ($rid = create_role(get_string('teleconnectpresenter', 'teleconnect'), 'teleconnectpresenter',
//                                get_string('teleconnectpresenterdescription', 'teleconnect'), 'teleconnectpresenter')) {

//             $mrole = new stdClass();
//             $mrole->id = $rid;
//             $result = $result && assign_capability('mod/teleconnect:meetingpresenter', CAP_ALLOW, $mrole->id, $sysctx->id);

//             set_role_contextlevels($mrole->id, $levels);
//         } else {
//             $result = false;
//         }
//     }

//     if (isset($coursecreatorrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($coursecreatorrid->id, $mrole->id);
//         }
//     }

//     if (isset($editingteacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($editingteacherrid->id, $mrole->id);
//         }
//     }

//     if (isset($teacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($teacherrid->id, $mrole->id);
//         }
//     }

//     // Fully setup the Adobe Connect Participant role.
//     $param = array('shortname' => 'teleconnectparticipant');

//     if ($result && !($mrole = $DB->get_record('role', $param))) {

//         if ($rid = create_role(get_string('teleconnectparticipant', 'teleconnect'), 'teleconnectparticipant',
//                                get_string('teleconnectparticipantdescription', 'teleconnect'), 'teleconnectparticipant')) {

//             $mrole = new stdClass();
//             $mrole->id  = $rid;
//             $result = $result && assign_capability('mod/teleconnect:meetingparticipant', CAP_ALLOW, $mrole->id, $sysctx->id);
//             set_role_contextlevels($mrole->id, $levels);
//         } else {
//             $result = false;
//         }
//     }

//     if (isset($coursecreatorrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($coursecreatorrid->id, $mrole->id);
//         }
//     }

//     if (isset($editingteacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($editingteacherrid->id, $mrole->id);
//         }
//     }

//     if (isset($teacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($teacherrid->id, $mrole->id);
//         }
//     }


//     // Fully setup the Adobe Connect Host role.
//     $param = array('shortname' => 'teleconnecthost');
//     if ($result && !$mrole = $DB->get_record('role', $param)) {
//         if ($rid = create_role(get_string('teleconnecthost', 'teleconnect'), 'teleconnecthost',
//                                get_string('teleconnecthostdescription', 'teleconnect'), 'teleconnecthost')) {

//             $mrole = new stdClass();
//             $mrole->id  = $rid;
//             $result = $result && assign_capability('mod/teleconnect:meetinghost', CAP_ALLOW, $mrole->id, $sysctx->id);
//             set_role_contextlevels($mrole->id, $levels);
//         } else {
//             $result = false;
//         }
//     }

//     if (isset($coursecreatorrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($coursecreatorrid->id, $mrole->id);
//         }
//     }

//     if (isset($editingteacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($editingteacherrid->id, $mrole->id);
//         }
//     }

//     if (isset($teacherrid->id)) {
//         $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
//         if (!$DB->get_record('role_allow_assign', $param)) {
//             core_role_set_assign_allowed($teacherrid->id, $mrole->id);
//         }
//     }
// }



if ($ADMIN->fulltree) {
//print_object($ADMIN);

    require_once($CFG->dirroot . '/mod/teleconnect/locallib.php');
    $PAGE->requires->js_init_call('M.mod_teleconnect.init');

//$data = get_data();

    //require_js($CFG->wwwroot . '/mod/teleconnect/testserverconnection.js');


    $settings->add(new admin_setting_configtext('teleconnect_host', get_string('host', 'teleconnect'),
                       get_string('host_desc', 'teleconnect'), 'localhost/api/xml', PARAM_URL));

    $settings->add(new admin_setting_configtext('teleconnect_meethost', get_string('meetinghost', 'teleconnect'),
                       get_string('meethost_desc', 'teleconnect'), 'localhost', PARAM_URL));

    $settings->add(new admin_setting_configtext('teleconnect_port', get_string('port', 'teleconnect'),
                       get_string('port_desc', 'teleconnect'), '80', PARAM_INT));

    $settings->add(new admin_setting_configtext('teleconnect_admin_login', get_string('admin_login', 'teleconnect'),
                       get_string('admin_login_desc', 'teleconnect'), 'admin', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('teleconnect_admin_password', get_string('admin_password', 'teleconnect'),
                       get_string('admin_password_desc', 'teleconnect'), ''));

    $settings->add(new admin_setting_configtext('teleconnect_admin_httpauth', get_string('admin_httpauth', 'teleconnect'),
                       get_string('admin_httpauth_desc', 'teleconnect'), 'my-user-id', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('teleconnect_email_login', get_string('email_login', 'teleconnect'),
                       get_string('email_login_desc', 'teleconnect'), '0'));

    $settings->add(new admin_setting_configcheckbox('teleconnect_https', get_string('https', 'teleconnect'),
                       get_string('https_desc', 'teleconnect'), '0'));


    $url = $CFG->wwwroot . '/mod/teleconnect/conntest.php';
    $url = htmlentities($url, ENT_COMPAT, 'UTF-8');
    $options = 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=300';
    $str = '<center><input type="button" onclick="window.open(\''.$url.'\', \'\', \''.$options.'\');" value="'.
           get_string('testconnection', 'teleconnect') . '" /></center>';

    $settings->add(new admin_setting_heading('teleconnect_test', '', $str));

    $param = new stdClass();
    $param->image = $CFG->wwwroot.'/mod/teleconnect/pix/rl_logo.png';
    $param->url = 'https://moodle.org/plugins/view.php?plugin=mod_teleconnect';

    $settings->add(new admin_setting_heading('teleconnect_intro', '', get_string('settingblurb', 'teleconnect', $param)));
}
