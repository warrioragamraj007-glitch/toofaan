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
 * Launches IDE
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/../vpl.class.php');
require_once(dirname(__FILE__).'/../vpl_submission.class.php');
require_once(dirname(__FILE__).'/../editor/editor_utility.php');

$PAGE->requires->js('/local/student/customchanges.js');

global $USER, $DB;
require_login();
$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', false, PARAM_INT);
$copy = optional_param('privatecopy', false, PARAM_INT);
$subid = optional_param( 'submissionid', false, PARAM_INT );
$vpl = new mod_vpl($id);
$pageparms = ['id' => $id];
$activityName = get_activity_name($id);
if ($userid && ! $copy) {
    $pageparms['userid'] = $userid;
}
if ($copy) {
    $pageparms['privatecopy'] = 1;
}
if ($subid) {
    $pageparms['submissionid'] = $subid;
}
$vpl->prepare_page( 'forms/edit.php', $pageparms );
if (! $vpl->is_visible()) {
    vpl_redirect('?id=' . $id, get_string( 'notavailable' ), 'error' );
}
if (! $vpl->is_submit_able()) {
    vpl_redirect('?id=' . $id, get_string( 'notavailable' ), 'error' );
}
if (! $userid || $userid == $USER->id) { // Edit own submission.
    $userid = $USER->id;
    $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
} else { // Edit other user submission.
    $vpl->require_capability( VPL_GRADE_CAPABILITY );
}
$vpl->restrictions_check();

$instance = $vpl->get_instance();



$grader = $vpl->has_capability(VPL_GRADE_CAPABILITY);

// This code allow to edit previous versions.
if ($subid && $grader) {
    $parms = [
            'id' => $subid,
            'vpl' => $instance->id,
            'userid' => $userid,
    ];
    $res = $DB->get_records( 'vpl_submissions', $parms );
    if (count( $res ) == 1) {
        $lastsub = $res[$subid];
    } else {
        $lastsub = false;
    }
} else {
    $lastsub = $vpl->last_user_submission( $userid );
}
$options = [];
$options['id'] = $id;
$options['restrictededitor'] = $instance->restrictededitor && ! $grader;
$options['save'] = ! $instance->example;
$options['run'] = ($instance->run || $grader);
$options['debug'] = ($instance->debug || $grader);
$options['evaluate'] = ($instance->evaluate || $grader);
$options['example'] = true && $instance->example;
$options['comments'] = ! $options['example'];
$options['username'] = $vpl->fullname($DB->get_record( 'user', [ 'id' => $userid ] ), false);
$linkuserid = $copy ? $USER->id : $userid;
$ajaxurl = "edit.json.php?id={$id}&userid={$linkuserid}";
$options['ajaxurl'] = $ajaxurl . '&action=';
if ( $copy ) {
    $loadajaxurl = "edit.json.php?id={$id}&userid={$userid}&privatecopy=1";
    if ( $subid && $lastsub ) {
        $loadajaxurl .= "&subid={$lastsub->id}";
    }
    $options['loadajaxurl'] = $loadajaxurl . '&action=';
}
$options['download'] = "../views/downloadsubmission.php?id={$id}&userid={$linkuserid}";
$duedate = $vpl->get_effective_setting('duedate', $linkuserid);
$timeleft = $duedate - time();
$hour = 60 * 60;
if ( $duedate > 0 && $timeleft > -$hour ) {
    $options['timeLeft'] = $timeleft;
}
if ( $subid ) {
    $options['submissionid'] = $subid;
}

$reqfgm = $vpl->get_required_fgm();
$options['resetfiles'] = ($reqfgm->is_populated() && ! $instance->example);
$options['maxfiles'] = intval($instance->maxfiles);
$reqfilelist = $reqfgm->getFileList();
$options['minfiles'] = count( $reqfilelist );
if ($options['example']) {
    $options['maxfiles'] = $options['minfiles'];
}
$options['readOnlyFiles'] = $vpl->get_readonly_files();
$options['saved'] = $lastsub && ! $copy;
if ($lastsub) {
    $submission = new mod_vpl_submission( $vpl, $lastsub );
    \mod_vpl\event\submission_edited::log( $submission );
}

if ($copy && $grader) {
    $userid = $USER->id;
}

vpl_editor_util::generate_jquery();
$vpl->print_header( get_string( 'edit', VPL ) );
?>
<br>
<!-- Button trigger modal for hint start-->
<!-- <div style="text-align: right; margin-right: 4%; position:relative; top:-6px; text-transform: capitalize; "> -->
<!-- <div style='white-space: nowrap; overflow: hidden; width: 400px; text-align:center; font-size:22px;font-weight:bold'><?php echo $activityName; ?> + "</div>  -->
<div class="header-container">
        <div class="activity-name">
            <?php echo $activityName; ?>
        </div>
        <div class="hint-button">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
                HINT
            </button>
        </div>
    </div>



<?php
//var_dump($instance->id);
$introshow= $instance->intro;
$context = context_module::instance($id);
$hint= file_rewrite_pluginfile_urls ($introshow, 'pluginfile.php', $context->id, 'mod_vpl', 'intro', NULL);
// $activityName = get_activity_name($id);
?>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Hint</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php
        if($hint!=null){
        echo '<div class="intro-image-container">';
         echo $hint;
        echo '</div>';
        }else{
            echo "No hint found.";
        }
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>
<!-- Button trigger modal for hint end-->
<?php
/**** to hide description,submission,edit,submission view tabs from the vpl edit page start */
if(!user_has_role_assignment($USER->id,5) && !user_has_role_assignment($USER->id,3)){
$vpl->print_view_tabs( basename( __FILE__ ) );
}
/**** to hide description,submission,edit,submission view tabs from the vpl edit page end */
vpl_editor_util::print_tag();
vpl_editor_util::print_js_i18n();
// vpl_editor_util::print_js_description($vpl, $userid); ///**** to hide description from the vpl edit page ******/



vpl_editor_util::generate_requires($vpl, $options);
$vpl->print_footer();

if(user_has_role_assignment($USER->id,5) || user_has_role_assignment($USER->id,3)){
?>

<script>

    /** to remove links of courses and added dashboard links start */
    var baseUrl = '<?php echo $CFG->wwwroot ?>';
   var url = baseUrl + '/local/student/dashboard.php';
    // console.log(url);

    // Remove the element with class 'page-context-header'.
    document.querySelector('.page-context-header').remove();

    // Get the activity name based on the course module ID
    var activityName = '<?php echo get_activity_name($id); ?>';

    // Set the HTML content for the element with id 'page-navbar'.
    // document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: center;'> <a id='dlink' href='" + url + "'>Dashboard</a> <span style='margin: 0 5px;'>/</span> " + activityName + " </div>";

    // document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: center;'> <a id='dlink' href='" + url + "'>Dashboard</a> <span style='margin: 0 5px;'>/</span> <div style='white-space: nowrap; overflow: hidden; width: 400px;'>" + activityName + "</div> </div>";

    document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: end;'>  <span style='margin: 0 5px;'></span> </div>";
    // Set the 'href' attribute for the element with id 'dlink'.
    document.getElementById('dlink').href = url;
    /** to remove links of courses and added dashboard links start */
</script>
<style>
/* #page-content {
width: 100vw;
margin-left: -30%;
} */
#topofscroll {
max-width: 100%!important;
margin-top: -4%!important;
}
</style>
<?php
}
?>

<?php
// Function to get the activity name based on the course module ID
function get_activity_name($id) {
    $coursemodule = get_coursemodule_from_id('', $id, 0, false, MUST_EXIST);
    return $coursemodule->name;
}

?>

<script>
    /********Removing Rename, Delete, Import, Download,undo,redo,comments,next and About buttons from vpl edit menu start*********/
    // Function to remove a button by ID
    function removeButton(buttonId) {
        var buttonToRemove = document.getElementById(buttonId);
        if (buttonToRemove) {
            buttonToRemove.parentNode.removeChild(buttonToRemove);
            // console.log("Element removed successfully: " + buttonId);
        } else {
            // console.error("Element with ID '" + buttonId + "' not found");
        }
    }

    // Wait for the elements to exist in the DOM
    function waitForElements() {
        var aboutButton = document.getElementById("vpl_ide_about");
        var renameButton = document.getElementById("vpl_ide_rename");
        var deleteButton = document.getElementById("vpl_ide_delete");
        var importButton = document.getElementById("vpl_ide_import");
        var downloadButton = document.getElementById("vpl_ide_download");

        if (aboutButton && renameButton && deleteButton && importButton && downloadButton &&
            document.getElementById("vpl_ide_comments") &&
            document.getElementById("vpl_ide_undo") &&
            document.getElementById("vpl_ide_redo") &&
            document.getElementById("vpl_ide_next")) {

            // Remove 'About' button
            aboutButton.parentNode.removeChild(aboutButton);

            // Remove other buttons
            removeButton("vpl_ide_rename");
            removeButton("vpl_ide_delete");
            removeButton("vpl_ide_import");
            removeButton("vpl_ide_download");

            // Remove additional buttons
            removeButton("vpl_ide_comments");
            removeButton("vpl_ide_undo");
            removeButton("vpl_ide_redo");
            removeButton("vpl_ide_next");

            // Stop waiting once all buttons are found and removed
        } else {
            setTimeout(waitForElements, 100); // Check every 100 milliseconds
        }
    }

    // Start waiting for elements
    /********Removing Rename, Delete, Import, Download,undo,redo,comments,next and About buttons from vpl edit menu end*********/
    waitForElements();
</script>
<style>
    .intro-image-container img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    .intro-image-container {
        max-width: 80%;
        max-height: 70vh;
        overflow: hidden;
        margin: 0 auto;
    }
    .header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    padding: 10px;
}
.activity-name {
    /* white-space: nowrap; */
    /* overflow: hidden; */
    /* width: 400px; */
    font-size: 22px;
    font-weight: bold;
}
.input-group {
        display :none;
    }
    .divider {
display: none;
}
</style>
