<?php
// RUN this cron every 15 days 
$servername = "localhost";
$username = "root";
$password = "tele123$";
$dbname = "tessellator50";
$college='KMIT';
// Set the URL of the endpoint you want to POST data to
$url = 'http://172.20.36.249/appsbackend/dronaapi.php?college=KMIT';

// Create connection
$mysqli = new mysqli($servername, $username, $password,$dbname);

$course_ids = ['1667', '457']; // add your courseids you want to POST data

// $dates = [];
// $today = new DateTime();

// for ($i = 0; $i < 5; $i++) {
//     $dates[] = $today->format('Y-m-d'); // Format as 'Y-m-d' for SQL compatibility
//     $today->modify('-1 day');
// }
$startDate = new DateTime('2024-10-20'); // Replace with your desired start date or keep empty to post on todays data

// $startDate = new DateTime(); 
$today = new DateTime(); // Today's date

// Initialize an array to hold the dates
$dates = [];

// Loop from the start date to today
while ($startDate <= $today) {
    $dates[] = $startDate->format('Y-m-d'); // Format as 'Y-m-d' for SQL compatibility
    $startDate->modify('+1 day'); // Move to the next day
}
$stdsection = 0;

foreach ($course_ids as $courseid) {
    foreach ($dates as $date) {
        // var_dump($date);
        $jsonData = getCourseTodayPerformance($mysqli, $courseid, $stdsection, $date);
         var_dump($jsonData);
        
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
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Process the response
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
    echo 'HTTP Response Code: ' . $httpCode . '<br>';
    echo 'Response:<br>';
    echo $response;
}

// Close cURL session
curl_close($ch);
    }
}

function getCourseTodayPerformance($mysqli,$courseid,$stdsection,$date){
    
$course_query = "SELECT shortname FROM mdl_course where id='$courseid'";
// var_dump($courseid);
$result = $mysqli->query($course_query);
$row = $result->fetch_assoc();
$course_name = $row['shortname'];
        
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
       
            $performance = TotalMeanGradeToday($mysqli,$courseid, $student->id, $date); // Get student's performance
    
            // Only add the student if they have quiz or lab data
            if ($performance['totallabscount'] || $performance['totalquizescount']) {
                // var_dump('per');
                $formattedDate = DateTime::createFromFormat('Y-m-d', $date)->format('d-m-y');

                $studentData[] = [
                    'cdate' => $formattedDate,
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
    $result = json_encode([
        'method' => 3319,
        'students' => $studentData
    ]);

    return $result;

}

function TotalMeanGradeToday($mysqli,$courseid,$studentId,$date){
    $t1 = "1.00";
    $t2 = "23.00";
    $d1 = $date . ' ' . $t1;
    $d2 = $date . ' ' . $t2;
    
    $from = strtotime($d1);
    $slot_end = strtotime($d2);
    $courseReport=array();
// var_dump($studentId);
    $vpl = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'vpl'")->fetch_object()->id;
$quiz = $mysqli->query("SELECT id FROM mdl_modules WHERE name = 'quiz'")->fetch_object()->id;
// var_dump($vpl);
// var_dump($quiz);
// Get course modules within the expected completion date range
    
$sql = "
    SELECT * 
    FROM mdl_course_modules 
    WHERE course = '$courseid' 
      AND completionexpected BETWEEN '$from' AND '$slot_end'
      AND module IN ($vpl, $quiz)
";
// var_dump($sql);
$res = $mysqli->query($sql);
// var_dump($res);

$items_completed_today = $res->num_rows;
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
// Loop through course modules to calculate grades and counts
while ($item = $res->fetch_object()) {
    // var_dump('res');
    $module = $item->module;
    $instance = $item->instance;

    // Get item name
    $sql_item = "SELECT name FROM mdl_modules WHERE id = '$module'";
    $item_res = $mysqli->query($sql_item)->fetch_object();
    $itemname = $item_res->name;

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
   
    // Count labs and quizzes
    if ($module == $vpl) {
        $totalLabsCount++;
        if ($grade) {
            $labaverage += $grade;
            $totalLabsAttemptedCount++;
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
                // var_dump($totalLabsAttemptedCount);
            }
        }
    }

    if ($module == $quiz) {
        $totalQuizCount++;
        if ($grade) {
            $quizaverage += $grade;
            $totalQuizAttemptedCount++;
            // var_dump($totalQuizAttemptedCount);
        }
    }

    $totalgrade += $grade;
}
    

// Check for current activities attempted grades
$startedActivitiesSql = "
   SELECT * FROM `mdl_activity_status_tsl` WHERE (`status` = 1 OR `status` = 0 OR `status` = 2) AND ((`activity_start_time`  between '".$from."' AND '".$slot_end."') OR (`activity_stop_time`  between '".$from."' AND '".$slot_end."')) 
";
// var_dump($startedActivitiesSql);
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
if($module==$quiz){$totalQuizCount++;if($grade){$quizaverage=$quizaverage+$grade;$totalQuizAttemptedCount++;}
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




?>
