<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Joseph.Cape
 * Date: 26/07/13
 * Time: 17:24
 * To change this template use File | Settings | File Templates.
 */

//include simplehtml_form.php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/watchlist/lib.php');
global $OUTPUT, $PAGE;
//$urlparams     =  required_param('value',0,PARAM_INT);
$processing    =   optional_param('processing',0,PARAM_INT);

$new_output = "";

$PAGE->set_context(context_system::instance());

if($_GET["change"]=='course')
{

$data=courseChange();
}

if($_GET["change"]=='topic')
{

    $data=topicChange();
}

if($_GET["change"]=='gt')
{

    $data=gtChange();
}
if($_GET["change"]=='addstudentwatch')
{

    $data=add_student_to_watchlist();
}
function add_student_to_watchlist()
{
    $cid= $_GET['cid'];
    $uid=$_GET['uid'];
    $status=getStatus($uid,$cid);
    if($status)
        $status=0;
    else
        $status=1;
     updateStatus($status,$uid,$cid);
    return $status;
}
function courseChange(){
    global $DB;
    $courseid=$_GET['cid'];
$categories="";
    $topics="";
    $activities="";
    $result="";
    /************************RETERVING CATEGORIES OF COURSE FROM GRADE BOOK***/
    $query="SELECT *
FROM mdl_grade_categories  WHERE courseid='".$courseid."' AND (parent!=''OR parent!=null) ";

    $category_obj = $DB->get_records_sql($query);
	$categories.='<select id="gcategory" class="gcategory">';
    $categories.=html_writer::start_tag('option',array('value'=>"0"));
    $categories.="Select Category";

    $categories.=html_writer::end_tag('option');
    foreach (   $category_obj as $category) {
//var_dump($sec);


        $categories.=html_writer::start_tag('option',array('value'=>$category->id));
        $categories.=$category->fullname;

        $categories.=html_writer::end_tag('option');

    }
    $categories.=html_writer::start_tag('option',array('value'=>"all"));

    $categories.="All";

    $categories.=html_writer::end_tag('option').'</select>';

    /************************* RETRIVING TOPICS OF A COURSE ********************/
    $secQuery="SELECT *
FROM mdl_course_sections WHERE course='".$courseid."'";


    $sectons_obj = $DB->get_records_sql( $secQuery);

    $topics.='<select class="topic" id="topics">'.html_writer::start_tag('option',array('value'=>"0"));
    $topics.="Select Topic";

    $topics.=html_writer::end_tag('option');
    foreach (   $sectons_obj as $section) {
//var_dump($sec);
        if(empty($section->name))
        {
        }
        else{

            $topics.=html_writer::start_tag('option',array('value'=>$section->id));
            $topics.=$section->name;

            $topics.=html_writer::end_tag('option');
        }
    }

    $topics.=html_writer::start_tag('option',array('value'=>"all"));

    $topics.="All";

    $topics.=html_writer::end_tag('option').'</select>';

    /***************getting all activities of a course *********/

    $activities.='<select class="act" id="act">'.html_writer::start_tag('option',array('value'=>"",'selected'=>'selected'));

    $activities.="Select Activity";
    $activities.=html_writer::end_tag('option');
    $activities_obj= $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $courseid AND `completionexpected`>0 ");
    foreach($activities_obj as $act ){

        $instance=$act->instance;
        $module=$act->module;
        $modname= $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
        $tablename="mdl_".$modname;


        $name=$DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

        $val=  $instance."-". $modname;

        //showing only vpl and quiz in reports page--added by mahesh
        if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0) {
            $activities .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

            $activities .= $name;
            $activities .= html_writer::end_tag('option');
        }


    }
    $activities.=html_writer::start_tag('option',array('value'=>"all"));
    $activities.="All";
    $activities.=html_writer::end_tag('option')."</select>";


    $result.=    $categories."#".$topics."#". $activities;
    return $result;
}


function topicChange()
{
    $course_id = $_GET['cid'];
    $section_id = $_GET['sid'];
    $category_id = $_GET['gid'];

    global $DB;
    $data = "";


    $data .= html_writer::start_tag('option', array('value' => "", 'slected' => 'selected'));

    $data .= "Select Activity";
    $data .= html_writer::end_tag('option');
    if(($category_id=="0"||$category_id==""|| strcasecmp($category_id ,'all')==0)&&($section_id=="0"||$section_id==""|| strcasecmp($section_id ,'all')==0)){




        $activities_obj= $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` =   $course_id  AND `completionexpected`>0 ");
        foreach($activities_obj as $act ){

            $instance=$act->instance;
            $module=$act->module;
            $modname= $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
            $tablename="mdl_".$modname;


            $name=$DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

            $val=  $instance."-". $modname;
            //showing only vpl and quiz in reports page--added by mahesh
            if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0) {
                $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                $data .= $name;
                $data .= html_writer::end_tag('option');
            }


        }

    }

    else if ($category_id == "0" || $category_id == "" || strcasecmp($category_id, 'all') == 0) {


        $activities = $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id AND  `section` =  $section_id AND `completionexpected`>0");
        foreach ($activities as $act) {

            $instance = $act->instance;
            $module = $act->module;
            $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
            $tablename = "mdl_" . $modname;


            $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

            $val = $instance . "-" . $modname;

            //showing only vpl and quiz in reports page--added by mahesh
            if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0) {
                $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                $data .= $name;
                $data .= html_writer::end_tag('option');
            }
        }

    } else if ($section_id == "0" || $section_id == "" || strcasecmp($section_id, 'all') == 0) {


        $cat_activities = $DB->get_records_sql("SELECT *
   FROM `mdl_grade_items`
   WHERE `courseid` = $course_id AND  `categoryid` =    $category_id");
        foreach ($cat_activities as $activity) {
            $cat_item_instace = $activity->iteminstance;
            $activities = $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id  AND `instance` =     $cat_item_instace");

            foreach ($activities as $act) {

                $instance = $act->instance;
                $module = $act->module;
                $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
                $tablename = "mdl_" . $modname;


                $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

                $val = $instance . "-" . $modname;

                //showing only vpl and quiz in reports page--added by mahesh
                if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0) {
                    $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                    $data .= $name;
                    $data .= html_writer::end_tag('option');
                }


            }
        }
    }
    else {


        $cat_activities = $DB->get_records_sql("SELECT *
   FROM `mdl_grade_items`
   WHERE `courseid` = $course_id AND  `categoryid` =    $category_id");

        foreach ($cat_activities as $activity) {
            $cat_item_instace = $activity->iteminstance;

            $activities = $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id AND  `section` =  $section_id AND `instance` =     $cat_item_instace AND `completionexpected`>0");

            foreach ($activities as $act) {

                $instance = $act->instance;
                $module = $act->module;
                $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
                $tablename = "mdl_" . $modname;


                $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

                $val = $instance . "-" . $modname;
                //showing only vpl and quiz in reports page--added by mahesh
                if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0) {
                    $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                    $data .= $name;
                    $data .= html_writer::end_tag('option');
                }


            }
        }

    }
    /*********************** SELECT ALL OPTION *****************/


    $data.=html_writer::start_tag('option',array('value'=>"all"));
    $data.="All";
    $data.=html_writer::end_tag('option');
    return  $data;

}

    function gtChange(){

        $course_id=$_GET['cid'];
        $section_id=$_GET['sid'];
        $category_id=$_GET['gid'];
//echo  $course_id."".$section_id."".$category_id;
        global $DB;
        $data="";



        $data.=html_writer::start_tag('option',array('value'=>"",'slected'=>'selected'));

        $data.="Select Activity";
        $data.=html_writer::end_tag('option');

     if(($category_id=="0"||$category_id==""|| strcasecmp($category_id ,'all')==0)&&($section_id=="0"||$section_id==""|| strcasecmp($section_id ,'all')==0)){

         $activities_obj= $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` =   $course_id  AND `completionexpected`>0 ");
         foreach($activities_obj as $act ){

             $instance=$act->instance;
             $module=$act->module;
             $modname= $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
             $tablename="mdl_".$modname;


             $name=$DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

             $val=  $instance."-". $modname;

             //showing only vpl and quiz in reports page--added by mahesh
             if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0){
                 $data .=html_writer::start_tag('option',array('value'=>$val,'class'=>$modname));

                 $data .=$name;
                 $data.=html_writer::end_tag('option');
             }



         }
        }

        else if($section_id=="0"||$section_id==""|| strcasecmp($section_id ,'all')==0){




            $cat_activities= $DB->get_records_sql("SELECT *
   FROM `mdl_grade_items`
   WHERE `courseid` = $course_id AND  `categoryid` =    $category_id");
            foreach($cat_activities as $activity) {
                $cat_item_instace = $activity->iteminstance;
                $activities = $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id AND `instance` =     $cat_item_instace AND `completionexpected`>0");

                foreach ($activities as $act) {

                    $instance = $act->instance;
                    $module = $act->module;
                    $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
                    $tablename = "mdl_" . $modname;


                    $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

                    $val = $instance . "-" . $modname;
                    //showing only vpl and quiz in reports page--added by mahesh
                    if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0){
                        $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                        $data .= $name;
                        $data .= html_writer::end_tag('option');
                    }


                }
            }}

      else  if($category_id=="0"||$category_id==""|| strcasecmp($category_id ,'all')==0){


            $activities= $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id AND  `section` =  $section_id AND `completionexpected`>0");
            foreach($activities as $act ) {

                $instance = $act->instance;
                $module = $act->module;
                $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
                $tablename = "mdl_" . $modname;


                $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

                $val = $instance . "-" . $modname;
                //showing only vpl and quiz in reports page--added by mahesh
                if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0){
                    $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                    $data .= $name;
                    $data .= html_writer::end_tag('option');
                }
            }

        }

        else

        {

            $cat_activities= $DB->get_records_sql("SELECT *
   FROM `mdl_grade_items`
   WHERE `courseid` = $course_id AND  `categoryid` =    $category_id");
            foreach($cat_activities as $activity) {
                $cat_item_instace = $activity->iteminstance;
                $activities = $DB->get_records_sql("SELECT *
   FROM `mdl_course_modules`
   WHERE `course` = $course_id AND `instance` =     $cat_item_instace AND  `section` =  $section_id AND `completionexpected`>0");


                foreach ($activities as $act) {

                    $instance = $act->instance;
                    $module = $act->module;
                    $modname = $DB->get_field_sql("SELECT `name`
   FROM `mdl_modules`
   WHERE `id`=$module");
                    $tablename = "mdl_" . $modname;


                    $name = $DB->get_field_sql("SELECT `name`
FROM $tablename
WHERE `id` =$instance
");

                    $val = $instance . "-" . $modname;
                    //showing only vpl and quiz in reports page--added by mahesh
                    if(strcmp($modname,'vpl')==0||strcmp($modname,'quiz')==0){
                        $data .= html_writer::start_tag('option', array('value' => $val, 'class' => $modname));

                        $data .= $name;
                        $data .= html_writer::end_tag('option');
                    }
                }


            }

            }









    /*********************** SELECT ALL OPTION *****************/


    $data.=html_writer::start_tag('option',array('value'=>"all"));
    $data.="All";
    $data.=html_writer::end_tag('option');
    return  $data;
}




if (!$processing) {
    echo json_encode(array('html' => $data));
} else {
    echo json_encode(array('new_output'=>$new_output));
}


