<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Processes AJAX requests from IDE
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

define( 'AJAX_SCRIPT', true );

require(__DIR__ . '/../../../config.php');

global $PAGE, $OUTPUT, $USER;

$result = new stdClass();
$result->success = true;
$result->response = new stdClass();
$result->error = '';
try {
    require_once(dirname( __FILE__ ) . '/edit.class.php');
    if (! isloggedin()) {
        throw new Exception( get_string( 'loggedinnot' ) );
    }
    $id = required_param( 'id', PARAM_INT ); // Course module id.
    $action = required_param( 'action', PARAM_ALPHANUMEXT );
    $userid = optional_param( 'userid', false, PARAM_INT );
    $subid = optional_param( 'subid', false, PARAM_INT );
    $copy = optional_param('privatecopy', false, PARAM_INT);
    $vpl = new mod_vpl( $id );
    // TODO use or not sesskey."require_sesskey();".
    require_login( $vpl->get_course(), false );

    $PAGE->set_url( new moodle_url( '/mod/vpl/forms/edit.json.php', [
            'id' => $id,
            'action' => $action,
    ] ) );
    echo $OUTPUT->header(); // Send headers.
    $rawdata = file_get_contents( "php://input" );
    $rawdatasize = strlen( $rawdata );
    if ($_SERVER['CONTENT_LENGTH'] != $rawdatasize) {
        throw new Exception( "Ajax POST error: CONTENT_LENGTH expected " . $_SERVER['CONTENT_LENGTH'] . " found $rawdatasize)" );
    }
    \mod_vpl\util\phpconfig::increase_memory_limit();
    $actiondata = json_decode($rawdata, null, 512, JSON_INVALID_UTF8_SUBSTITUTE );
    if (! $vpl->is_submit_able()) {
        throw new Exception( get_string( 'notavailable' ) );
    }
    if (! $userid || $userid == $USER->id) { // Make load own submission.
        $userid = $USER->id;
        $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
        $vpl->restrictions_check();
    } else { // Access other user submission.
        $vpl->require_capability( VPL_GRADE_CAPABILITY );
    }
    $instance = $vpl->get_instance();
    switch ($action) {
        case 'save':
            if ($userid != $USER->id) {
                $vpl->require_capability( VPL_MANAGE_CAPABILITY );
            }
            $files = mod_vpl_edit::filesfromide( $actiondata->files );
            if ( empty($actiondata->comments) ) {
                $actiondata->comments = '';
            }
            if ( empty($actiondata->version) ) {
                $actiondata->version = -1;
            } else {
                $actiondata->version = (int) $actiondata->version;
            }
            $result->response = mod_vpl_edit::save( $vpl, $userid, $files, $actiondata->comments, $actiondata->version );
            break;
        case 'update':
            $files = mod_vpl_edit::filesfromide( $actiondata->files );
            $filestodelete = isset($actiondata->filestodelete) ? $actiondata->filestodelete : [];
            $result->response = mod_vpl_edit::update($vpl,
                                                     $userid,
                                                     $actiondata->processid,
                                                     $files,
                                                     $filestodelete);
            break;
        case 'resetfiles':
            $files = mod_vpl_edit::get_requested_files( $vpl );
            $result->response->files = mod_vpl_edit::filestoide( $files );
            break;
        case 'load':
            if ( isset($actiondata->submissionid) ) {
                $subid = $actiondata->submissionid;
            }
            if ( $subid && $vpl->has_capability( VPL_GRADE_CAPABILITY ) ) {
                $load = mod_vpl_edit::load( $vpl, $userid , $subid);
            } else {
                $load = mod_vpl_edit::load( $vpl, $userid );
            }
            if ($copy) {
                $load->version = -1;
            }
            $load->files = mod_vpl_edit::filestoide( $load->files );
            $result->response = $load;
            break;
        case 'run':
        case 'debug':
        case 'evaluate':
            if (! $instance->$action && ! $vpl->has_capability( VPL_GRADE_CAPABILITY )) {
                throw new Exception( get_string( 'notavailable' ) );
            }
            $result->response = mod_vpl_edit::execute( $vpl, $userid, $action, $actiondata );
            break;
        case 'retrieve':

        // algorithm check ram mohan
        if ( isset($actiondata->submissionid) ) {
            $subid = $actiondata->submissionid;
        }
             // code checking by ram mohan
             $load = mod_vpl_edit::load( $vpl, $userid ,$subid );
             //var_dump(reset($load->files));
             //var_dump($vpl);
             //exit(0);
             $code=reset($load->files);
             $code = addslashes(str_replace(["\n", "\r"], '\n', $code));
            
             $algo = $vpl->get_instance()->algorithm;
             //$algo= $vpl->cm->idnumber;
             //var_dump($algo);
             //exit(0);
             //$code = "'" . str_replace("'", "\\'", $code) . "'";
            //$code = str_replace(array("\r\n", "\r", "\n"), ' ', $code); // Remove line breaks
            // $code = preg_replace('/\/\/(.*)/', '', $code); // Remove inline comments
 
          if($algo!=''){
           
           $res=mod_vpl_edit::retrieve_result( $vpl, $userid,$actiondata->processid  );
           //var_dump($res->compilation);
           //var_dump(stripos($res->compilation,'Not compiled'));
           if(stripos($res->compilation,"Not compiled") !== false){
               $res->compilation=$res->compilation;
           }else{
                   //  $res->evaluation=$res->evaluation."++".$msg;

                   $model = 'gpt-3.5-turbo';
                   $prompt = $code . " Analyze the given Java code and determine if the program uses $algo or not. Provide the time complexity of the code in the following format:\n\n Provide your response in JSON format: {'response': [yes/no],'Comments':[Comments],'Time complexity':[Time complexity]}. Please SEND RESPONSE in VALID JSON FORMAT ONLY.";
                   $data = array(
                       'model' => $model,
                       'messages' => array(
                           array(
                               'role' => 'user',
                               'content' => $prompt
                           )
                       )
                   );
                   
                   $ch = curl_init();
                   curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                   curl_setopt($ch, CURLOPT_POST, 1);
                   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                   
                   $headers = array();
                   $headers[] = 'Content-Type: application/json';
                   $headers[] = 'Authorization: Bearer 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';
                   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                   
                   $response = curl_exec($ch);
                   if (curl_errno($ch)) {
                       echo 'Error:' . curl_error($ch);
                   }
                   curl_close($ch);
                   $response = json_decode($response, true);
                   
                   if($response['choices'][0]["message"]["content"]){
                   
                   $json_string=$response['choices'][0]["message"]["content"];
                   
                   $json_string = str_replace("'", "\"", $json_string);
                   // Decode the JSON string into an associative array
                   $data = json_decode($json_string, true);
                   
                   // Access the values
                   $responseValue = strtoupper($data['response']);
                   $comments = $data['Comments'];
                   $timeComplexity = $data['Time complexity'];
                   $responseAsBool = ($responseValue === 'YES');
                   // Output the values
                   //echo "Response: $response<br/>";
                  // echo "Comments: $comments<br/>";
                   //echo "Time complexity: $timeComplexity<br/>";
                   }
                   else{
                     //  throw new Exception("Invalid Json Response Try again");
                   }
                   
                   //$actiondata->timecomplexity=$timeComplexity ;
                   //$actiondata->validalgorithm=$responseAsBool ;
                   //var_dump($actiondata);
                    
     
                   $msg="";
                    if(!$responseAsBool){
                       // throw new Exception("Not Implemented $algo Algorithm [ Time complexity $timeComplexity ]");
                       $msg="Not Implemented $algo Algorithm [$timeComplexity]";
                    }
                    else{
                       //throw new Exception("Implemented  $algo Algorithm [ Time complexity $timeComplexity ]");
                       $msg="Implemented  $algo Algorithm [$timeComplexity]";
                      
                    }
                
                    if($algo=='TimeComplexity'){
                        $msg="Time Compexity of your code : $timeComplexity";
                    }
     
                  
     
                    global $DB;
                    try {
                     $rsql = "UPDATE mdl_vpl_submissions
                     SET algorithm = '".$responseAsBool."', timecomplexity = '".$timeComplexity."'
                     WHERE userid = '".$userid."' AND vpl = '".$vpl->get_instance()->id."' AND id = (
                         SELECT max_id
                         FROM (
                             SELECT MAX(id) AS max_id
                             FROM mdl_vpl_submissions
                             WHERE userid = '".$userid."' AND vpl = '".$vpl->get_instance()->id."'
                         ) AS subquery
                     )";
                     $result1 = $DB->execute($rsql, null);
                     // Additional code here if needed after the query execution.
                 } catch (Exception $e) {
                     // Handle the exception here, e.g., log the error, display a message, or take appropriate action.
                    // echo $rsql;
                 }


                       $res->compilation=$res->compilation."--".$msg;
           }
           
             // $res->grade=$res->grade."**".$msg;
             $result->response = $res;
            }
            else{
                $res=mod_vpl_edit::retrieve_result( $vpl, $userid ,$actiondata->processid );
                $result->response = $res;
            }
                //end of code algorithm check ram mohan

            //$result->response = mod_vpl_edit::retrieve_result( $vpl, $userid, $actiondata->processid );
            break;
        case 'cancel':
            $result->response->error = mod_vpl_edit::cancel( $vpl, $userid, $actiondata->processid );
            break;
        case 'getjails':
            $result->response->servers = vpl_jailserver_manager::get_https_server_list( $vpl->get_instance()->jailservers );
            break;
        case 'directrun':
            $files = mod_vpl_edit::filesfromide( $actiondata->files );
            $result->response = mod_vpl_edit::directrun( $vpl, $userid, $actiondata->command, $files);
            break;
        default:
            throw new Exception( 'ajax action error: ' + $action );
    }
    if ($result->response === null) {
        $result->success = false;
        $result->error = "Response is null for $action";
    } else {
        $duedate = $vpl->get_effective_setting('duedate', $userid);
        $timeleft = $duedate - time();
        $hour = 60 * 60;
        if ( $duedate > 0 && $timeleft > -$hour ) {
            $result->response->timeLeft = $timeleft;
        }
    }
} catch ( Exception $e ) {
    $result->success = false;
    $result->error = $e->getMessage();
}
echo json_encode( $result );
die();
