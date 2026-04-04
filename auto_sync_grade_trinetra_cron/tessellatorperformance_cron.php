<?php
$start_time = microtime(true);

//@error_reporting(E_ALL | E_STRICT);
 //@ini_set('display_errors', '1');
 
// RUN this cron every day
 define('CLI_SCRIPT', true);
require_once('/var/www/html/toofaan/config.php');

$college='KMEC';

$mysqli = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);

$url = 'https://kmec-api.teleuniv.in/tessellatorperformance/cronData';
$logFile = "autogradeslogs.log"; // Log file
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

$vplid = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'vpl'")->fetch_object()->id;
$quizid = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'quiz'")->fetch_object()->id;

$today = new DateTime(); // Today's date
$date = $today->format('Y-m-d'); // Get today's date in 'Y-m-d' format
$todayStart = strtotime(date('Y-m-d 00:00:00'));
$todayEnd = strtotime(date('Y-m-d 23:59:59'));

// Get only courses where activities started today
$sql = "
    SELECT DISTINCT cm.course
    FROM mdl_course_modules cm
    JOIN mdl_activity_status_tsl tsl ON cm.id = tsl.activityid
    WHERE tsl.activity_start_time BETWEEN $todayStart AND $todayEnd
      AND tsl.status IN (0,1,2)
      AND cm.module IN ($vplid, $quizid)
";
$result = $mysqli->query($sql);

$course_ids = [];
// var_dump($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $course_ids[] = $row['course'];
    }
}

$stdsection = 0;
$allData = [];

foreach ($course_ids as $courseid) {
        //var_dump($date);
        $Data = getCourseTodayPerformance($mysqli, $courseid, $stdsection, $date,$vplid,$quizid);
     //  var_dump($Data);
      if (!empty($Data) && is_array($Data)) {
    $allData = array_merge($allData, $Data);
}
        }
      //var_dump($allData);
            $jsonData = json_encode([
        //'method' => 3321,
        'students' => $allData
    ]);
//var_dump($jsonData);
// Initialize cURL session
$ch = curl_init();
// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Set cURL options for POST request
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return the response as a string
curl_setopt($ch, CURLOPT_POST, 1); // Set the request method to POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Set the JSON data as the request body
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json', // Set the content type to JSON
    'Content-Length: ' . strlen($jsonData) // Set the content length
));

// Execute the cURL session and fetch the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    logMessage("cURL Error: " . curl_error($ch));
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Process the response
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
     logMessage("HTTP Response Code: " . $httpCode);
logMessage("tessellator Response: " . $response);
    echo 'HTTP Response Code: ' . $httpCode . '<br>';
    echo 'Response:<br>';
    echo $response;
}

// Close cURL session
curl_close($ch);


function getCourseTodayPerformance($mysqli,$courseid,$stdsection,$date,$vplid,$quizid) {
    // var_dump($courseid);
    $studentData = [];
    
    if (!$mysqli) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
$course_query = "SELECT shortname FROM mdl_course where id='$courseid'";
// var_dump($courseid);
$result = $mysqli->query($course_query);
$row = $result->fetch_assoc();
$course_fullname = $row['shortname'];
$course_name_parts = explode('-', $course_fullname);
$course_name = trim($course_name_parts[0]);
        
        $section_query = "SELECT id FROM mdl_user_info_field WHERE shortname = 'section'";
        // var_dump($section_query);
        $section_result = $mysqli->query($section_query);
        $sectionfield = $section_result->fetch_assoc()['id'];
        // var_dump($sectionfield);
    
        // Query to get the `rollno` field ID
        $roll_query = "SELECT id FROM mdl_user_info_field WHERE shortname = 'rollno'";
        $roll_result = $mysqli->query($roll_query);
        $rollfield = $roll_result->fetch_assoc()['id'];
        // var_dump($rollfield);
    $student_query ="SELECT u.id, u.username, u.firstname, u.lastname, u.email
    FROM mdl_user u
    JOIN mdl_role_assignments ra ON ra.userid = u.id
    JOIN mdl_context ctx ON ra.contextid = ctx.id
    JOIN mdl_course c ON ctx.instanceid = c.id
    WHERE ctx.contextlevel = 50 
    AND c.id = $courseid
    AND ra.roleid = 5; 
    ";
    $students= $mysqli->query($student_query);
    $studentArray=array();
    $att=0;
    // Loop through each student
    while ($student = $students->fetch_object()) {
        $att++;
        $rollno = $student->username;
        // var_dump($rollno);
       
            $performance = TotalMeanGradeToday($mysqli,$courseid, $student->id, $date,$vplid,$quizid); // Get student's performance
    
            // Only add the student if they have quiz or lab data
            if ($performance['totallabscount'] || $performance['totalquizescount']) {
                // var_dump('per');
                $formattedDate = DateTime::createFromFormat('Y-m-d', $date)->format('d-m-y');

                $studentData[] = [
                    'datelabel' => $formattedDate,
                    'subject' => $course_name,
                    
                    'htno' => $rollno,
                    // 'FirstName' => htmlspecialchars($student->firstname),
                    'totallabs' => $performance['totallabscount'],
                    'totalquizes' => $performance['totalquizescount'],
                    'totalattemptedlabs' => $performance['attemptedlabs'],
                    'totalattemptedquizes' => $performance['attemptedquiz'],
                    'labsgrade' => $performance['labaverage'],
                    'quizgrade' => $performance['quizaverage']
                ];
               
            }
        // }
    }
    $result = json_encode([$studentData]);

    return $studentData;

}

function TotalMeanGradeToday($mysqli,$courseid,$studentId,$date,$vpl,$quiz) {
   
    $from = strtotime($date . ' 00:00:00');
$slot_end = strtotime($date . ' 23:59:59');

   
    $courseReport=array();
// var_dump($studentId);
//     $vpl = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'vpl'")->fetch_object()->id;
// $quiz = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'quiz'")->fetch_object()->id;
// var_dump($vpl);
// var_dump($quiz);

// $items_completed_today = $res->num_rows;
// var_dump($items_completed_today);
// Initialize counters and averages
$totalgrade = 0;
$meangrade = 0;
$totalLabsCount = 0;
$totalLabsAttemptedCount = 0;
$totalQuizCount = 0;
$totalQuizAttemptedCount = 0;
$labaverage = 0;
$quizaverage = 0;
$tlabaverage = 0;
$tquizaverage = 0;


// Check for current activities attempted grades
$startedActivitiesSql = "
   SELECT *
   FROM mdl_activity_status_tsl
   WHERE status IN (0, 1, 2)
     AND activity_start_time BETWEEN '".$from."' AND '".$slot_end."'
";

 //var_dump($startedActivitiesSql);
$startedActivitiesRes = $mysqli->query($startedActivitiesSql);
// var_dump($startedActivitiesRes);
$startedActivityIds = [];
while ($activity = $startedActivitiesRes->fetch_object()) {
    $startedActivityIds[] = $activity->activityid;
}
// var_dump($startedActivityIds);

if (count($startedActivityIds)) {
    $rsql = "
        SELECT * 
        FROM mdl_course_modules 
        WHERE id IN (" . implode(',', $startedActivityIds) . ")
          AND course = '$courseid'
          AND module IN ($vpl, $quiz)
    ";
    //var_dump($rsql);
    $currentRes = $mysqli->query($rsql);
    $items_completed_today += $currentRes->num_rows;
    // var_dump($items_completed_today);

    while ($item = $currentRes->fetch_object()) {
        $module = $item->module;
        $instance = $item->instance;
// var_dump($instance);
        // Get item name
        $sql_item = "SELECT name FROM mdl_modules WHERE id = '$module'";
        $item_res = $mysqli->query($sql_item)->fetch_object();
        $itemname = $item_res->name;
// var_dump($itemname);

$sql_grade_item = "
    SELECT id 
    FROM mdl_grade_items 
    WHERE courseid = '$courseid' 
      AND itemmodule = '$itemname' 
      AND iteminstance = '$instance'
";
$grade_item_res = $mysqli->query($sql_grade_item)->fetch_object();
$grade_item_id = $grade_item_res ? $grade_item_res->id : null;

// var_dump($grade_item_id);
if ($grade_item_id) {
    // Get the student's grade for the specific grade item
    $sql_grade = "
        SELECT finalgrade 
        FROM mdl_grade_grades 
        WHERE itemid = '$grade_item_id' 
          AND userid = '$studentId'
    ";
    // var_dump($sql_grade);
    $grade_res = $mysqli->query($sql_grade)->fetch_object();
    $grade = $grade_res ? $grade_res->finalgrade : null;
    // var_dump($grade);
}

if ($module == $vpl) {
    $totalLabsCount++;
    if ($grade) {
        $labaverage += $grade;
        $totalLabsAttemptedCount++;
        // var_dump($labaverage);
        // var_dump($totalLabsAttemptedCount);
    } else {
        $sql_submission = "
            SELECT datesubmitted 
            FROM mdl_vpl_submissions 
            WHERE vpl = '$instance' 
              AND userid = '$studentId'
        ";
        $submission_res = $mysqli->query($sql_submission);
        if ($submission_res->num_rows > 0) {
            $totalLabsAttemptedCount++;
        }
    }
    $tlabaverage=$labaverage/$totalLabsCount;

}
if($module==$quiz)
{
$totalQuizCount++;
if($grade){
$quizaverage=$quizaverage+$grade;
$totalQuizAttemptedCount++;
}
          $tquizaverage=$quizaverage/$totalQuizCount;

}

            $totalgrade=$totalgrade+$grade;


    }

}

if($totalgrade>0)
{
    $meangrade=$totalgrade/$items_completed_today;
    // var_dump($meangrade);
}

$courseReport=array("totallabscount"=>$totalLabsCount,"labaverage"=>$tlabaverage,"totalquizescount"=>$totalQuizCount,"quizaverage"=>$tquizaverage,"coursemeangrade"=>round($meangrade,2),'attemptedlabs'=>$totalLabsAttemptedCount,'attemptedquiz'=>$totalQuizAttemptedCount);
return $courseReport;

}



$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
echo "Page loaded in $execution_time seconds";
?>

