<?php
/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/teleconnect/locallib.php');

class mod_teleconnect_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE, $CFG;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('teleconnectname', 'teleconnect'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    /// Adding the required "intro" field to hold the description of the instance
        $this->add_intro_editor(false, get_string('teleconnectintro', 'teleconnect'));

//        $mform->addElement('htmleditor', 'intro', get_string('teleconnectintro', 'teleconnect'));
//        $mform->setType('intro', PARAM_RAW);
//        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
//        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

    /// Adding "introformat" field
//        $mform->addElement('format', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
    /// Adding the rest of teleconnect settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic

        $mform->addElement('header', 'teleconnectfieldset', get_string('teleconnectfieldset', 'teleconnect'));

        // Meeting URL
        $attributes=array('size'=>'20');
        $mform->addElement('text', 'meeturl', get_string('meeturl', 'teleconnect'), $attributes);
        $mform->setType('meeturl', PARAM_PATH);
        $mform->addHelpButton('meeturl', 'meeturl', 'teleconnect');
//        $mform->addHelpButton('meeturl', array('meeturl', get_string('meeturl', 'teleconnect'), 'teleconnect'));
        $mform->disabledIf('meeturl', 'tempenable', 'eq', 0);

        // Public or private meeting
        $meetingpublic = array(1 => get_string('public', 'teleconnect'), 0 => get_string('private', 'teleconnect'));
        $mform->addElement('select', 'meetingpublic', get_string('meetingtype', 'teleconnect'), $meetingpublic);
        $mform->addHelpButton('meetingpublic', 'meetingtype', 'teleconnect');
//        $mform->addHelpButton('meetingpublic', array('meetingtype', get_string('meetingtype', 'teleconnect'), 'teleconnect'));

        // Meeting Template
        $templates = array();
        $templates = $this->get_templates();
        ksort($templates);
        $mform->addElement('select', 'templatescoid', get_string('meettemplates', 'teleconnect'), $templates);
        $mform->addHelpButton('templatescoid', 'meettemplates', 'teleconnect');
//        $mform->addHelpButton('templatescoid', array('templatescoid', get_string('meettemplates', 'teleconnect'), 'teleconnect'));
        $mform->disabledIf('templatescoid', 'tempenable', 'eq', 0);


        $mform->addElement('hidden', 'tempenable');
        $mform->setType('tempenable', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        // Start and end date selectors
        $time       = time();
        $starttime  = usertime($time);
        $mform->addElement('date_time_selector', 'starttime', get_string('starttime', 'teleconnect'));
        $mform->addElement('date_time_selector', 'endtime', get_string('endtime', 'teleconnect'));
        $mform->setDefault('endtime', strtotime('+2 hours'));


//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements(array('groups' => true));

        // Disabled the group mode if the meeting has already been created
        $mform->disabledIf('groupmode', 'tempenable', 'eq', 0);
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }

    function data_preprocessing(&$default_values) {
        global $CFG, $DB;

        if (array_key_exists('update', $default_values)) {

            $params = array('instanceid' => $default_values['id']);
            $sql = "SELECT id FROM {teleconnect_meeting_groups} WHERE ".
                   "instanceid = :instanceid";

            if ($DB->record_exists_sql($sql, $params)) {
                $default_values['tempenable'] = 0;
            }
        }
    }

    function validation($data, $files) {
        global $CFG, $DB, $USER, $COURSE;

        $errors = parent::validation($data, $files);

        $username     = set_tusername($USER->username, $USER->email);
        $usr_fldscoid = '';
        $tconnect     = tconnect_login();

        // Search for a Meeting with the same starting name.  It will cause a duplicate
        // meeting name (and error) when the user begins to add participants to the meeting
        $meetfldscoid = tconnect_get_folder($tconnect, 'meetings');
        $filter = array('filter-like-name' => $data['name']);
        $namematches = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

        /// Search the user's tele connect folder
        $usrfldscoid = tconnect_get_user_folder_sco_id($tconnect, $username);

	if (!empty($usrfldscoid)) {
        	$namematches = $namematches + tconnect_meeting_exists($tconnect, $usrfldscoid, $filter);
        }

        if (empty($namematches)) {
            $namematches = array();
        }

        // Now search for existing meeting room URLs
        $url = $data['meeturl'];
        $url = $data['meeturl'] = teleconnect_clean_meet_url($data['meeturl']);

        // Check to see if there are any trailing slashes or additional parts to the url
        // ex. mymeeting/mysecondmeeting/  Only the 'mymeeting' part is valid
        if ((0 != substr_count($url, '/')) and (false !== strpos($url, '/', 1))) {
            $errors['meeturl'] = get_string('invalidtelemeeturl', 'teleconnect');
        }

        $filter = array('filter-like-url-path' => $url);
        $urlmatches = tconnect_meeting_exists($tconnect, $meetfldscoid, $filter);

        /// Search the user's tele connect folder
        if (!empty($usrfldscoid)) {
            $urlmatches = $urlmatches + tconnect_meeting_exists($tconnect, $usrfldscoid, $filter);
        }

        if (empty($urlmatches)) {
            $urlmatches = array();
        } else {

            // format url for comparison
            if ((false === strpos($url, '/')) or (0 != strpos($url, '/'))) {
                $url = '/' . $url;
            }

        }

        // Check URL for correct length and format
        if (strlen($data['meeturl']) > 60) {
            $errors['meeturl'] = get_string('longurl', 'teleconnect');
        } elseif (empty($data['meeturl'])) {
            // Do nothing
        } elseif (!preg_match('/^[a-z][a-z\-]*/i', $data['meeturl'])) {
            $errors['meeturl'] = get_string('invalidurl', 'teleconnect');
        }

        // Check for available groups if groupmode is selected
        if ($data['groupmode'] > 0) {
            $crsgroups = groups_get_all_groups($COURSE->id);
            if (empty($crsgroups)) {
                $errors['groupmode'] = get_string('missingexpectedgroups', 'teleconnect');
            }
        }

        // Adding activity
        if (empty($data['update'])) {

            if ($data['starttime'] == $data['endtime']) {
                $errors['starttime'] = get_string('samemeettime', 'teleconnect');
                $errors['endtime'] = get_string('samemeettime', 'teleconnect');
            } elseif ($data['endtime'] < $data['starttime']) {
                $errors['starttime'] = get_string('greaterstarttime', 'teleconnect');
            }

            // Check for local activities with the same name
            $params = array('name' => $data['name']);
            if ($DB->record_exists('teleconnect', $params)) {
                $errors['name'] = get_string('duplicatemeetingname', 'teleconnect');
                return $errors;
            }

            // Check tele connect server for duplicated names
            foreach($namematches as $matchkey => $match) {
                if (0 == substr_compare($match->name, $data['name'] . '_', 0, strlen($data['name'] . '_'), false)) {
                    $errors['name'] = get_string('duplicatemeetingname', 'teleconnect');
                }
            }

            foreach($urlmatches as $matchkey => $match) {
                $matchurl = rtrim($match->url, '/');
                if (0 == substr_compare($matchurl, $url . '_', 0, strlen($url . '_'), false)) {
                    $errors['meeturl'] = get_string('duplicateurl', 'teleconnect');
                }
            }

        } else {
            // Updating activity
            // Look for existing meeting names, excluding this activity's group meeting(s)
            $params = array('instanceid' => $data['instance']);
            $sql = "SELECT meetingscoid, groupid FROM {teleconnect_meeting_groups} ".
                   " WHERE instanceid = :instanceid";

            $grpmeetings = $DB->get_records_sql($sql, $params);

            if (empty($grpmeetings)) {
                $grpmeetings = array();
            }

            foreach($namematches as $matchkey => $match) {
                if (!array_key_exists($match->scoid, $grpmeetings)) {
                    if (0 == substr_compare($match->name, $data['name'] . '_', 0, strlen($data['name'] . '_'), false)) {
                        $errors['name'] = get_string('duplicatemeetingname', 'teleconnect');
                    }
                }
            }

            foreach($urlmatches as $matchkey => $match) {
                if (!array_key_exists($match->scoid, $grpmeetings)) {
                    if (0 == substr_compare($match->url, $url . '_', 0, strlen($url . '_'), false)) {
                        $errors['meeturl'] = get_string('duplicateurl', 'teleconnect');
                    }
                }
            }

            // Validate start and end times
            if ($data['starttime'] == $data['endtime']) {
                $errors['starttime'] = get_string('samemeettime', 'teleconnect');
                $errors['endtime'] = get_string('samemeettime', 'teleconnect');
            } elseif ($data['endtime'] < $data['starttime']) {
                $errors['starttime'] = get_string('greaterstarttime', 'teleconnect');
            }
        }

        tconnect_logout($tconnect);

        return $errors;
    }

    function get_templates() {
        $tconnect = tconnect_login();

        $templates_meetings = tconnect_get_templates_meetings($tconnect);
        tconnect_logout($tconnect);
        return $templates_meetings;
    }

}
