<?php
/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_teleconnect_uninstall() {
    global $DB;

    $result = true;

    $param = array('shortname' => 'teleconnectparticipant');
    if ($mrole = $DB->get_record('role', $param)) {
        $result = $result && delete_role($mrole->id);
    }

    $param = array('shortname' => 'teleconnectpresenter');
    if ($mrole = $DB->get_record('role', $param)) {
        $result = $result && delete_role($mrole->id);
    }

    $param = array('shortname' => 'teleconnecthost');
    if ($mrole = $DB->get_record('role', $param)) {
        $result = $result && delete_role($mrole->id);
    }

    return $result;
}