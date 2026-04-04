<?php
// This file replaces:
//   * STATEMENTS section in db/install.xml
//   * lib.php/modulename_install() post installation hook
//   * partially defaults.php

/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_teleconnect_install() {
    global $DB;

    // The commented out code is waiting for a fix for MDL-25709
    $result = true;
    $timenow = time();
    $sysctx = context_system::instance();
    $mrole = new stdClass();
    $levels = array(CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE);

    $param = array('shortname' => 'coursecreator');
    $coursecreator = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    if (empty($coursecreator)) {
        $param = array('archetype' => 'coursecreator');
        $coursecreator = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    }
    $coursecreatorrid = array_shift($coursecreator);

    $param = array('shortname' =>'editingteacher');
    $editingteacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    if (empty($editingteacher)) {
        $param = array('archetype' => 'editingteacher');
        $editingteacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    }
    $editingteacherrid = array_shift($editingteacher);

    $param = array('shortname' =>'teacher');
    $teacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    if (empty($teacher)) {
        $param = array('archetype' => 'teacher');
        $teacher = $DB->get_records('role', $param, 'id ASC', 'id', 0, 1);
    }
    $teacherrid = array_shift($teacher);

    // Fully setup the tele Connect Presenter role.
    $param = array('shortname' => 'teleconnectpresenter');
    if (!$mrole = $DB->get_record('role', $param)) {

        if ($rid = create_role(get_string('teleconnectpresenter', 'teleconnect'), 'teleconnectpresenter',
                               get_string('teleconnectpresenterdescription', 'teleconnect'), 'teleconnectpresenter')) {

            $mrole = new stdClass();
            $mrole->id = $rid;
            $result = $result && assign_capability('mod/teleconnect:meetingpresenter', CAP_ALLOW, $mrole->id, $sysctx->id);

            set_role_contextlevels($mrole->id, $levels);
        } else {
            $result = false;
        }
    }

    if (isset($coursecreatorrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($coursecreatorrid->id, $mrole->id);
        }
    }

    if (isset($editingteacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($editingteacherrid->id, $mrole->id);
        }
    }

    if (isset($teacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($teacherrid->id, $mrole->id);
        }
    }

    // Fully setup the tele Connect Participant role.
    $param = array('shortname' => 'teleconnectparticipant');

    if ($result && !($mrole = $DB->get_record('role', $param))) {

        if ($rid = create_role(get_string('teleconnectparticipant', 'teleconnect'), 'teleconnectparticipant',
                               get_string('teleconnectparticipantdescription', 'teleconnect'), 'teleconnectparticipant')) {

            $mrole = new stdClass();
            $mrole->id  = $rid;
            $result = $result && assign_capability('mod/teleconnect:meetingparticipant', CAP_ALLOW, $mrole->id, $sysctx->id);
            set_role_contextlevels($mrole->id, $levels);
        } else {
            $result = false;
        }
    }

    if (isset($coursecreatorrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($coursecreatorrid->id, $mrole->id);
        }
    }

    if (isset($editingteacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($editingteacherrid->id, $mrole->id);
        }
    }

    if (isset($teacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($teacherrid->id, $mrole->id);
        }
    }


    // Fully setup the tele Connect Host role.
    $param = array('shortname' => 'teleconnecthost');
    if ($result && !$mrole = $DB->get_record('role', $param)) {
        if ($rid = create_role(get_string('teleconnecthost', 'teleconnect'), 'teleconnecthost',
                               get_string('teleconnecthostdescription', 'teleconnect'), 'teleconnecthost')) {

            $mrole = new stdClass();
            $mrole->id  = $rid;
            $result = $result && assign_capability('mod/teleconnect:meetinghost', CAP_ALLOW, $mrole->id, $sysctx->id);
            set_role_contextlevels($mrole->id, $levels);
        } else {
            $result = false;
        }
    }

    if (isset($coursecreatorrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $coursecreatorrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($coursecreatorrid->id, $mrole->id);
        }
    }

    if (isset($editingteacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $editingteacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($editingteacherrid->id, $mrole->id);
        }
    }

    if (isset($teacherrid->id)) {
        $param = array('allowassign' => $mrole->id, 'roleid' => $teacherrid->id);
        if (!$DB->get_record('role_allow_assign', $param)) {
            allow_assign($teacherrid->id, $mrole->id);
        }
    }

    return $result;

}