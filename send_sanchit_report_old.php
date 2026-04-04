<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/config.php');

// External DB connection (sanchit)
$servername = "ps.kmitonline.com";
$username = "psuser";
$password = 'Password541$$';
$dbname = "sanchit_live";
$collegeName = 'KMEC';
$logFile ="/var/www/html/toofaan/sanchitrepotslogs.log";
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    logMessage("DB Connection Error: " . $mysqli->connect_error);

    die("Failed to connect to external DB: " . $mysqli->connect_error);
}

// Escape college name
$escapedCollege = $mysqli->real_escape_string($collegeName);

// Step 1: Get college_id
$collegeQuery = "SELECT id FROM colleges WHERE name = '$escapedCollege'";
$collegeResult = $mysqli->query($collegeQuery);
if (!$collegeResult || $collegeResult->num_rows === 0) {
logMessage("College '$collegeName' not found.");
    die("College not found.");
}
$collegeRow = $collegeResult->fetch_assoc();
$collegeId = $collegeRow['id'];

// Step 2: Get activity session data from Moodle
$query = "SELECT 
    c.fullname AS coursename,
    COUNT(DISTINCT cm.id) AS total_activities,
    GROUP_CONCAT(DISTINCT CONCAT(u.firstname, ' ', u.lastname) SEPARATOR ', ') AS teachers,
    CASE
        WHEN SUM(cs.agentstatus IN (0, 1)) > 0 THEN 'BETAAL'
        WHEN SUM(cm.is_toofaan = 1 AND (cs.agentstatus IS NULL OR cs.agentstatus = 2)) > 0 THEN 'TOOFAAN'
        ELSE 'TESSELLATOR'
    END AS category
FROM mdl_activity_status_tsl tsl
JOIN mdl_course_modules cm ON cm.id = tsl.activityid
JOIN mdl_course c ON c.id = cm.course
LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
LEFT JOIN mdl_context ctx ON ctx.contextlevel = 50 AND ctx.instanceid = c.id
LEFT JOIN mdl_role_assignments ra ON ra.contextid = ctx.id
LEFT JOIN mdl_user u ON u.id = ra.userid
LEFT JOIN mdl_role r ON r.id = ra.roleid
WHERE DATE(FROM_UNIXTIME(tsl.activity_start_time)) = CURDATE()
  AND r.shortname = 'editingteacher'
GROUP BY c.id";

$data = $DB->get_records_sql($query);
if (!$data) {
    logMessage("❌ No activity session data found or query failed.");

}
// Step 3: Group data by tool
$groupedData = []; // toolId => [ courseData, courseData, ... ]

foreach ($data as $row) {
    $coursename = $mysqli->real_escape_string($row->coursename);
    $activities = (int)$row->total_activities;
    $teachers = $mysqli->real_escape_string($row->teachers);
    $tool = $mysqli->real_escape_string($row->category ?? 'TESSELLATOR');

    // Get tool ID
    $toolQuery = "SELECT id FROM tools WHERE name = '$tool'";
    $toolResult = $mysqli->query($toolQuery);
    if (!$toolResult || $toolResult->num_rows === 0) {
$msg = "⚠️ Tool '$tool' not found in DB. Skipping...";
        logMessage($msg);
        echo "$msg\n";
        continue;
    }
    $toolId = $toolResult->fetch_assoc()['id'];

    // Build course session array
    $courseData = [
        'coursename' => $row->coursename,
        'total_activities' => $activities,
        'teachers' => $row->teachers
    ];

    // Group by toolId
    if (!isset($groupedData[$toolId])) {
        $groupedData[$toolId] = [];
    }
    $groupedData[$toolId][] = $courseData;
}

// Step 4: Insert or update tool_usage
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

foreach ($groupedData as $toolId => $coursesArray) {
    $sessionJson = json_encode(['courses' => $coursesArray], JSON_UNESCAPED_UNICODE);
    if ($sessionJson === false) {
        die('JSON encoding error: ' . json_last_error_msg());
    }
    $sessionEscaped = $mysqli->real_escape_string($sessionJson);

    // Check if record exists
    $checkQuery = "SELECT id, session_data FROM tool_usage 
                   WHERE college_id = $collegeId 
                     AND tool_id = $toolId 
                     AND start_date = '$today'";
    $checkResult = $mysqli->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        // Update existing record (merge courses)
        $existingRow = $checkResult->fetch_assoc();
        $existingData = json_decode($existingRow['session_data'], true);
        if (!isset($existingData['courses'])) {
            $existingData['courses'] = [];
        }

        // Merge without duplicates
        foreach ($coursesArray as $newCourse) {
            $exists = false;
            foreach ($existingData['courses'] as &$existingCourse) {
                if ($existingCourse['coursename'] === $newCourse['coursename']) {
                    $exists = true;
                    if (
                $existingCourse['total_activities'] !== $newCourse['total_activities'] ||
                $existingCourse['teachers'] !== $newCourse['teachers']
            ) {
                // Update with new data
                $existingCourse['total_activities'] = $newCourse['total_activities'];
                $existingCourse['teachers'] = $newCourse['teachers'];
            }
                    break;
                }
            }
             unset($existingCourse);
            if (!$exists) {
                $existingData['courses'][] = $newCourse;
            }
        }

        $finalJson = json_encode(['courses' => $existingData['courses']], JSON_UNESCAPED_UNICODE);
        $finalEscaped = $mysqli->real_escape_string($finalJson);

        $updateQuery = "UPDATE tool_usage 
                        SET session_data = '$finalEscaped', updated_at = '$now' 
                        WHERE id = " . $existingRow['id'];
if ($mysqli->query($updateQuery)) {
             logMessage("🔁 Updated tool_usage for tool_id $toolId");
            echo "🔁 Updated tool_usage for tool_id $toolId\n";
        } else {
            logMessage("Update failed: " . $mysqli->error);
            echo "❌ Failed to update tool_usage for tool_id $toolId\n";
        }
    } else {
        // Insert new
        $insertQuery = "INSERT INTO tool_usage (college_id, tool_id, start_date, session_data, created_at, updated_at)
                        VALUES ($collegeId, $toolId, '$today', '$sessionEscaped', '$now', '$now')";
 if ($mysqli->query($insertQuery)) {
             logMessage("➕ Inserted new tool_usage record for tool_id $toolId");
            echo "➕ Inserted new tool_usage record for tool_id $toolId\n";
        } else {
            logMessage("Insert failed: " . $mysqli->error);
            echo "❌ Failed to insert tool_usage for tool_id $toolId\n";
        }
    }
}

echo "Tool usage data processed successfully.\n";
?>
