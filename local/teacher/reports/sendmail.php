<?php
require_once(dirname(__FILE__) . '/../../config.php');


require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot.'/teacher/reports/reports_db.php');
require_once($CFG->libdir.'/phpmailer/class.phpmailer.php');
require_once($CFG->libdir.'/phpmailer/class.smtp.php');

function reportsBygrade()
{


    global $OUTPUT, $PAGE, $DB, $dat,$USER;
    $repObj=new custom_grade_report_db();
    $courseId=$_GET['cid'];
    $sectionId=$_GET['sid'];
    $act_values =$_GET['values'];
    $category_id=$_GET['gcid'];

    $dept=$_GET["dept"];
    $section=$_GET["section"];
    $grade_type=$_GET["gt"];
    $watchlist=$_GET["wl"];
    $rank=$_GET["rank"];
    $course_name=$DB->get_field_sql("SELECT `fullname`
FROM `mdl_course`
WHERE `id` ='$courseId'");

    $category_name=$DB->get_field_sql("SELECT `fullname`
FROM `mdl_grade_categories`
WHERE `id` ='$category_id'");

    $section_name=$DB->get_field_sql("SELECT `name`
FROM `mdl_course_sections`
WHERE `id` ='$sectionId'");


    $values_for_export="<b> Course Name : </b>" .$course_name."<br/>";
    if(!empty($category_name)){
        $values_for_export.="<b> Category Name :</b>" .$category_name."<br/>";
    }
    if(!empty($section_name)) {
        $values_for_export .= "<b> Section Name : </b>" . $section_name . "<br/>";

    }
    $activities="";
    $actValues = explode("@",  $act_values);
    $actValues=array_filter( $actValues);
    $act_name="";

    /******************GETTING NAMES OF ACTIVITIES *********************/

    $i=0;
    foreach ($actValues as $act) {
        $i=$i+1;
        $current_act = explode("-", $act);

        $act_name=$DB->get_field_sql("SELECT `name`
FROM `mdl_$current_act[1]`
WHERE `id` ='$current_act[0]'");
        if($i==1){
            $activities=$act_name;
        }else{
            $activities=$activities.",".$act_name;
        }

    }
    $values_for_export.="<b> Activities:: </b>" .$activities."<br/>";


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





    $processing    =   optional_param('processing',0,PARAM_INT);

    if(strcasecmp($grade_type,"submitted")==0||strcasecmp($grade_type,"notsubmitted")==0)
    {
        $grade_type="";
    }
    $dat='<h2 style="text-align: center;text-decoration: underline;">Complete Report</h2><br/><br/>';
    //$dat.=$pdfcreator;
    $dat.='<br/><br/><table >
<tr><td >Course</td><td >'.$course_name.'</td></tr><tr>

<td >Activity Type</td><td >--</td></tr>
<tr><td >Topic</td><td >'.$topic_name.'</td></tr>
<tr><td>Watchlisted</td><td >'.$watchlist.'</td></tr>

<tr><td >Activity </td><td >'.$act_name.'</td></tr>
<tr><td >Department</td><td >'.$dept.'</td></tr>

</table>';

    /*<tr><td >Submission Type</td><td >'.$grade_type.'</td><td >Section</td><td >'.$section.'</td></tr>
    <tr><td >EAMCET Rank</td><td >'.$rank.'</td><td ></td><td ></td></tr>
    */

    $reultHtml=$repObj->pdfRenderOutput($resultArr,$dept
        ,$section,$grade_type,$watchlist);

    return $values_for_export."#".$reultHtml;
}


function sendCustomMail($to ,$data)
{
$mail = new PHPMailer;

//$mail->SMTPDebug = 1;                               // Enable verbose debug output

$mail->IsSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'tessellator@kmit.in';                 // SMTP username
$mail->Password = 'Tele123$';                           // SMTP password
$mail->SMTPAuth   = true;
$mail->SMTPSecure = "ssl";                          // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465;                                    // TCP port to connect to

$mail->From = 'tessellator@kmit.in';
$mail->FromName = 'TESSELLATOR';
//$mail->addAddress($to, 'Joe User');     // Add a recipient
$mail->addAddress('tessellator@kmit.in');               // Name is optional
$mail->addReplyTo('tessellator@kmit.in', 'TESSELLATOR');
$mail->addCC('ngogte@kmit.in');
//$mail->addBCC('bcc@example.com');

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = date("m/d/Y").'    Report';
$mail->Body    = $data;
$mail->AltBody = 'Telepradigm Networks Pvt Ltd';


if(!$mail->send()) {

    return '<b>Message could not be sent.</b>';

} else {
    return '<b> Message has been sent</b>';
}}
$res=reportsBygrade();

$export_data= explode("#", $res);
$data='<html ><head><style type="text/css">
.generaltable {
    background-color: #E5B467;
    border-bottom: 1px solid #E5B467;
    color: #FFF;
}
.generaltable thead  {
    text-decoration:none;
    font-size: 12px !important;
    color: black;
}
.generaltable td   {
    background-color: #FFF;
    border-bottom: 1px solid #E6E6E6;
     color: black;
     padding:8px 0px !important;
}
 .generaltable hr {
    margin-top: 5px;
    border-width: 1px 0px;
    border-style: solid none;
    border-color: #988989;
    margin-bottom: 5px;
}
.generaltable h3{
text-align: center;
text-decoration: underline;
}
.generaltable a{
text-decoration: none;
color: black;
}
</style></head><body>';
//$export_data[0];
global $USER;
$userobj=get_complete_user_data(id,$USER->id);


//$firstname=$userobj->firstna
$data.=$data;
$data.="Dear" ."  " .$userobj->firstname;

$data.=$dat;
$data.="<br/><br/><table   class=' generaltable resulttbl' id='tableData'><thead class='head'><tr>
                                   <th>Roll NO</th>
                                   <th>Full Name</th>
                                  <th>Grade</th>
                                   <th>Rank</th><th>Department</th><th>Section</th><th>Watchlist</th></tr></thead><tbody>";
$data.=$export_data[1];

$data.="</tbody></table></body></html>";



//$resutl=sendCustomMail($userobj->email ,$data);
$resutl=sendCustomMail("eanusha@teleparadigm.com" ,$data);
//$exportresult=new Export_Reports();
//$html =$data;

//echo $html;
//$exportresult->report_download_pdf($html);

echo json_encode(array('html' => $resutl));
//echo "HIII";

?>

