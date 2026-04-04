<?php
/*
 * utility library for ajax calls
 *
 * contains methods for rendering course vise section tables
 *
 * removing a set of students from watchlist
 *
 *
 */
require('../../config.php');
//require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');
//require_once($CFG->dirroot . '/local/watchlist/lib.php');
//require_once($CFG->dirroot.'/local/teacher/testcenter/testcenterutil.php');
//require_once($CFG->libdir.'/gradelib.php');;
//require_once($CFG->libdir . '/csvlib.class.php');


// Remove fro Watchlist
$selectedids= optional_param('selected', -1, PARAM_INT); // array od student ids
$topicdisplayflag=optional_param('flag', 1, PARAM_INT);
//echo $topicdisplayflag;
$couid=optional_param('cid', -1, PARAM_INT);
if($couid!=1)
removeFromWatchlist($selectedids,$couid);



/*
 * removeFromWatchlist(array userids , courseid )
 *
 * to remove a set of users from watchlist
 */

function removeFromWatchlist($selectedids,$couid)
{
    if ($selectedids != -1) {
        foreach ($selectedids as $sid) {
            updateStatus(0, $sid, $couid);  // update watchlist status for each student on course base
        }
    }
}




// Get all courses along with  sections and labs
$cid= optional_param('cid', -1, PARAM_INT);
if($cid != -1) {
    $count=0;

    if($cid !=0) {

        $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
        echo get_courselist($course,$topicdisplayflag);
    }
    else
        echo  "<div style='height:300px;'><h3> No Records Found</h3></div>";

}


/*
 * get_courselist($course obj)
 *
 * returns a table with section and activities present in each section and no of activies completed
 *
 * information
 *
 */

function get_courselist($course,$topicdisplayflag)
{
    global $CFG;
    $topicdisplayflag=isset($topicdisplayflag)?$topicdisplayflag:1;
    $html = "<table class='generaltable search-table' id='cours' width='100%'>
            <thead>
            <tr>
                 <th style='width: 10%;'>Select</th>
               <th style='width: 25%;'>Topics</th>
               <th style='width: 20%;'>Status</th>
               <th style='width: 45%;'>Activities</th>
            </tr>
            </thead>
            <tbody id='cbody'>";

    $ehtml=$html;
    $completedhtml='';/*variable created by mahesh */
    $modinfo = get_fast_modinfo($course);
    $mods = $modinfo->get_cms();
    $sections = $modinfo->get_section_info_all();

    $arr = array();
    $main_array = get_sections($sections);
    // Activity loop
    $arr = get_activities($mods, $main_array);
    $arr1=get_activities_with_names($mods, $main_array);
    //var_dump($arr);
    $ptop = '';
    $com=0;$tot=0;
    $ht='';
    $count=0;
    $che=0;$activitynames='';
    foreach ($mods as $mod) {
        $top = $main_array[$mod->section];
        if ($main_array[$mod->section] == "")
            continue;
        if ($ptop != $mod->section) {
            $activitycounter=0;$completioncounter=0;
$i=1;

            $activitynames="<div id='act' ><div class='act-row'>";

 foreach ($arr1[$mod->section] as $modsec) {
                // var_dump($modsec);
            $len=count($arr1[$mod->section]);//know length of activities array in a section
                $src=$CFG->wwwroot.'/pix/'.$modsec['modname'].'.jpeg';
                $activitynames.="<div class='act-cell'><img src=$src alt=".$modsec['modname']." title=".$modsec['modname']." />&emsp;".$modsec['actname']."</div>";

            if($i%2==0 && $len!=$i){
                 $activitynames.="</div><div class='act-row other-rows'>";
             }
           $i++;
            }
            if($i%2==0 ){
                $activitynames.="</div></div>";
            }
            else
            $activitynames.="</div></div>";
            $i=0;

            foreach ($arr[$mod->section] as $modsec) {
                $activitycounter++;
                $com+=(int)$modsec['completion'];
                $tot+=(int)$modsec['count'];
                $ht .= "<span  >".$modsec['completion'] .' of ' . $modsec['count']."</span>";
                $src=$CFG->wwwroot.'/pix/'.$modsec['modname'].'.jpeg';
                $ht .="&emsp;<img src=$src alt=".$modsec['modname']." title=".$modsec['modname']." />";
                $ht .= "&emsp;&emsp;";
                if($modsec['completion']==$modsec['count'])
                    $completioncounter++;

            }

            if($completioncounter==$activitycounter){
                $classvar='seccompleted';
                $sta='disabled="true"';
                //print_r("completed");
                $sel='';



            }
            else{
                $classvar='';
                $sta='';
                if($che==0)
                {
                 $sel='checked';
                    $che++;
                }
                else{
                    $sel='';
                }

                //print_r("not completed");
            }
            /*code modified by mahesh -- start*/
            if($classvar){
                if($topicdisplayflag!=1){


                $completedhtml.='';
                $completedhtml .= html_writer::start_tag('tr', array('class' => $course->id.' '.$classvar));
                $completedhtml .= html_writer::start_tag('td');
                $completedhtml .= "<input type='radio' name='topics'  class='rdo' $sel $sta value ='$course->id-$mod->section'/>";
                $completedhtml .= html_writer::end_tag('td');
                $completedhtml .= html_writer::start_tag('td');
                $completedhtml .= "<span >".$top."</span>";
                $completedhtml .= html_writer::end_tag('td');
                $completedhtml .= html_writer::start_tag('td');
                $completedhtml .=$ht;
                $completedhtml .= html_writer::end_tag('td');
                $completedhtml .= html_writer::start_tag('td');
                $completedhtml .=$activitynames;

                $completedhtml .= html_writer::end_tag('td');
                $activitynames='';
                $ht='';
                $completedhtml .= html_writer::end_tag('td');
                $completedhtml .= html_writer::end_tag('tr');
                    $count++;
                }else{
                    $ht='';
                }
            }
            else{
                if($topicdisplayflag!=0){

                $html .= html_writer::start_tag('tr', array('class' => $course->id.' '.$classvar));
                $html .= html_writer::start_tag('td');
                $html .= "<input type='radio' name='topics'  class='rdo' $sel $sta value ='$course->id-$mod->section'/>";
                $html .= html_writer::end_tag('td');
                $html .= html_writer::start_tag('td');
                $html .= "<span >".$top."</span>";
                $html .= html_writer::end_tag('td');
                $html .= html_writer::start_tag('td');
                $html .=$ht;
                $html .= html_writer::end_tag('td');
                $html .= html_writer::start_tag('td');
                $html .=$activitynames;

                $html .= html_writer::end_tag('td');
                $activitynames='';
                $ht='';
                $html .= html_writer::end_tag('td');
                $html .= html_writer::end_tag('tr');
                    $count++;
                }else{
                    $ht='';
                }
            }
            /*code modified by mahesh -- end*/


        }
        $ptop = $mod->section;
    }
    $html.=$completedhtml;//adding closed sections at the end
    $html.="</tbody></table>";
    $html.="<p class='tabres'>($count) results found</p>";
    $sta=$com.','.$tot;
    if($count==0){
        $html=$ehtml."<tr><td colspan='4' style='text-align:center'><div class='nores'><h4> No Chapters found for this course</h4></div></td>
        </tr><tr><td colspan='4' style='padding: 15px 0px  !important; '></td></tr>
        <tr><td colspan='4' style='padding: 15px 0px  !important; ' ></td></tr>
        <tr><td colspan='4' style='padding: 15px 0px  !important; ' ></td></tr>
        <tr><td colspan='4' style='padding: 15px 0px  !important; '></td></tr></tbody></table>";
    }
    $html.="<input type='hidden' id='cstatus' value='$sta'/>";
    return $html;

}

/*
 * to arrage section names store in the section index
 */
function get_sections($sections)
{
    $main_array = array();
    $arr = array();
    $main_array = array();
    foreach ($sections as $sec) {
        $main_array[$sec->id] = $sec->name;
    }
    return $main_array;
}


/*
 * get module vise activity count along with completed activities count
 * $mods modules list in a couse
 * $main_array is sections array index represents section id and value at index is section name
 */

function get_activities($mods, $main_array)
{
    $arr = array();


    foreach ($mods as $mod) {
        if($mod->module!=1 &&$mod->module!=7) {
            //var_dump(get_string($mod->modname));
            //    var_dump($mod);

            if ($main_array[$mod->section] == "")
                continue;
            //here is the code-----------------------------------------------------------------------------------------------------------------------
            if(get_string($mod->modname) == "Feedback"){
                echo "";
            }
            else {
                if (array_key_exists($mod->section, $arr)) {
                    $count = $arr[$mod->section][get_string($mod->modname)]['count'];
                    $comp = (int)$arr[$mod->section][get_string($mod->modname)]['completion'] + (int)getActStatus($mod->id);
                    $arr[$mod->section][get_string($mod->modname)] = array("modname" => get_string($mod->modname), "completion" => $comp, 'count' => ++$count);
                } else {
                    $arr[$mod->section][get_string($mod->modname)] = array("modname" => get_string($mod->modname), "completion" => getActStatus($mod->id), "count" => 1);
                }
            }
        }//end of module!=1
    }

    return $arr;
}


// new code
function get_activities_with_names($mods, $main_array)
{
    $arr1 = array();


    foreach ($mods as $mod) {
        if($mod->module!=1 &&$mod->module!=7) {
            //var_dump($mod->name);
            if ($main_array[$mod->section] == "")
                continue;
            //Here is the code----------------------------------------------------------------------------------------------------------------------------------
            if($mod->modname == "feedback"){
                echo "";
            }
            else {
                $arr1[$mod->section][$mod->id] = array("actid" => $mod->id, "name" => $mod->modname, "modname" => get_string($mod->modname), "actname" => $mod->fullname);
            }

        }//end of module!=1
    }

    return $arr1;
}
//--------------------------------------------------------------------------------------//





// Get all courses along with  sections and labs
$coursid= optional_param('courseid', -1, PARAM_INT); // getting course id
if($coursid !=-1) {

    echo getWatchlistByCourse($coursid); // to get list of watchlisted people of a course
}





// Get all courses along with  sections and labs


$courid= optional_param('courid', -1, PARAM_INT); // getting course id
if($courid !=-1) {
    echo getWatchlistByCoursePdf($courid); // to get list of watchlisted people of a course
}




// Get all courses along with  sections and labs
$csvcourid= optional_param('csvcourid', -1, PARAM_INT); // getting course id
if($csvcourid !=-1) {
    echo getWatchlistByCourseCsv($csvcourid); // to get list of watchlisted people of a course
}



/*
 * $cid is course id
 * getWatchedByCourse( $courseid) To Get a table of watchlisted people of given course
 * Outline Report url : $CFG->wwwroot/report/outline/user.php?id=$userid&course=$courseid&mode=outline
 * Profile Url : $CFG->wwwroot . '/teacher/student_profile.php?sid=$userid
 *
 * To get completed activity ids of a course till yesterday
 * $yt=time()-86400;
 * select id from mdl_course_modules where course=$cid and `completionexpected`<= $yt
 *
 */



function getWatchlistByCourse($coursid){

    global $DB,$CFG; // Global Variables
    $count=0;

    if($coursid !=0) {
        $context = get_context_instance(CONTEXT_COURSE, $coursid); // Getting Copurse context from courseid

        $students = get_role_users(5, $context); // Getting students of a course

        $course = $DB->get_record('course', array('id' => $coursid), '*', MUST_EXIST); // Getting course record

        $watlist = getAllWatchlistRecordByCourse($coursid, 1); // calling lib method to get records of watchlisted

        $html = '';

        $html .= "<table id='wtab' class='tablesorter generaltable search-table1'><thead><tr>
        <th><input type='checkbox' class='checkbox1' id='selectall'/></th><th>Roll No</th>
        <th>Full Name</th><th>EAMCET Rank</th><th>Department</th>
        <th>Section</th>
        <th>Mean Grade</th><th>Today's Grade</th>
        <th>Attendance</th>
        <th>Report</th></tr></thead><tbody id='wtbody'>";
        $ehtml = $html;

        $html .= "<input  type='hidden' name='couid' value='$coursid'>";
        $count = 0;

            foreach ($watlist as $wlist) {

                $userobj = get_complete_user_data(id, $wlist->userid); // Getting User Object from userid
                $t = time(); //to get todays date time
                $yt = time() - 86400; // to get yesterdays date
                $attandance = getCntofAbsentActivities($wlist->userid, $coursid);

                $pregrade = round(MeanGrade($coursid, $wlist->userid),2) + 0.00;
                $pragrade = round(TodaysGrade($coursid, $wlist->userid),2) + 0.00;// checking Todays grade existance
                $html .= "<tr>";
                $html .= "<td ><input class='checkbox1' type='checkbox' name='check[]' value='$wlist->userid'></td>
                <td> <a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . $userobj->profile['rollno'] . "</a></td>
                <td><a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . fullname($userobj) . "</a></td>
                <td> " . $userobj->profile['eamcetrank'] . " </td><td> " . $userobj->profile['dept'] . " </td><td> " . $userobj->profile['section'] . " </td>
                <td>$pregrade</td><td>$pragrade</td><td>" . $attandance . "</td>
                <td><a target='_blank' href='$CFG->wwwroot/teacher/useroutline.php?id=$wlist->userid&course=$coursid&mode=complete'>
                <img title='Report' src='$CFG->wwwroot/pix/repo.png' style='width:16px;height:16px;padding-left:10px;'></a></td></tr>";
                                $count++;
            }
            $html .= "</tbody></table>";
            $html .= "<p class='tabres'>($count) results found</p>";


    }
    if($count==0){
        $html=$ehtml."<tr><td colspan='10' style='text-align:center'><div class='nores'><h4> No watchlist Results found for this course</h4></div></td></tr>
        <tr><td colspan='10' style='padding: 15px 0px  !important; '></td></tr>
        <tr><td colspan='10' style='padding: 15px 0px  !important; '></td></tr>
        <tr><td colspan='10' style='padding: 15px 0px  !important; '></td></tr>
        <tr><td colspan='10' style='padding: 15px 0px  !important; '></td></tr></tbody></table>";
        $html.="<input type='hidden' value=$count id='wcount'/>";
    }
    return $html;

}


// watchlist csv file


function getWatchlistByCourseCsv($coursid){

    global $DB,$CFG; // Global Variables

    $context = get_context_instance(CONTEXT_COURSE, $coursid); // Getting Copurse context from courseid

    $students = get_role_users(5, $context); // Getting students of a course

    $course = $DB->get_record('course', array('id' => $coursid), '*', MUST_EXIST); // Getting course record

    $watlist= getAllWatchlistRecordByCourse($coursid,1); // calling lib method to get records of watchlisted

    $html='';

    $html .= "<table id='wtab' class='generaltable search-table1'><thead>
    <tr><th>Roll No</th><th>Full Name</th><th>Rank</th><th>Department</th>
    <th>Mean Grade</th><th>Today's Grade</th><th>Attendance</th></tr></thead>
    <tbody id='wtbody'>";
    $fields = array('Roll No'        => 'Roll No',
        'Full Name'  => 'Full Name',
        'Rank'     => 'EAMCET Rank',
        'Department' => 'Department',
        'Mean Grade'  => 'Mean Grade',
        'Todays Grade'  => 'Todays Grade',
        'Attendance'  => 'Attendance',
    );
    $fdata[]=array();
    $filename = clean_filename($course->fullname.'_Watchlisted_Students_list');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);

    $html.= "<input  type='hidden' name='couid' value='$coursid'>";
    foreach ($watlist as $wlist) {
    $data=array();
        $userobj = get_complete_user_data(id, $wlist->userid); // Getting User Object from userid
        $t=time(); //to get todays date time
        $yt=time()-86400; // to get yesterdays date
        $attandance=getCntofAbsentActivities($wlist->userid,$coursid);
        $pregrade=round(MeanGrade($coursid,$wlist->userid),2)+0.00;
        $pragrade=round(TodaysGrade($coursid,$wlist->userid),2)+0.00;// checking Todays grade existance
        //$pragrade='B';
        $attandance = getCntofAbsentActivities($wlist->userid, $coursid);
        $html.= "<tr>";
        $html.= "<td> <a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . $userobj->profile['rollno'] . "</a></td><td><a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . fullname($userobj) . "</a></td><td> " . $userobj->profile['eamcetrank'] . " </td><td> " . $userobj->profile['dept'] . " </td><td>$pregrade</td><td>$pragrade</td><td>".$attandance."</td></tr>";
        $data = array('Roll No'        => $userobj->profile['rollno'],
            'Full Name'  =>fullname($userobj),
            'Rank'     => $userobj->profile['eamcetrank'],
            'Department' => $userobj->profile['dept'],
            'Mean Grade' => $pregrade,
            'Todays Grade'=>$pragrade,
            'Attendance'  => $attandance,
        );
        $csvexport->add_data($data);

    }

    $csvexport->download_file();

}


// watchlist print as pdf


function getWatchlistByCoursePdf($coursid){

    global $DB,$CFG; // Global Variables

    $context = get_context_instance(CONTEXT_COURSE, $coursid); // Getting Copurse context from courseid

    $students = get_role_users(5, $context); // Getting students of a course

    $course = $DB->get_record('course', array('id' => $coursid), '*', MUST_EXIST); // Getting course record

    $watlist= getAllWatchlistRecordByCourse($coursid,1); // calling lib method to get records of watchlisted

    $html='';

    $html .= "<table id='wtab' class='generaltable search-table1'><thead>
        <tr><th>Roll No</th><th>Full Name</th><th>EAMCET Rank</th><th>Department</th>
        <th>Mean Grade</th><th>Today's Grade</th><th>Attendance</th>
        </tr></thead><tbody id='wtbody'>";

    $html.= "<input  type='hidden' name='couid' value='$coursid'>";
    foreach ($watlist as $wlist) {
        $attandance = getCntofAbsentActivities($wlist->userid, $coursid);

        $userobj = get_complete_user_data(id, $wlist->userid); // Getting User Object from userid
        $t=time(); //to get todays date time
        $yt=time()-86400; // to get yesterdays date
        $attandance=getCntofAbsentActivities($wlist->userid,$coursid);
        $pregrade=round(MeanGrade($coursid,$wlist->userid),2)+0.00;
        $pragrade=round(TodaysGrade($coursid,$wlist->userid),2)+0.00;// checking Todays grade existance
        $html.= "<tr>";
        $html.= "<td> <a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . $userobj->profile['rollno'] . "</a></td><td><a href=" . $CFG->wwwroot . '/report/outline/user.php?id='.$wlist->userid.'&course='.$coursid.'&mode=outline'.">" . fullname($userobj) . "</a></td><td> " . $userobj->profile['eamcetrank'] . " </td><td> " . $userobj->profile['dept'] . " </td><td>$pregrade</td><td>$pragrade</td><td>".$attandance."</td></tr>";
    }
    $html .= "</tbody></table>";
    return $html;

}

/*******************************************************************************************/

function TodaysGrade($courseid,$studentId)
{
    global $DB;
    $at= strtotime(date("m/d/Y"));
    $sql="SELECT *
        FROM mdl_course_modules
        WHERE course = '".$courseid."'
        AND completionexpected >'".$at."'";
    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);
    $totalgrade=0;
    $meangrade=0;
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;

        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
        FROM mdl_modules
        WHERE id ='".$module."'";

        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name;

        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;
        $totalgrade=$totalgrade+$grade;
    }
    if($totalgrade>0)
    {
        $meangrade=$totalgrade/$items_completed_today;
    }

    return round($meangrade,2);

}





function MeanGrade($courseid,$studentId)
{
    global $DB;
    $at= strtotime(date("m/d/Y"));
    $sql="SELECT *
    FROM mdl_course_modules
    WHERE course = '".$courseid."'
    AND completionexpected <'".$at."'AND  completionexpected >0";
    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);
    $totalgrade=0;
    $meangrade=0;
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;
        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
        FROM mdl_modules
        WHERE id ='".$module."'";
        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name;
        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;
        $totalgrade=$totalgrade+$grade;
    }
    if($totalgrade>0)
    {
        $meangrade=$totalgrade/$items_completed_today;
    }
   return round($meangrade,2);
}





function TotalMeanGrade($courseid,$studentId)
{
    global $DB;
    $at= strtotime(date("m/d/Y"));
    $sql="SELECT *
    FROM mdl_course_modules
    WHERE course = '".$courseid."' AND  completionexpected >0";
    $res=$DB->get_records_sql($sql);
    $items_completed_today=count($res);
    $totalgrade=0;
    $meangrade=0;
    foreach ($res as $item )
    {
        $module=$item->module;
        $instance=$item->instance;

        /**************GETTING ITEM NAME **********/
        $sql_item="SELECT name
        FROM mdl_modules
        WHERE id ='".$module."'";
        $item_res=$DB->get_record_sql($sql_item);
        $itemname= $item_res->name;
        $grading_info=grade_get_grades($courseid, 'mod', $itemname,$instance, $studentId);
        $item = $grading_info->items[0];
        $gradeI= $item->grades[$studentId];
        $grade = $gradeI->grade ;
        $totalgrade=$totalgrade+$grade;
    }
    if($totalgrade>0)
    {
        $meangrade=$totalgrade/$items_completed_today;
    }
    return round($meangrade,2);
}


