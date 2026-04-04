<?php

/**
 * @package mod
 * @subpackage teleconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('tconnect_class.php');
require_once('tconnect_class_dom.php');
require_once($CFG->libdir.'/weblib.php');



define('tele_VIEW_ROLE', 'view');
define('tele_HOST_ROLE', 'host');
define('tele_MINIADMIN_ROLE', 'mini-host');
define('tele_REMOVE_ROLE', 'remove');

define('tele_PARTICIPANT', 1);
define('tele_PRESENTER', 2);
define('tele_REMOVE', 3);
define('tele_HOST', 4);

define('tele_TEMPLATE_POSTFIX', '- Template');
define('tele_MEETING_POSTFIX', '- Meeting');

define('tele_MEETPERM_PUBLIC', 0); //means the Acrobat Connect meeting is public, and anyone who has the URL for the meeting can enter the room.
define('tele_MEETPERM_PROTECTED', 1); //means the meeting is protected, and only registered users and accepted guests can enter the room.
define('tele_MEETPERM_PRIVATE', 2); // means the meeting is private, and only registered users and participants can enter the room

define('tele_TMZ_LENGTH', 6);

function tele_connection_test($host = '', $port = 80, $username = '',
                               $password = '', $httpheader = '',
                               $emaillogin, $https = false) {

    if (empty($host) or
        empty($port) or (0 == $port) or
        empty($username) or
        empty($password) or
        empty($httpheader)) {

        echo "</p>One of the required parameters is blank or incorrect: <br />".
             "Host: $host<br /> Port: $port<br /> Username: $username<br /> Password: $password".
             "<br /> HTTP Header: $httpheader</p>";

        die();
    }

    $messages = array();

    $tconnectDOM = new tconnect_class_dom($host,
                                         $port,
                                         $username,
                                         $password,
                                         '',
                                         $https);

    $params = array(
        'action' => 'common-info'
    );

    // Send common-info call to obtain the session key
    echo '<p>Sending common-info call:</p>';
    $tconnectDOM->create_request($params);

    if (!empty($tconnectDOM->_xmlresponse)) {

        // Get the session key from the XML response
        $tconnectDOM->read_cookie_xml($tconnectDOM->_xmlresponse);

        $cookie = $tconnectDOM->get_cookie();
        if (empty($cookie)) {

            echo '<p>unable to obtain session key from common-info call</p>';
            echo '<p>xmlrequest:</p>';
            $doc = new DOMDocument();

            if ($doc->loadXML($tconnectDOM->_xmlrequest)) {
                echo '<p>' . htmlspecialchars($doc->saveXML()) . '</p>';
            } else {
                echo '<p>unable to display the XML request</p>';
            }

            echo '<p>xmlresponse:</p>';
            $doc = new DOMDocument();

            if ($doc->loadXML($tconnectDOM->_xmlresponse)) {
                echo '<p>' . htmlspecialchars($doc->saveHTML()) . '</p>';
            } else {
                echo '<p>unable to display the XML response</p>';
            }

        } else {

            // print success
            echo '<p style="color:#006633">successfully obtained the session key: ' . $tconnectDOM->get_cookie() . '</p>';

            // test logging in as the administrator
            $params = array(
                  'action' => 'login',
                  'login' => $tconnectDOM->get_username(),
                  'password' => $tconnectDOM->get_password(),
            );

            $tconnectDOM->create_request($params);

            if ($tconnectDOM->call_success()) {
                echo '<p style="color:#006633">successfully logged in as admin user</p>';
                //$username

                //Test retrevial of folders
                echo '<p>Testing retrevial of shared content, recording and meeting folders:</p>';
                $folderscoid = tconnect_get_folder($tconnectDOM, 'content');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained shared content folder scoid: '. $folderscoid . '</p>';
                } else {

                    echo '<p>error obtaining shared content folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';

                }

                $folderscoid = tconnect_get_folder($tconnectDOM, 'forced-archives');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained forced-archives (meeting recordings) folder scoid: '. $folderscoid . '</p>';
                } else {

                    echo '<p>error obtaining forced-archives (meeting recordings) folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';

                }

                $folderscoid = tconnect_get_folder($tconnectDOM, 'meetings');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained meetings folder scoid: '. $folderscoid . '</p>';
                } else {

                    echo '<p>error obtaining meetings folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';

                }

                //Test creating a meeting
                $folderscoid = tconnect_get_folder($tconnectDOM, 'meetings');

                $meeting = new stdClass();
                $meeting->name = 'testmeetingtest';
                $time = time();
                $meeting->starttime = $time;
                $time = $time + (60 * 60);
                $meeting->endtime = $time;

                if (($meetingscoid = tconnect_create_meeting($tconnectDOM, $meeting, $folderscoid))) {
                    echo '<p style="color:#006633">successfully created meeting <b>testmeetingtest</b> scoid: '. $meetingscoid . '</p>';
                } else {

                    echo '<p>error creating meeting <b>testmeetingtest</b> folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';
                }

                //Test creating a user
                $user = new stdClass();
                $user->username = 'testusertest';
                $user->firstname = 'testusertest';
                $user->lastname = 'testusertest';
                $user->email = 'testusertest@test.com';

                if (!empty($emaillogin)) {
                    $user->username = $user->email;
                }

                $skipdeletetest = false;

                if (!($usrprincipal = tconnect_user_exists($tconnectDOM, $user))) {
                      $usrprincipal = tconnect_create_user($tconnectDOM, $user);
                    if ($usrprincipal) {
                        echo '<p style="color:#006633">successfully created user <b>testusertest</b> principal-id: '. $usrprincipal . '</p>';
                    } else {
                        echo '<p>error creating user  <b>testusertest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';

                        tconnect_logout($tconnectDOM);
                        die();
                    }
                } else {

                    echo '<p>user <b>testusertest</b> already exists skipping delete user test</p>';
                    $skipdeletetest = true;
                }

                //Test assigning a user a role to the meeting
                if (tconnect_check_user_perm($tconnectDOM, $usrprincipal, $meetingscoid, tele_PRESENTER, true)) {
                    echo '<p style="color:#006633">successfully assigned user <b>testusertest</b>'.
                         ' presenter role in meeting <b>testmeetingtest</b>: '. $usrprincipal . '</p>';
                } else {
                        echo '<p>error assigning user <b>testusertest</b> presenter role in meeting <b>testmeetingtest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';
                }

                //Test removing role from meeting
                if (tconnect_check_user_perm($tconnectDOM, $usrprincipal, $meetingscoid, tele_REMOVE_ROLE, true)) {
                    echo '<p style="color:#006633">successfully removed presenter role for user <b>testusertest</b>'.
                         ' in meeting <b>testmeetingtest</b>: '. $usrprincipal . '</p>';
                } else {
                        echo '<p>error remove presenter role for user <b>testusertest</b> in meeting <b>testmeetingtest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';
                }

                //Test removing user from server
                if (!$skipdeletetest) {
                    if (tconnect_delete_user($tconnectDOM, $usrprincipal)) {
                        echo '<p style="color:#006633">successfully removed user <b>testusertest</b> principal-id: '. $usrprincipal . '</p>';
                    } else {
                        echo '<p>error removing user <b>testusertest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';
                    }
                }

                //Test removing meeting from server
                if ($meetingscoid) {
                    if (tconnect_remove_meeting($tconnectDOM, $meetingscoid)) {
                        echo '<p style="color:#006633">successfully removed meeting <b>testmeetingtest</b> scoid: '. $meetingscoid . '</p>';
                    } else {
                        echo '<p>error removing meeting <b>testmeetingtest</b> folder</p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($tconnectDOM->_xmlrequest). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($tconnectDOM->_xmlresponse). '</p>';
                    }
                }


            } else {
                echo '<p style="color:#680000">logging in as '. $username . ' was not successful, check to see if the username and password are correct </p>';
            }

       }

    } else {
        echo '<p style="color:#680000">common-info API call returned an empty document.  Please check your settings and try again </p>';
    }

    tconnect_logout($tconnectDOM);

}

/**
 * Returns the folder sco-id
 * @param object an tele connection_class object
 * @param string $folder name of the folder to get
 * (ex. forced-archives = recording folder | meetings = meetings folder
 * | content = shared content folder)
 * @return mixed tele connect folder sco-id || false if there was an error
 *
 */
function tconnect_get_folder($tconnect, $folder = '') {
    $folderscoid = false;
    $params = array('action' => 'sco-shortcuts');
    return 1911296367;
    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $folderscoid = tconnect_get_folder_sco_id($tconnect->_xmlresponse, $folder);
//        $params = array('action' => 'sco-contents', 'sco-id' => $folderscoid);
    }

    return $folderscoid;
}

/**
 * TODO: comment function and return something meaningful
 */
function tconnect_get_folder_sco_id($xml, $folder) {
    $scoid = false;


    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('sco');

    if (!empty($domnodelist->length)) {

        for ($i = 0; $i < $domnodelist->length; $i++) {

            $domnode = $domnodelist->item($i)->attributes->getNamedItem('type');

            if (!is_null($domnode)) {

                if (0 == strcmp($folder, $domnode->nodeValue)) {
                    $domnode = $domnodelist->item($i)->attributes->getNamedItem('sco-id');

                    if (!is_null($domnode)) {
                        $scoid = (int) $domnode->nodeValue;

                    }
                }
            }
        }
    }

    /*if($folder=='meetings'){
        $folder='courses';
    }*/
    $scoid=1187441412;

    return $scoid;

}



/**
 * Log in as the admin user.  This should only be used to conduct API calls.
 */
function tconnect_login() {
    global $CFG, $USER, $COURSE;

    if (!isset($CFG->teleconnect_host) or
        !isset($CFG->teleconnect_admin_login) or
        !isset($CFG->teleconnect_admin_password)) {
            if (is_siteadmin($USER->id)) {
                notice(get_string('adminnotsetupproperty', 'teleconnect'),
                       $CFG->wwwroot . '/admin/settings.php?section=modsettingteleconnect');
            } else {
                notice(get_string('notsetupproperty', 'teleconnect'),
                       '', $COURSE);
            }
    }

    if (isset($CFG->teleconnect_port) and
        !empty($CFG->teleconnect_port) and
        ((80 != $CFG->teleconnect_port) and (0 != $CFG->teleconnect_port))) {
        $port = $CFG->teleconnect_port;
    } else {
        $port = 80;
    }

    $https = false;

    if (isset($CFG->teleconnect_https) and (!empty($CFG->teleconnect_https))) {
        $https = true;
    }


    $tconnect = new tconnect_class_dom($CFG->teleconnect_host,
                                      $CFG->teleconnect_port,
                                      $CFG->teleconnect_admin_login,
                                      $CFG->teleconnect_admin_password,
                                      '',
                                      $https);

    $params = array(
        'action' => 'common-info'
    );

    $tconnect->create_request($params);

    $tconnect->read_cookie_xml($tconnect->_xmlresponse);

    $params = array(
          'action' => 'login',
          'login' => $tconnect->get_username(),
          'password' => $tconnect->get_password(),
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $tconnect->set_connection(1);
    } else {
        $tconnect->set_connection(0);
    }

    return $tconnect;
}


/**
 * Logout
 * @param object $tconnect - connection object
 * @return true on success else false
 */
function tconnect_logout(&$tconnect) {
    if (!$tconnect->get_connection()) {
        return true;
    }

    $params = array('action' => 'logout');
    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $tconnect->set_connection(0);
        return true;
    } else {
        $tconnect->set_connection(1);
        return false;
    }
}

/**
 * Calls all operations needed to retrieve and return all
 * templates defined in the shared templates folder and meetings
 * @param object $tconnect connection object
 * @return array $templates an array of templates
 */
function tconnect_get_templates_meetings($tconnect) {
    $templates = array();
    $meetings = array();
    $meetfldscoid = false;
    $tempfldscoid = false;

    $params = array(
        'action' => 'sco-shortcuts',
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        // Get shared templates folder sco-id
        $tempfldscoid = tconnect_get_shared_templates($tconnect->_xmlresponse);
    }

    if (false !== $tempfldscoid) {
        $params = array(
            'action' => 'sco-expanded-contents',
            'sco-id' => $tempfldscoid,
        );

        $tconnect->create_request($params);

        if ($tconnect->call_success()) {
            $templates = tconnect_return_all_templates($tconnect->_xmlresponse);
        }
    }

//    if (false !== $meetfldscoid) {
//        $params = array(
//            'action' => 'sco-expanded-contents',
//            'sco-id' => $meetfldscoid,
//            'filter-type' => 'meeting',
//        );
//
//        $tconnect->create_request($params);
//
//        if ($tconnect->call_success()) {
//            $meetings = tconnect_return_all_meetings($tconnect->_xmlresponse);
//        }
//
//    }

    return $templates + $meetings;
}

/**
 * Parse XML looking for shared-meeting-templates attribute
 * and returning the sco-id of the folder
 * @param string $xml returned XML from a sco-shortcuts call
 * @return mixed sco-id if found or false if not found or error
 */
function tconnect_get_shared_templates($xml) {
    $scoid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('shortcuts');

    if (!empty($domnodelist->length)) {

//        for ($i = 0; $i < $domnodelist->length; $i++) {

            $innerlist = $domnodelist->item(0)->getElementsByTagName('sco');

            if (!empty($innerlist->length)) {

                for ($x = 0; $x < $innerlist->length; $x++) {

                    if ($innerlist->item($x)->hasAttributes()) {

                        $domnode = $innerlist->item($x)->attributes->getNamedItem('type');

                        if (!is_null($domnode)) {

                            if (0 == strcmp('shared-meeting-templates', $domnode->nodeValue)) {
                                $domnode = $innerlist->item($x)->attributes->getNamedItem('sco-id');

                                if (!is_null($domnode)) {
                                    $scoid = (int) $domnode->nodeValue;
                                }
                            }
                        }
                    }
                }
            }
//        }

    }

    return $scoid;
}

function tconnect_return_all_meetings($xml) {
    $meetings = array();
    $xml = new SimpleXMLElement($xml);

    if (empty($xml)) {
        return $meetings;
    }

    foreach($xml->{'expanded-scos'}[0]->sco as $key => $sco) {
        if (0 == strcmp('meeting', $sco['type'])) {
            $mkey = (int) $sco['sco-id'];
            $meetings[$mkey] = (string) current($sco->name) .' '. tele_MEETING_POSTFIX;
        }
    }

    return $meetings;
}

/**
 * Parses XML for meeting templates and returns an array
 * with sco-id as the key and template name as the value
 * @param strimg $xml XML returned from a sco-expanded-contents call
 * @return array of templates sco-id -> key, name -> value
 */
function tconnect_return_all_templates($xml) {
    $templates = array();

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('expanded-scos');

    if (!empty($domnodelist->length)) {

        $innerlist = $domnodelist->item(0)->getElementsByTagName('sco');

        if (!empty($innerlist->length)) {

            for ($i = 0; $i < $innerlist->length; $i++) {

                if ($innerlist->item($i)->hasAttributes()) {
                    $domnode = $innerlist->item($i)->attributes->getNamedItem('type');

                    if (!is_null($domnode) and 0 == strcmp('meeting', $domnode->nodeValue)) {
                        $domnode = $innerlist->item($i)->attributes->getNamedItem('sco-id');

                        if (!is_null($domnode)) {
                            $tkey = (int) $domnode->nodeValue;
                            $namelistnode = $innerlist->item($i)->getElementsByTagName('name');

                            if (!is_null($namelistnode)) {
                                $name = $namelistnode->item(0)->nodeValue;
                                $templates[$tkey] = (string) $name .' ' . tele_TEMPLATE_POSTFIX;
                            }
                        }
                    }
                }
            }
        }
    }

    return $templates;
}

/**
 * Returns information about all recordings that belong to a specific
 * meeting sco-id
 *
 * @param obj $tconnect a tconnect_class object
 * @param int $folderscoid the recordings folder sco-id
 * @param int $sourcescoid the meeting sco-id
 *
 * @return mixed array an array of object with the recording sco-id
 * as the key and the recording properties as properties
 */
function tconnect_get_recordings($tconnect, $folderscoid, $sourcescoid) {
    $params = array('action' => 'sco-contents',
                    'sco-id' => $folderscoid,
                    //'filter-source-sco-id' => $sourcescoid,
                    'sort-name' => 'asc',
                    );

    // Check if meeting scoid and folder scoid are the same
    // If hey are the same then that means that forced recordings is not
    // enabled filter-source-sco-id should not be included.  If the
    // meeting scoid and folder scoid are not equal then forced recordings
    // are enabled and we can use filter by filter-source-sco-id
    // Thanks to A. gtdino
    if ($sourcescoid != $folderscoid) {
        $params['filter-source-sco-id'] = $sourcescoid;
    }

    $tconnect->create_request($params);

    $recordings = array();

    if ($tconnect->call_success()) {
        $dom = new DomDocument();
        $dom->loadXML($tconnect->_xmlresponse);

        $domnodelist = $dom->getElementsByTagName('scos');

        if (!empty($domnodelist->length)) {

//            for ($i = 0; $i < $domnodelist->length; $i++) {

                $innernodelist = $domnodelist->item(0)->getElementsByTagName('sco');

                if (!empty($innernodelist->length)) {

                    for ($x = 0; $x < $innernodelist->length; $x++) {

                        if ($innernodelist->item($x)->hasAttributes()) {

                            $domnode = $innernodelist->item($x)->attributes->getNamedItem('sco-id');

                            if (!is_null($domnode)) {
                                $meetingdetail = $innernodelist->item($x);

                                // Check if the SCO item is a recording or uploaded document.  We only want to display recordings
                                // if (!is_null($meetingdetail->getElementsByTagName('duration')->item(0)))
                                if (!is_null($meetingdetail->getElementsByTagName('duration')->item(0))) {

                                    $j = (int) $domnode->nodeValue;
                                    $value = (!is_null($meetingdetail->getElementsByTagName('name'))) ?
                                             $meetingdetail->getElementsByTagName('name')->item(0)->nodeValue : '';

                                    $recordings[$j]->name = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('url-path'))) ?
                                             $meetingdetail->getElementsByTagName('url-path')->item(0)->nodeValue : '';

                                    $recordings[$j]->url = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('date-begin'))) ?
                                             $meetingdetail->getElementsByTagName('date-begin')->item(0)->nodeValue : '';

                                    $recordings[$j]->startdate = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('date-end'))) ?
                                             $meetingdetail->getElementsByTagName('date-end')->item(0)->nodeValue : '';

                                    $recordings[$j]->enddate = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('date-created'))) ?
                                             $meetingdetail->getElementsByTagName('date-created')->item(0)->nodeValue : '';

                                    $recordings[$j]->createdate = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('date-modified'))) ?
                                             $meetingdetail->getElementsByTagName('date-modified')->item(0)->nodeValue : '';

                                    $recordings[$j]->modified = (string) $value;

                                    $value = (!is_null($meetingdetail->getElementsByTagName('duration'))) ?
                                             $meetingdetail->getElementsByTagName('duration')->item(0)->nodeValue : '';

                                    $recordings[$j]->duration = (string) $value;

                                    $recordings[$j]->sourcesco = (int) $sourcescoid;
                                }

                            }
                        }
                    }
                }
//            }

            return $recordings;
        } else {
            return false;
        }
    } else {
        return false;
    }

}




/**
 * Parses XML and returns the meeting sco-id
 * @param string XML obtained from a sco-update call
 */
function tconnect_get_meeting_scoid($xml) {
    $scoid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('sco');

    if (!empty($domnodelist->length)) {
        if ($domnodelist->item(0)->hasAttributes()) {
            $domnode = $domnodelist->item(0)->attributes->getNamedItem('sco-id');

            if (!is_null($domnode)) {
                $scoid = (int) $domnode->nodeValue;
            }
        }
    }

    return $scoid;
}

/**
 * Update meeting
 * @param obj $tconnect tconnect_class object
 * @param obj $meetingobj an teleconnect module object
 * @param int $meetingfdl tele connect meeting folder sco-id
 * @return bool true if call was successful else false
 */
function tconnect_update_meeting($tconnect, $meetingobj, $meetingfdl) {
    $params = array('action' => 'sco-update',
                    'sco-id' => $meetingobj->scoid,
                    'name' => htmlentities($meetingobj->name, ENT_COMPAT, 'UTF-8'),
                    'folder-id' => $meetingfdl,
// updating meeting URL using the API corrupts the meeting for some reason
//                    'url-path' => '/'.$meetingobj->meeturl,
                    'date-begin' => $meetingobj->starttime,
                    'date-end' => $meetingobj->endtime,
                    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }

}

/**
 * Update a meeting's access permissions
 * @param obj $tconnect tconnect_class object
 * @param int $meetingscoid meeting sco-id
 * @param int $perm meeting permission id
 * @return bool true if call was successful else false
 */
function tconnect_update_meeting_perm($tconnect, $meetingscoid, $perm) {
     $params = array('action' => 'permissions-update',
                     'acl-id' => $meetingscoid,
                     'principal-id' => 'public-access',
                    );

     switch ($perm) {
         case tele_MEETPERM_PUBLIC:
            $params['permission-id'] = 'view-hidden';
            break;
         case tele_MEETPERM_PROTECTED:
            $params['permission-id'] = 'remove';
            break;
         case tele_MEETPERM_PRIVATE:
         default:
            $params['permission-id'] = 'denied';
            break;
     }

     $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }


 }
/**
 * Update a meeting makehost
 * @param obj $tconnect tconnect_class object
 * @param int $meetingscoid meeting sco-id
 * @param int $perm meeting permission id
 * @return bool true if call was successful else false
 */
function tconnect_update_meeting_perm_makehost($tconnect, $meetingscoid, $perm) {
     global $CFG;
     $params = array('action' => 'permissions-update',
                     'acl-id' => $meetingscoid,
                     'principal-id' => $CFG->adobe_host_principal_id,
		     'permission-id' =>'host'
                    );

     $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }


 }
/**
 * Update a meeting makeparticipants
 * @param obj $tconnect tconnect_class object
 * @param int $meetingscoid meeting sco-id
 * @param int $perm meeting permission id
 * @return bool true if call was successful else false
 */
function tconnect_update_meeting_perm_makeparticipants($tconnect, $meetingscoid, $perm) {
     global $CFG;
     $params = array('action' => 'permissions-update',
                     'acl-id' => $meetingscoid,
                     'principal-id' => $CFG->adobe_participants_principal_id,
		     'permission-id' =>'view'
                    );

     $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }


 }

/** CONTRIB-1976, CONTRIB-1992
 * This function adds a fraction of a second to the ISO 8601 date
 * @param int $time unix timestamp
 * @return mixed a string (ISO 8601) containing the decimal fraction of a second
 * or false if it was not able to determine where to put it
 */
function tconnect_format_date_seconds($time) {

    $newdate = false;
    $date = date("c", $time);

    $pos = strrpos($date, '-');
    $length = strlen($date);

    $diff = $length - $pos;

    if ((0 < $diff) and (tele_TMZ_LENGTH == $diff)) {
        $firstpart = substr($date, 0, $pos);
        $lastpart = substr($date, $pos);
        $newdate = $firstpart . '.000' . $lastpart;

        return $newdate;
    }

    $pos = strrpos($date, '+');
    $length = strlen($date);

    $diff = $length - $pos;

    if ((0 < $diff) and (tele_TMZ_LENGTH == $diff)) {
        $firstpart = substr($date, 0, $pos);
        $lastpart = substr($date, $pos);
        $newdate = $firstpart . '.000' . $lastpart;

        return $newdate;

    }

    return false;
}

/**
 * Creates a meeting
 * @param obj $tconnect tconnect_class object
 * @param obj $meetingobj an teleconnect module object
 * @param int $meetingfdl tele connect meeting folder sco-id
 * @return mixed meeting sco-id on success || false on error
 */
function tconnect_create_meeting($tconnect, $meetingobj, $meetingfdl) {
    //date("Y-m-d\TH:i
    global $CFG;
    $starttime = tconnect_format_date_seconds($meetingobj->starttime);
    $endtime = tconnect_format_date_seconds($meetingobj->endtime);


    if (empty($starttime) or empty($endtime)) {
        $message = 'Failure (tconnect_find_timezone) in finding the +/- sign in the date timezone'.
                    "\n".date("c", $meetingobj->starttime)."\n".date("c", $meetingobj->endtime);
        debugging($message, DEBUG_DEVELOPER);
        return false;
    }

    $params = array('action' => 'sco-update',
                    'type' => 'meeting',
                   /* 'icon'=>'virtual-classroom',*/
                    'name' => htmlentities($meetingobj->name, ENT_COMPAT, 'UTF-8'),
                    'folder-id' => $meetingfdl,
                    'date-begin' => $starttime,
                    'date-end' => $endtime
                    );

    if (!empty($meetingobj->meeturl)) {
        $params['url-path'] = $meetingobj->meeturl;
    }

    if (!empty($meetingobj->templatescoid)) {
      //  $params['source-sco-id'] = $meetingobj->templatescoid;
    }

    $tconnect->create_request($params);


    if ($tconnect->call_success()) {
        $meeting_scoid=tconnect_get_meeting_scoid($tconnect->_xmlresponse);

        if(strcmp(htmlentities($meetingobj->name, ENT_COMPAT, 'UTF-8'),'testmeetingtest')==0){
            return $meeting_scoid;
        }
        $seminarid=$meeting_scoid ;
        $sessionname=htmlentities($meetingobj->name, ENT_COMPAT, 'UTF-8');
        $quota=1000;

        $sparams1 = array('action' => 'sco-session-seminar-list',
            'sco-id' => $meetingfdl,
        );

        $tconnect->create_request($sparams1);

        if ($tconnect->call_success()) {
            $result=$tconnect->_xmlresponse;
            $simple = $result;
            $p = xml_parser_create();
            xml_parse_into_struct($p, $simple, $vals, $index);
            xml_parser_free($p);

            for($i=0;$i<count($index['SCO-ID']);$i++){

                if($vals[$index['SCO-ID'][$i]]['value']==$seminarid){

                    $sessionname=$vals[$index['NAME'][$i]]['value'];
                    $quota=$vals[$index['QUOTA'][$i]]['value'];
                    $starttime=$vals[$index['DATE-BEGIN'][$i]]['value'];
                    $endtime=$vals[$index['DATE-END'][$i]]['value'];

                }
            }

        }


        $sessionname=$sessionname;
        $folderid=$seminarid;


        $surl=$CFG->teleconnect_host.'?action=sco-update&folder-id='.$folderid.'&type=seminarsession&name='.$sessionname.'&session='.$tconnect->get_cookie();

        $sxmlstr = file_get_contents($surl, FILE_USE_INCLUDE_PATH);

        if($sxmlstr) {//if sco-id is generated for current session start

            $sxml = new SimpleXMLElement($sxmlstr);
            //print_r($sxml);
            foreach ($sxml->sco as $sco) {
                foreach ($sco->attributes() as $key => $val) {
                    if ($key == 'sco-id') {
                        $scoid = $val;
                    }
                }
            }

            $starttime=str_replace("+", "%2b", $starttime);
            $starttime=str_replace(":", "%3A", $starttime);
            $endtime=str_replace("+", "%2b", $endtime);
            $endtime=str_replace(":", "%3A", $endtime);

            $ssurl = $CFG->teleconnect_host.'?action=seminar-session-sco-update&sco-id=' . $scoid . '&source-sco-id=' . $folderid . '&parent-acl-id=' . $folderid . '&name=' . $sessionname . '&date-begin='.$starttime.'&date-end='.$endtime.'&session=' . $tconnect->get_cookie();
            $ssxmlstr = file_get_contents($ssurl, FILE_USE_INCLUDE_PATH);
            $ssxml = new SimpleXMLElement($ssxmlstr);

            foreach ($ssxml->status as $status) {
                foreach ($status->attributes() as $key => $val) {
                    if ($key == 'code') {
                        $code = $val;
                    }
                }
            }
            if(strcmp($code,'invalid')==0){
                    var_dump($code);
                $sdurl = $CFG->teleconnect_host.'?action=sco-delete&sco-id='.$meeting_scoid. '&session=' . $tconnect->get_cookie() ;
                $sdxmlstr = file_get_contents($sdurl, FILE_USE_INCLUDE_PATH);
                debugging('Session Schedule Conflict', DEBUG_DEVELOPER);
                //return false;
                notice ('Session Schedule Conflict', $CFG->wwwroot, $course=null);
            }
            else{

                $sssurl = $CFG->teleconnect_host.'?action=acl-field-update&acl-id=' . $scoid . '&field-id=311&value=' . $quota . '&session=' . $tconnect->get_cookie();
                $sssxmlstr = file_get_contents($sssurl, false);
               // print_r("<br/>");
               // var_dump($sssxmlstr);
                foreach ($sssxmlstr->status as $sstatus) {
                    foreach ($sstatus->attributes() as $key => $val) {
                        if ($key == 'code') {
                            $scode = $val;
                        }
                    }
                }
                if(strcmp($scode,'invalid')==0){
                    var_dump($scode);
                    $sdurl = $CFG->teleconnect_host.'?action=sco-delete&sco-id='.$meeting_scoid. '&session=' . $tconnect->get_cookie() ;
                    $sdxmlstr = file_get_contents($sdurl, FILE_USE_INCLUDE_PATH);
                    debugging('Session Schedule Conflict', DEBUG_DEVELOPER);
                    //return false;
                    notice ('Session Schedule Conflict', $CFG->wwwroot, $course=null);
                }
                else{
                    return $meeting_scoid;
                }
            }

        }////if sco-id is generated for current session end
        else{
            //http://meet95317649.teleconnect.com/api/xml?action=sco-delete&sco-id=1112166094
            $sdurl = $CFG->teleconnect_host.'?action=sco-delete&sco-id='.$meeting_scoid. '&session=' . $tconnect->get_cookie() ;
            $sdxmlstr = file_get_contents($sdurl, FILE_USE_INCLUDE_PATH);
            debugging('Session Schedule Conflict', DEBUG_DEVELOPER);
            return false;
        }

       // return $meeting_scoid;
    } else {

        return false;
    }
}


//get session sco-id from sco-update response
    function tconnect_get_session_scoid($xml) {
        $scoid = false;

        $dom = new DomDocument();
        $dom->loadXML($xml);

        $domnodelist = $dom->getElementsByTagName('sco');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('sco-id');

                if (!is_null($domnode)) {
                    $scoid = (int) $domnode->nodeValue;
                }
            }
        }

        return $scoid;
    }
/**
 * Finds a matching meeting sco-id
 * @param object $tconnect a tconnect_class object
 * @param int $meetfldscoid Meeting folder sco-id
 * @param array $filter array key is the filter and array value is the value
 * (ex. array('filter-name' => 'meeting101'))
 * @return mixed array of objects with sco-id as key and meeting name and url as object
 * properties as value || false if not found or error occured
 */
function tconnect_meeting_exists($tconnect, $meetfldscoid, $filter = array()) {
    $matches = array();

    $params = array(
        'action' => 'sco-contents',
        'sco-id' => $meetfldscoid,
        'filter-type' => 'meeting',
    );

    if (empty($filter)) {
        return false;
    }

    $params = array_merge($params, $filter);
    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $dom = new DomDocument();
        $dom->loadXML($tconnect->_xmlresponse);

        $domnodelist = $dom->getElementsByTagName('scos');

        if (!empty($domnodelist->length)) {

            $innernodelist = $domnodelist->item(0)->getElementsByTagName('sco');

            if (!empty($innernodelist->length)) {

                for ($i = 0; $i < $innernodelist->length; $i++) {

                    if ($innernodelist->item($i)->hasAttributes()) {

                        $domnode = $innernodelist->item($i)->attributes->getNamedItem('sco-id');

                        if (!is_null($domnode)) {

                            $key = (int) $domnode->nodeValue;

                            $meetingdetail = $innernodelist->item($i);

                            $value = (!is_null($meetingdetail->getElementsByTagName('name'))) ?
                                     $meetingdetail->getElementsByTagName('name')->item(0)->nodeValue : '';

                            if (!isset($matches[$key])) {
                                $matches[$key] = new stdClass();
                            }

                            $matches[$key]->name = (string) $value;

                            $value = (!is_null($meetingdetail->getElementsByTagName('url-path'))) ?
                                     $meetingdetail->getElementsByTagName('url-path')->item(0)->nodeValue : '';

                            $matches[$key]->url = (string) $value;

                            $matches[$key]->scoid = (int) $key;

                            $value = (!is_null($meetingdetail->getElementsByTagName('date-begin'))) ?
                                     $meetingdetail->getElementsByTagName('date-begin')->item(0)->nodeValue : '';

                            $matches[$key]->starttime = (string) $value;

                            $value = (!is_null($meetingdetail->getElementsByTagName('date-end'))) ?
                                     $meetingdetail->getElementsByTagName('date-end')->item(0)->nodeValue : '';

                            $matches[$key]->endtime = (string) $value;

                        }

                    }
                }
            }
        } else {
            return false;
        }

    } else {
        return false;
    }

    return $matches;
}

/**
 * Parse XML and returns the user's principal-id
 * @param string $xml XML returned from call to principal-list
 * @param mixed user's principal-id or false
 */
function tconnect_get_user_principal_id($xml) {
    $usrprincipalid = false;

    //var_dump($xml);

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('principal-list');

    if (!empty($domnodelist->length)) {
        $domnodelist = $domnodelist->item(0)->getElementsByTagName('principal');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('principal-id');

                if (!is_null($domnode)) {
                    $usrprincipalid = (int) $domnode->nodeValue;
                }
            }
        }
    }

    return $usrprincipalid;
}

/**
 * Check to see if a user exists on the tele connect server
 * searching by username
 * @param object $tconnect a connection_class object
 * @param object $userdata an object with username as a property
 * @return mixed user's principal-id of match is found || false if not
 * found or error occured
 */
function tconnect_user_exists($tconnect, $usrdata) {
    $params = array(
        'action' => 'principal-list',
        'filter-login' => $usrdata->username,
//            'filter-type' => 'meeting',
// add more filters if this process begins to get slow
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {

        return tconnect_get_user_principal_id($tconnect->_xmlresponse);
    } else {
        return false;
    }


}

function tconnect_delete_user($tconnect, $principalid = 0) {

    if (empty($principalid)) {
        return false;
    }

    $params = array(
        'action' => 'principals-delete',
        'principal-id' => $principalid,
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }

}

/**
 * Creates a new user on the tele Connect server.
 * Parses XML from a principal-update call and returns
 * the principal-id of the new user.
 *
 * @param object $aconnet a tconnect_class object
 * @param object $usrdata an object with firstname,lastname,
 * username and email properties.
 * @return mixed principal-id of the new user or false
 */
function tconnect_create_user($tconnect, $usrdata) {
    $principal_id = false;

    $params = array(
        'action' => 'principal-update',
        'first-name' => $usrdata->firstname,
        'last-name' => $usrdata->lastname,
        'login' => $usrdata->username,
        'password' => strtoupper(md5($usrdata->username . time())),
        'extlogin' => $usrdata->username,
        'type' => 'user',
        'send-email' => 'false',
        'has-children' => 0,
        'email' => $usrdata->email,
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $dom = new DomDocument();
        $dom->loadXML($tconnect->_xmlresponse);

        $domnodelist = $dom->getElementsByTagName('principal');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('principal-id');

                if (!is_null($domnode)) {
                    $principal_id = (int) $domnode->nodeValue;
                }
            }
        }
    }

    return $principal_id;
}

function tconnect_assign_user_perm($tconnect, $usrprincipal, $meetingscoid, $type) {
    $params = array(
        'action' => 'permissions-update',
        'acl-id' => $meetingscoid, //sco-id of meeting || principal id of user 11209,
        'permission-id' => $type, //  host, mini-host, view
        'principal-id' => $usrprincipal, // principal id of user you are looking at
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
          return true;
//        print_object($tconnect->_xmlresponse);
    } else {
          return false;
//        print_object($tconnect->_xmlresponse);
    }
}

function tconnect_remove_user_perm($tconnect, $usrprincipal, $meetingscoid) {
    $params = array(
        'action' => 'permissions-update',
        'acl-id' => $meetingscoid, //sco-id of meeting || principal id of user 11209,
        'permission-id' => tele_REMOVE_ROLE, //  host, mini-host, view
        'principal-id' => $usrprincipal, // principal id of user you are looking at
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
//        print_object($tconnect->_xmlresponse);
    } else {
//        print_object($tconnect->_xmlresponse);
    }

}


/**
 * Check if a user has a permission
 * @param object $tconnect a tconnect_class object
 * @param int $usrprincipal user principal-id
 * @param int $meetingscoid meeting sco-id
 * @param int $roletype can be tele_PRESENTER, tele_PARTICIPANT or tele_REMOVE
 * @param bool $assign set to true if you want to assign the user the role type
 * set to false to just check the user's permission.  $assign parameter is ignored
 * if $roletype is tele_REMOVE
 * @return TODO
 *
 */
function tconnect_check_user_perm($tconnect, $usrprincipal, $meetingscoid, $roletype, $assign = false) {
    $perm_type = '';
    $hasperm = false;

    switch ($roletype) {
        case tele_PRESENTER:
            $perm_type = tele_MINIADMIN_ROLE;
            break;
        case tele_PARTICIPANT:
            $perm_type = tele_VIEW_ROLE;
            break;
        case tele_HOST:
            $perm_type = tele_HOST_ROLE;
            break;
        case tele_REMOVE:
            $perm_type = tele_REMOVE_ROLE;
            break;
        default:
            break;
    }

    $params = array(
        'action' => 'permissions-info',
    //  'filter-permission-id' => 'mini-host',
        'acl-id' => $meetingscoid, //sco-id of meeting || principal id of user 11209,
//        'filter-permission-id' => $perm_type, //  host, mini-host, view
        'filter-principal-id' => $usrprincipal, // principal id of user you are looking at
    );

    if (tele_REMOVE_ROLE != $perm_type) {
        $params['filter-permission-id'] = $perm_type;
    }

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        $dom = new DomDocument();
        $dom->loadXML($tconnect->_xmlresponse);

        $domnodelist = $dom->getElementsByTagName('permissions');

        if (!empty($domnodelist->length)) {
            $domnodelist = $domnodelist->item(0)->getElementsByTagName('principal');

            if (!empty($domnodelist->length)) {
                $hasperm = true;
            }
        }

        if (tele_REMOVE_ROLE != $perm_type and $assign and !$hasperm) {
            // TODO: check return values of the two functions below
            // Assign permission to user
            return tconnect_assign_user_perm($tconnect, $usrprincipal, $meetingscoid, $perm_type);
        } elseif (tele_REMOVE_ROLE == $perm_type) {
            // Remove user's permission
            return tconnect_remove_user_perm($tconnect, $usrprincipal, $meetingscoid);
        } else {
            return $hasperm;
        }
    }
}

/**
 * Remove a meeting
 * @param obj $tconnect tele connection object
 * @param int $scoid sco-id of the meeting
 * @return bool true of success false on failure
 */
function tconnect_remove_meeting($tconnect, $scoid) {
    $params = array(
        'action' => 'sco-delete',
        'sco-id' => $scoid,
    );

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {
        return true;
    } else {
        return false;
    }
}

/**
 * Move SCOs to the shared content folder
 * @param obj $tconnect a tconnect_class object
 * @param array sco-ids as array keys
 * @return bool false if error or nothing to move true if a move occured
 */
function tconnect_move_to_shared($tconnect, $scolist) {
    // Get shared folder sco-id
    $shscoid = tconnect_get_folder($tconnect, 'content');

    // Iterate through list of sco and move them all to the shared folder
    if (!empty($shscoid)) {

        foreach ($scolist as $scoid => $data) {
            $params = array(
                'action' => 'sco-move',
                'folder-id' => $shscoid,
                'sco-id' => $scoid,
            );

            $tconnect->create_request($params);

        }

        return true;
    } else {
        return false;
    }
}

/**
 * Gets a list of roles that this user can assign in this context
 *
 * @param object $context the context.
 * @param int $rolenamedisplay the type of role name to display. One of the
 *      ROLENAME_X constants. Default ROLENAME_ALIAS.
 * @param bool $withusercounts if true, count the number of users with each role.
 * @param integer|object $user A user id or object. By default (null) checks the permissions of the current user.
 * @return array if $withusercounts is false, then an array $roleid => $rolename.
 *      if $withusercounts is true, returns a list of three arrays,
 *      $rolenames, $rolecounts, and $nameswithcounts.
 */
function teleconnect_get_assignable_roles($context, $rolenamedisplay = ROLENAME_ALIAS, $withusercounts = false, $user = null) {
    global $USER, $DB;

    // make sure there is a real user specified
    if ($user === null) {
        $userid = !empty($USER->id) ? $USER->id : 0;
    } else {
        $userid = !empty($user->id) ? $user->id : $user;
    }

    if (!has_capability('moodle/role:assign', $context, $userid)) {
        if ($withusercounts) {
            return array(array(), array(), array());
        } else {
            return array();
        }
    }

    $parents = $context->get_parent_context_ids(true);
    $contexts = implode(',' , $parents);

    $params = array();
    $extrafields = '';
    if ($rolenamedisplay == ROLENAME_ORIGINALANDSHORT or $rolenamedisplay == ROLENAME_SHORT) {
        $extrafields .= ', r.shortname';
    }

    if ($withusercounts) {
        $extrafields = ', (SELECT count(u.id)
                             FROM {role_assignments} cra JOIN {user} u ON cra.userid = u.id
                            WHERE cra.roleid = r.id AND cra.contextid = :conid AND u.deleted = 0
                          ) AS usercount';
        $params['conid'] = $context->id;
    }

    if (is_siteadmin($userid)) {
        // show all roles allowed in this context to admins
        $assignrestriction = "";
    } else {
        $assignrestriction = "JOIN (SELECT DISTINCT raa.allowassign AS id
                                      FROM {role_allow_assign} raa
                                      JOIN {role_assignments} ra ON ra.roleid = raa.roleid
                                     WHERE ra.userid = :userid AND ra.contextid IN ($contexts)
                                   ) ar ON ar.id = r.id";
        $params['userid'] = $userid;
    }
    $params['contextlevel'] = $context->contextlevel;
    $sql = "SELECT r.id, r.name $extrafields
              FROM {role} r
              $assignrestriction
              JOIN {role_context_levels} rcl ON r.id = rcl.roleid
             WHERE rcl.contextlevel = :contextlevel
          ORDER BY r.sortorder ASC";
    $roles = $DB->get_records_sql($sql, $params);

    // Only include tele Connect roles
    $param = array('shortname' => 'teleconnectpresenter');
    $presenterid    = $DB->get_field('role', 'id', $param);

    $param = array('shortname' => 'teleconnectparticipant');
    $participantid  = $DB->get_field('role', 'id', $param);

    $param = array('shortname' => 'teleconnecthost');
    $hostid         = $DB->get_field('role', 'id', $param);

    foreach ($roles as $key => $data) {
        if ($key != $participantid and $key != $presenterid and $key != $hostid) {
            unset($roles[$key]);
        }
    }

    $rolenames = array();
    foreach ($roles as $role) {
        if ($rolenamedisplay == ROLENAME_SHORT) {
            $rolenames[$role->id] = $role->shortname;
            continue;
        }
        $rolenames[$role->id] = $role->name;
        if ($rolenamedisplay == ROLENAME_ORIGINALANDSHORT) {
            $rolenames[$role->id] .= ' (' . $role->shortname . ')';
        }
    }
    if ($rolenamedisplay != ROLENAME_ORIGINALANDSHORT and $rolenamedisplay != ROLENAME_SHORT) {
        $rolenames = role_fix_names($rolenames, $context, $rolenamedisplay);
    }

    if (!$withusercounts) {
        return $rolenames;
    }

    $rolecounts = array();
    $nameswithcounts = array();
    foreach ($roles as $role) {
        $nameswithcounts[$role->id] = $rolenames[$role->id] . ' (' . $roles[$role->id]->usercount . ')';
        $rolecounts[$role->id] = $roles[$role->id]->usercount;
    }
    return array($rolenames, $rolecounts, $nameswithcounts);
}

/**
 * This function accepts a username and an email and returns the user's
 * tele connect user name, depending on the module's configuration settings
 *
 * @param string - moodle username
 * @param string - moodle email
 *
 * @return string - user's tele connect user name
 */
function set_tusername($username, $email) {
    global $CFG;

    if (isset($CFG->teleconnect_email_login) and !empty($CFG->teleconnect_email_login)) {
        return $email;
    } else {
        return $username;
    }
}

/**
 * This function search through the user-meetings folder for a folder named
 * after the user's login name and returns the sco-id of the user's folder
 *
 * @param obj - tele connection connection object
 * @param string - the name of the user's folder
 * @return mixed - sco-id of the user folder (int) or false if no folder exists
 *
 */
function tconnect_get_user_folder_sco_id($tconnect, $folder_name) {

    $scoid   = false;
    $usr_meet_scoid = tconnect_get_folder($tconnect, 'user-meetings');

    if (empty($usr_meet_scoid)) {
        return $scoid;
    }

    $params = array('action' => 'sco-expanded-contents',
                    'sco-id' => $usr_meet_scoid,
                    'filter-name' => $folder_name);

    $tconnect->create_request($params);

    if ($tconnect->call_success()) {

        $dom = new DomDocument();
        $dom->loadXML($tconnect->_xmlresponse);

        $domnodelist = $dom->getElementsByTagName('sco');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('sco-id');

                if (!is_null($domnode)) {
                    $scoid = (int) $domnode->nodeValue;
                }
            }
        }
    }

    return $scoid;
}

/**
 * This function returns the user's tele connect login username based off of
 * the tele connect module's login configuration settings (Moodle username or
 * Moodle email)
 *
 * @param int userid
 * @return mixed - user's login username or false if something bad happened
 */
function get_tconnect_username($userid) {
    global $DB;

    $username = '';
    $param    = array('id' => $userid);
    $record   = $DB->get_record('user', $param, 'id,username,email');

    if (!empty($userid) && !empty($record)) {
        $username = set_tusername($record->username, $record->email);
    }

    return $username;
}

/**
 * TEST FUNCTIONS - DELETE THIS AFTER COMPLETION OF TEST
 */
/*
function texpandsco ($tconnect, $scoid) {
    global $USER;

    $folderscoid = false;
    $params = array('action' => 'sco-expanded-contents',
                    'sco-id' => $scoid,
                    'filter-name' => $USER->email);

    $tconnect->create_request($params);

//    if ($tconnect->call_success()) {
//    }

}

function tout ($data) {
    $filename = '/tmp/tout.xml';
    $somecontent = $data;

    if (is_writable($filename)) {
        if (!$handle = fopen($filename, 'w')) {
             echo "Cannot open file ($filename)";
             return;
        }

        // Write $somecontent to our opened file.
        if (fwrite($handle, $somecontent) === FALSE) {
            echo "Cannot write to file ($filename)";
            return;
        }

        //echo "Success, wrote ($somecontent) to file ($filename)";

        fclose($handle);

    } else {
        echo "The file $filename is not writable";
    }
} */
