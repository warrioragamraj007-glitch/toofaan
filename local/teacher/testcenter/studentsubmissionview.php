
<?php
//if (user_has_role_assignment($USER->id,3)  ) {}
?>
<style>
    #block-region-side-pre{
        display:none;
    }.page-context-header.container{
display:none;
}
    #region-main{
        margin-right: 2%;
        width: 96% !important;
	min-height:500px;
    }

        #shd2{
        display: none;
    }
    .sublist-title{

        font-weight: bold;
        padding: 10px;
        vertical-align: bottom;


    }
.lastcol{
text-align:left !important;
}
h3{margin-top:0px !important;}
#studentname span {
    font-size: 1.2em !important;
}
#studentname {
    text-align: center;
}
.ace_editor{
    font-size: 22px;
}
</style>


<?php




require_once('../../../config.php');

require_once($CFG->dirroot.'/mod/vpl/forms/grade_form.php');
require_once($CFG->dirroot.'/mod/vpl/vpl_submission.class.php');
require_once($CFG->dirroot.'/mod/vpl/vpl.class.php');
require_once($CFG->dirroot.'/mod/vpl/locallib.php');
//require_once($CFG->dirroot.'/mod/vpl/views/');
 //$PAGE->requires->js('/teacher/jquery-latest.min.js',true);
//$PAGE->requires->css('/teacher/teacher.css',true);
global $CFG, $PAGE,$OUTPUT;


$context =  context_course::instance($COURSE->id);




try{
    require_login();
    if (!(user_has_role_assignment($USER->id,3) ) ) {

        redirect($CFG->wwwroot);
    }
    $actid = required_param('id',PARAM_INT);  //activityid
    $userid = optional_param('userid',FALSE,PARAM_INT);
    $vpl = new mod_vpl($actid);
    if($userid){
        //$vpl->prepare_page('forms/studentsubmissionview.php', array('id' => $id, 'userid' => $userid));
    }else{
       // $vpl->prepare_page('forms/studentsubmissionview.php', array('id' => $id));
    }

    if(!$vpl->is_visible()){
        notice(get_string('notavailable'));
    }
    $course = $vpl->get_course();
    $instance = $vpl->get_instance();

    $submissionid =  optional_param('submissionid',FALSE,PARAM_INT);
    //Read records
    if($userid && $userid != $USER->id){
        //Grader
        $vpl->require_capability(VPL_GRADE_CAPABILITY);
        $grader =TRUE;
        if($submissionid){
            $subinstance = $DB->get_record('vpl_submissions', array('id' => $submissionid));
        }else{
            $subinstance = $vpl->last_user_submission($userid);
        }
    }
    else{
        //view own submission
        $vpl->require_capability(VPL_VIEW_CAPABILITY);
        $userid = $USER->id;
        $grader = FALSE;
        if($submissionid && $vpl->has_capability(VPL_GRADE_CAPABILITY)){
            $subinstance = $DB->get_record('vpl_submissions',array('id' => $submissionid));
        }else{
            $subinstance = $vpl->last_user_submission($userid);
        }
    }
    if($subinstance!=null && $subinstance->vpl != $vpl->get_instance()->id){
        print_error('invalidcourseid');
    }
    if($USER->id == $userid){
        $vpl->network_check();
        $vpl->password_check();
    }
    //Print header
    $PAGE->requires->css(new moodle_url('/mod/vpl/css/sh.css'));
    $PAGE->requires->css(new moodle_url('/mod/vpl/editor/VPLIDE.css'));
    $vpl->print_header(get_string('submissionview',VPL));
   // $vpl->print_view_tabs(basename(__FILE__));
    //Display submission

    //Check consistence
    if(!$subinstance){
        notice(get_string('nosubmission',VPL),vpl_mod_href('view.php','id',$actid,'userid',$userid));
    }
    $submissionid = $subinstance->id;

    $sublist='<div class="sublist-title" id="studentname"> </div>';
    echo html_writer::tag('div',$sublist);

    //previous submissions list
    $detailed = abs(optional_param('detailed', 0, PARAM_INT))%2;

    $course = $vpl->get_course();
    $strdatesubmitted="Submitted On";
    $strsubmissionip="IP Address";
    $strdescription="Description";
    $strdtest="Action";
    $table = new html_table();
    $table->head  = array ('#',$strdatesubmitted, $strsubmissionip,$strdtest);
    //$table->align = array ('right','left', 'right');
    $table->nowrap = array (true,true,true);
    $submissionslist = $vpl->user_submissions($userid);
    $submissions = array();
    $nsub = count($submissionslist);
    foreach ($submissionslist as $submission) {
        if($detailed){
            $link = '#f'.$nsub;
        }else{
            $link =$CFG->wwwroot.'/local/teacher/testcenter/studentsubmissionview.php?id='.$actid.'&userid='.$userid.'&submissionid='.$submission->id;

        }
        $date = '<a href="'.$link.'" >'.userdate($submission->datesubmitted).'</a>';
$link2=$CFG->wwwroot.'/mod/vpl/forms/edit.php?id='.$actid.'&userid='.$userid;
$run='<a target="_blank" href="'.$link2.' ">Run</a>';
        $sub = new mod_vpl_submission($vpl,$submission);
        $submissions[] = $sub;
        $table->data[] = array ($nsub--,
            $date,$submission->subip
            ,$run);
    }

    echo '<div class="clearer"> </div>';
    echo html_writer::table($table);
    echo '<div style="text-align:center">';
    $url_base=$CFG->wwwroot.'/local/teacher/testcenter/studentsubmissionview.php?id='.$actid.'&userid='.$userid.'&detailed=';
    $urls= array($url_base.'0',$url_base.'1');
    echo '</div>';





    if($vpl->is_inconsistent_user($subinstance->userid,$userid)){
        print_error('vpl submission user inconsistence');
    }
    if($vpl->get_instance()->id != $subinstance->vpl){
        print_error('vpl submission vpl inconsistence');
    }
    $submission = new mod_vpl_submission($vpl,$subinstance);

    if($vpl->get_visiblegrade() || $vpl->has_capability(VPL_GRADE_CAPABILITY)){
        if($submission->is_graded()){
            //echo '<h2>'.get_string('grade').'</h2>';
            //$submission->print_grade(true);
        }
    }
    //$vpl->print_variation( $subinstance->userid);
    $subfullview='<div class="sublist-title" style="margin-top:20px">Submission Fullview</div>';
    echo html_writer::tag('div',$subfullview);
    $submission->print_student_submission();


    //echo "before footer";








    $vpl->print_footer();
    \mod_vpl\event\submission_viewed::log($submission);

}catch(Exception $e){
    print_r($e);
}
$user = $DB->get_record('user', array('id'=>$userid, 'deleted'=>0), '*', MUST_EXIST);

echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";
?>
<style>
#page{
margin-top: 2% !important;
}
#page-content{
min-height:585px !important;
}
</style>
<script>

$(document).ready(function () {

    var baseUrl=$('#baseurl').val();
    var url=baseUrl+'teacher/dashboard.php';
    $('#page-navbar').append("<div id='navbar' style='padding:6px;float:left;'> <a id='dlink'>Dashboard</a> </div>");
    $('#dlink').attr("href",url);
    $('#studentname').append('<span> Showing submissions of  <span style="text-transform:Uppercase"><?php echo fullname($user);?></span> for  <?php echo $vpl->get_printable_name();?></span>');

});
</script>
