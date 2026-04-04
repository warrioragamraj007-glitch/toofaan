<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
/************* FOR REPORTS ***************/
require_once($CFG->dirroot.'/teacher/reports/reports_db.php');
function reportsBygrade()
{


    global $OUTPUT, $PAGE;
    $courseId=$_GET['cid'];
    $act_values =$_GET['values'];
    $repObj=new custom_grade_report_db();


 $rank=$_GET["rank"];


//var_dump($resultArr);
    $dept=$_GET["dept"];
    $section=$_GET["section"];
    $sectiontest=explode('-',$section);
    if(sizeof($sectiontest)>2){
    	$section=$sectiontest[sizeof($sectiontest)-2]."-".$sectiontest[sizeof($sectiontest)-1];
    }
    $grade_type=$_GET["gt"];
    $watchlist=$_GET["wl"];
    if( strcasecmp($grade_type,"submitted")==0||strcasecmp($grade_type,"notsubmitted")==0)
    {
        $act_values =$_GET['values'];
        $current_act = explode("-", $act_values);
        $students=$repObj->getStudentsByCourse($courseId);
        $students=$repObj->studnetsBySubmission($students,$courseId,$current_act[0],$grade_type);
        $grade_type="";
    }
else{

    $students=$repObj->getStudentsByCourse($courseId);
}
    $resultArr=$repObj->getGradeByActivity($students,$courseId,$act_values,$rank);

    $reultHtml=$repObj->renderOutput($resultArr,$dept
        ,$section,$grade_type,$watchlist);

    echo json_encode(array('html' => $reultHtml));

    $processing    =   optional_param('processing',0,PARAM_INT);


return $resultArr;
}
reportsBygrade();
