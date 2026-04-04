<?php

// echo "vpl review";
require_once('../../config.php');
require_once($CFG->dirroot.'/mod/vpl/forms/grade_form.php');
require_once($CFG->dirroot.'/mod/vpl/vpl_submission.class.php');
require_once($CFG->dirroot.'/mod/vpl/vpl.class.php');
require_once($CFG->dirroot.'/mod/vpl/locallib.php');
// echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";


$PAGE->requires->js('/local/student/jquery-latest.min.js',true);
global $USER;
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
//$context =  context_course::instance("27");
if (user_has_role_assignment($USER->id,5)  ) {
    $PAGE->requires->css('/local/student/styles/custom1.css');
}
$PAGE->requires->js('/local/student/customchanges.js');
try{
    require_login();

    $id = required_param('id',PARAM_INT);
    $userid = $USER->id;
    $vpl = new mod_vpl($id);
    if($userid){
        //$vpl->prepare_page('forms/vplreview.php', array('id' => $id, 'userid' => $userid));
    }else{
       // $vpl->prepare_page('forms/vplreview.php', array('id' => $id));
    }
     // if(!$vpl->is_visible()){
       // notice(get_string('notavailable'));
    //}
    $course = $vpl->get_course();
    $instance = $vpl->get_instance();

    $submissionid =  optional_param('submissionid',FALSE,PARAM_INT);
    //Read records
    if($userid && $userid != $USER->id){
        //Grader
      //  $vpl->require_capability(VPL_GRADE_CAPABILITY);
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
    //    $vpl->network_check();
    //    $vpl->performNetworkCheck(); ////commented to get rid of error

        // $vpl->password_check();
        // $vpl->perform_password_check(); //commented to get rid of error
    }
    //Print header
    $PAGE->requires->css(new moodle_url('/mod/vpl/css/sh.css'));
    $PAGE->requires->css(new moodle_url('/mod/vpl/editor/VPLIDE.css'));
    $vpl->print_header(get_string('submissionview',VPL));
   // $vpl->print_view_tabs(basename(__FILE__));
    //Display submission

    //Check consistence
    if(!$subinstance){
        notice(get_string('nosubmission',VPL),vpl_mod_href('view.php','id',$id,'userid',$userid));
    }
    $submissionid = $subinstance->id;
    //previous submissions list
    $detailed = abs(optional_param('detailed', 0, PARAM_INT))%2;

    $course = $vpl->get_course();
   /* $strdatesubmitted="Submitted On";
    $strdescription="Description";
    $table = new html_table();
    $table->head  = array ('#',$strdatesubmitted, $strdescription);
    $table->align = array ('right','left', 'right');
    $table->nowrap = array (true,true,true);
    $submissionslist = $vpl->user_submissions($userid);
    $submissions = array();
    $nsub = count($submissionslist);
    foreach ($submissionslist as $submission) {
        if($detailed){
            $link = '#f'.$nsub;
        }else{
            $link =$CFG->wwwroot.'/student/vplreview.php?id='.$id.'&userid='.$userid.'&submissionid='.$submission->id;

        }
        $date = '<a href="'.$link.'">'.userdate($submission->datesubmitted).'</a>';
        $sub = new mod_vpl_submission($vpl,$submission);
        $submissions[] = $sub;
        $table->data[] = array ($nsub--,
            $date,
            s($sub->get_detail()));
    }
    */
    echo '<div style="text-align:center">';
    $url_base=$CFG->wwwroot.'local/student/vplreview.php?id='.$id.'&userid='.$userid.'&detailed=';
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

     $submission->print_submission();


    //echo "before footer";
    $vpl->print_footer();
    \mod_vpl\event\submission_viewed::log($submission);
}catch(Exception $e){
    print_r($e);
}

?>


<?php
//if (user_has_role_assignment($USER->id,3)  ) {}
?>
<style>
    #page-student-vplreview{

    }
    #page-student-vplreview #region-main {
    margin-right: 0px !important;
}
    #block-region-side-pre{
        display:none;
    }
    #region-main{
        margin-right: 2%;
        width: 96% !important;
    }


    #shd2{
        display: none;
    }
    .sublist-title{
        background-color: #048ec7;
        color: #fff;
        font-weight: bold;
        padding: 10px;
        vertical-align: bottom;
        background-image: linear-gradient(to bottom, #ea6645, #ea6645);
        border-radius: 5px 5px 0 0;

    }
.lastcol{
text-align:left !important;
}
h3{margin-top:0px !important}
    #page-content {

        min-height: 525px !important;
    }
    .ace_editor{
        font-size:20px;
    }

    #topofscroll {
max-width: 100%!important;
margin-top: -2%!important;
}
</style>

</style>



<script>
<?php if(user_has_role_assignment($USER->id,5)){ ?>
// if ($('#roles').val()) {
    var baseUrl = '<?php echo $CFG->wwwroot ?>';
    var url = baseUrl + '/local/student/dashboard.php';

    // Remove the element with class 'page-context-header'.
    document.querySelector('.page-context-header').remove();

    // Set the HTML content for the element with id 'page-navbar'.
    // document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: center; text-align:end'>  <span style='margin: 0 5px;'></span> <h4>Review</h4> </div>";
    document.getElementById('page-navbar').innerHTML = "<div id='navbar' style='padding: 2% 6% 1% 2%; display: flex; align-items: center;'>  <span style='margin: 0 5px;'></span> <div style='white-space: nowrap; overflow: hidden; width: 550px;text-align: end;'><h4 style='text-align:end'>Review</h4></div> </div>";
    // Set the 'href' attribute for the element with id 'dlink'.
    document.getElementById('dlink').href = url;
// }
<?php
}
?>
</script>

</script>
