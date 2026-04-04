<?php
// RUN this cron every  day 
define('CLI_SCRIPT', true);
require_once('/var/www/html/toofaan/config.php');
//$servername = "localhost";
//$username = "root";
//$password = 'tele123$';
//$dbname = "toofan5";
$college='KMEC';
$logFile = "autogradeslogs.log"; // Log file
// Set the URL of the endpoint you want to POST data to
//$url = 'http://172.20.36.249/appsbackend/dronaapi.php?college=KMIT';
$url = 'https://kmec-api.teleuniv.in/assignmentperformance/cronData';
// Create connection


function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

$mysqli = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);


$sql = "SELECT 
    u.username AS htno,
    u.firstname AS name,
    g.finalgrade AS grade,
    c.shortname AS subject,
    FROM_UNIXTIME(MAX(ast.activity_start_time), '%d-%m-%Y') AS datelabel  -- Ensure one start time per user
FROM mdl_grade_grades g
JOIN mdl_grade_items gi ON g.itemid = gi.id
JOIN mdl_user u ON g.userid = u.id
JOIN mdl_course c ON gi.courseid = c.id
JOIN mdl_course_modules cm ON gi.iteminstance = cm.instance AND gi.itemmodule = 'assign'
LEFT JOIN mdl_activity_status_tsl ast ON cm.id = ast.activityid  -- Link with activity ID
WHERE DATE(FROM_UNIXTIME(g.timemodified)) = CURDATE()
GROUP BY u.id, u.username, u.firstname, g.finalgrade, c.shortname;  -- Group by student to remove duplicates
";

$result = $mysqli->query($sql);
// $data = [];
   // while ($row = $result->fetch_assoc()) {
     //   $data[] = $row;
   // }
    // Set response header to JSON
   // $data= json_encode($data);
   $studentGrades = [];

foreach ($result as $row) {
//var_dump($row);
$course_fullname=$row['subject'];
$course_name_parts = explode('-', $course_fullname);
$course_name = trim($course_name_parts[0]);
    $key = $row['htno'] . '-' . $row['subject'] . '-' . $row['datelabel']; // Unique key for each student-subject-date
    
    if (!isset($studentGrades[$key])) {
        $studentGrades[$key] = [
            'htno' => $row['htno'],
            'name' => $row['name'],
            'subject' => $course_name,
            'datelabel' => $row['datelabel'],
            'grades' => []
        ];
    }
    
    // Collect grades
    $studentGrades[$key]['grades'][] = floatval($row['grade']);
}

// Compute average for each student
$finalResults = [];
foreach ($studentGrades as $key => $data) {
//var_dump($studentGrades);
    $avgGrade = round(array_sum($data['grades']) / count($data['grades']));
    
    $finalResults[] = [
        'htno' => $data['htno'],
        'name' => $data['name'],
       // 'grade' => $avgGrade,
           //'grade' => number_format($avgGrade, 2, '.', ''), 
'grade' => round($avgGrade, 2),
        'subject' => $data['subject'],
        'datelabel' => $data['datelabel']
    ];
}
   $jsonData=  json_encode([
        //'method' => 3327,
        'students' => $finalResults
    ]);
//var_dump($jsonData);
//logMessage("JSON Data Sent: " . $jsonData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json', 
    'Content-Length: ' . strlen($jsonData) 
));
$response = curl_exec($ch);

if (curl_errno($ch)) {
    logMessage("cURL Error: " . curl_error($ch));

    echo 'cURL Error: ' . curl_error($ch);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
    logMessage("HTTP Response Code: " . $httpCode);
logMessage("assignment Response: " . $response);
    echo 'HTTP Response Code: ' . $httpCode . '<br>';
    echo 'Response:<br>';
    echo $response;
}
curl_close($ch);




?>
