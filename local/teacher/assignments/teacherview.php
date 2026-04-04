<style>
    #page{

    }
    #page-navbar{
        margin-left: 5% !important;
        MARGIN-TOP: 2% !important;
    }

    #region-main-box {
        min-height: 500px !important;
    }

</style>

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
 * This file is the entry point to the assign module. All pages are rendered from here
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$PAGE->requires->js('/student/jquery-latest.min.js');
$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/assign:view', $context);

$assign = new assign($context, $cm, $course);
$urlparams = array('id' => $id,
                  'action' => optional_param('action', '', PARAM_TEXT),
                  'rownum' => optional_param('rownum', 0, PARAM_INT),
                  'useridlistid' => optional_param('useridlistid', $assign->get_useridlist_key_id(), PARAM_ALPHANUM));

$url = new moodle_url('/mod/assign/teacherview.php', $urlparams);
$PAGE->set_url($url);

if (user_has_role_assignment($USER->id,5)  ) {
    $PAGE->requires->css('/student/custom.css');
}
if (user_has_role_assignment($USER->id,3)  ) {
    $PAGE->requires->css('/student/custom.css');
}

$completion=new completion_info($course);
$completion->set_module_viewed($cm);

// Get the assign class to
// render the page.
echo $assign->view(optional_param('action', '', PARAM_TEXT));

?>
<script>
$( document ).ready(function() {
    var baseUrl=$('#baseurl').val();
    var url=baseUrl+'teacher/dashboard.php';
    $('#page-navbar').append("<div id='navbar' style='padding:2% 5% 1% 6%;float: left'> <a id='dlink'>Dashboard</a> <span> / </span> <b>Quiz</b></div>");
    $('#dlink').attr("href",url);

});
</script>
