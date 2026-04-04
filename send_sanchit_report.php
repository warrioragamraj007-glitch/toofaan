<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/config.php');

// === DB CONFIG FOR SANCHIT ===
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

// === COLLEGE ID ===
$escapedCollege = $mysqli->real_escape_string($collegeName);
$collegeQuery = "SELECT id FROM colleges WHERE name = '$escapedCollege'";
$collegeResult = $mysqli->query($collegeQuery);
if (!$collegeResult || $collegeResult->num_rows === 0) {
    logMessage("College '$collegeName' not found.");
    die("College not found.");
}
$collegeId = $collegeResult->fetch_assoc()['id'];

// === SESSION DATA QUERY ===
$query = <<<SQL
WITH valid_activities AS (
    SELECT tsl.activityid AS cmid
    FROM mdl_activity_status_tsl tsl
    JOIN mdl_assign_submission sub ON tsl.activityid = (
        SELECT cm.id FROM mdl_course_modules cm 
        JOIN mdl_assign a ON a.id = cm.instance AND cm.module = (SELECT id FROM mdl_modules WHERE name = 'assign') 
        WHERE a.id = sub.assignment LIMIT 1
    )
    WHERE DATE(FROM_UNIXTIME(tsl.activity_start_time)) = CURDATE()
      AND sub.status = 'submitted'
    UNION
    SELECT tsl.activityid AS cmid
    FROM mdl_activity_status_tsl tsl
    JOIN mdl_grade_items gi ON gi.iteminstance = (
        SELECT cm.instance FROM mdl_course_modules cm WHERE cm.id = tsl.activityid
    ) AND gi.courseid = (
        SELECT cm.course FROM mdl_course_modules cm WHERE cm.id = tsl.activityid
    )
    JOIN mdl_grade_grades gg ON gg.itemid = gi.id
    WHERE DATE(FROM_UNIXTIME(tsl.activity_start_time)) = CURDATE()
      AND gg.finalgrade IS NOT NULL
)
SELECT 
    c.fullname AS coursename,
    COUNT(DISTINCT cm.id) AS total_activities,
    GROUP_CONCAT(DISTINCT CONCAT(teacher.firstname, ' ', teacher.lastname) SEPARATOR ', ') AS teachers,
    COUNT(DISTINCT student.userid) AS total_students,
    COUNT(DISTINCT COALESCE(submitted.userid, graded.userid)) AS students_with_submission_or_grade,
    CASE
        WHEN SUM(cs.agentstatus IN (0, 1)) > 0 THEN 'BETAAL'
        WHEN SUM(cm.is_toofaan = 1 AND (cs.agentstatus IS NULL OR cs.agentstatus = 2)) > 0 THEN 'TOOFAAN'
        ELSE 'TESSELLATOR'
    END AS category
FROM valid_activities va
JOIN mdl_course_modules cm ON cm.id = va.cmid
JOIN mdl_course c ON c.id = cm.course
LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
LEFT JOIN mdl_context teacher_ctx ON teacher_ctx.contextlevel = 50 AND teacher_ctx.instanceid = c.id
LEFT JOIN mdl_role_assignments teacher_ra ON teacher_ra.contextid = teacher_ctx.id AND teacher_ra.roleid = 3
LEFT JOIN mdl_user teacher ON teacher.id = teacher_ra.userid
LEFT JOIN mdl_enrol e ON e.courseid = c.id
LEFT JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
LEFT JOIN mdl_context student_ctx ON student_ctx.contextlevel = 50 AND student_ctx.instanceid = c.id
LEFT JOIN mdl_role_assignments student ON student.userid = ue.userid AND student.contextid = student_ctx.id AND student.roleid = 5
LEFT JOIN (
    SELECT sub.userid, cm.id AS cmid
    FROM mdl_assign_submission sub
    JOIN mdl_assign a ON a.id = sub.assignment
    JOIN mdl_course_modules cm ON cm.instance = a.id AND cm.module = (SELECT id FROM mdl_modules WHERE name = 'assign')
    WHERE sub.status = 'submitted'
) submitted ON submitted.cmid = cm.id AND submitted.userid = student.userid
LEFT JOIN (
    SELECT gg.userid, cm.id AS cmid
    FROM mdl_grade_grades gg
    JOIN mdl_grade_items gi ON gi.id = gg.itemid
    JOIN mdl_course_modules cm ON cm.instance = gi.iteminstance AND cm.course = gi.courseid
    WHERE gg.finalgrade IS NOT NULL
) graded ON graded.cmid = cm.id AND graded.userid = student.userid
GROUP BY c.id;
SQL;

$data = $DB->get_records_sql($query);
if (!$data) {
    logMessage("❌ No activity session data found.");
}

$groupedData = [];

foreach ($data as $row) {
    $coursename = $mysqli->real_escape_string($row->coursename);
    $activities = (int)$row->total_activities;
    $teachers = $mysqli->real_escape_string($row->teachers);
    $tool = $mysqli->real_escape_string($row->category ?? 'TESSELLATOR');

    $toolQuery = "SELECT id FROM tools WHERE name = '$tool'";
    $toolResult = $mysqli->query($toolQuery);
    if (!$toolResult || $toolResult->num_rows === 0) {
        logMessage("⚠️ Tool '$tool' not found. Skipping.");
        continue;
    }
    $toolId = $toolResult->fetch_assoc()['id'];

    // --- GET DETAILED ACTIVITIES ---
    $activityDetails = [];
    $activitiesInCourse = $DB->get_records_sql("
        SELECT cm.id AS activityid, cm.instance, cm.module, m.name AS modname, cm.course
        FROM mdl_course_modules cm
        JOIN mdl_modules m ON cm.module = m.id
        WHERE cm.course = (
            SELECT id FROM mdl_course WHERE fullname = ?
        ) AND cm.id IN (
            SELECT activityid FROM mdl_activity_status_tsl
            WHERE DATE(FROM_UNIXTIME(activity_start_time)) = CURDATE()
        )
    ", [$row->coursename]);

    foreach ($activitiesInCourse as $act) {
        $activityName = $DB->get_field_sql("SELECT name FROM mdl_{$act->modname} WHERE id = ?", [$act->instance]);

        $submitted = 0;
        if ($act->modname === 'assign') {
            $submitted = $DB->count_records_sql("
                SELECT COUNT(DISTINCT sub.userid)
                FROM mdl_assign_submission sub
                WHERE sub.assignment = ? AND sub.status = 'submitted'
            ", [$act->instance]);
        }

        // $graded = $DB->count_records_sql("
        //     SELECT COUNT(DISTINCT gg.userid)
        //     FROM mdl_grade_items gi
        //     JOIN mdl_grade_grades gg ON gi.id = gg.itemid
        //     WHERE gi.iteminstance = ? AND gi.courseid = ? AND gg.finalgrade IS NOT NULL
        // ", [$act->instance, $act->course]);
$graded = $DB->count_records_sql("
        SELECT COUNT(DISTINCT gg.userid)
        FROM mdl_grade_items gi
        JOIN mdl_grade_grades gg ON gi.id = gg.itemid
        JOIN mdl_role_assignments ra ON gg.userid = ra.userid
        JOIN mdl_context ctx ON ra.contextid = ctx.id
        WHERE gi.iteminstance = ?
          AND gi.courseid = ?
          AND gg.finalgrade IS NOT NULL
          AND ra.roleid = 5
          AND ctx.contextlevel = 50
          AND ctx.instanceid = gi.courseid
    ", [$act->instance, $act->course]);
    
        // $fullScore = $DB->count_records_sql("
        //     SELECT COUNT(DISTINCT gg.userid)
        //     FROM mdl_grade_items gi
        //     JOIN mdl_grade_grades gg ON gi.id = gg.itemid
        //     WHERE gi.iteminstance = ? AND gi.courseid = ? AND gg.finalgrade = gi.grademax
        // ", [$act->instance, $act->course]);

          $fullScore = $DB->count_records_sql("
    SELECT COUNT(DISTINCT gg.userid)
    FROM mdl_grade_items gi
    JOIN mdl_grade_grades gg ON gi.id = gg.itemid
    JOIN mdl_role_assignments ra ON gg.userid = ra.userid
    JOIN mdl_context ctx ON ra.contextid = ctx.id
    WHERE gi.iteminstance = ?
      AND gi.courseid = ?
      AND gg.finalgrade = gi.grademax
      AND ra.roleid = 5
      AND ctx.contextlevel = 50
      AND ctx.instanceid = gi.courseid
", [$act->instance, $act->course]);
 // ✅ Get requested files only for VPL activities
 $requestedFiles = [];
 if ($act->modname === 'vpl') {
     $reqFolder = "/var/www/toofan50data/vpl_data/{$act->instance}/required_files/";
     if (is_dir($reqFolder)) {
         $files = glob($reqFolder . "*");
         foreach ($files as $file) {
             if (is_file($file)) {
                 $requestedFiles[] = basename($file);
             }
         }
     }
 }
        $activityDetails[] = [
            'activityid' => $act->activityid,
            'name' => $activityName,
            'submitted' => $submitted,
            'graded' => $graded,
            'full_score' => $fullScore,
            'activity_type' =>$act->modname,
            'requested_files' => $requestedFiles  // ✅ add this line

        ];
    }

    $courseData = [
        'coursename' => $row->coursename,
        'total_activities' => $activities,
        'teachers' => $row->teachers,
        'students' => $row->students_with_submission_or_grade . '/' . $row->total_students,
        'activities' => $activityDetails
    ];

    $groupedData[$toolId][] = $courseData;
}

// === INSERT/UPDATE TOOL USAGE ===
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// foreach ($groupedData as $toolId => $coursesArray) {
//     $sessionJson = json_encode(['courses' => $coursesArray], JSON_UNESCAPED_UNICODE);
//     $sessionEscaped = $mysqli->real_escape_string($sessionJson);

//     $checkQuery = "SELECT id, session_data FROM tool_usage 
//                    WHERE college_id = $collegeId AND tool_id = $toolId AND start_date = '$today'";
//     $checkResult = $mysqli->query($checkQuery);

//     if ($checkResult && $checkResult->num_rows > 0) {
//         $existingRow = $checkResult->fetch_assoc();
//         $existingData = json_decode($existingRow['session_data'], true);
//         $existingData['courses'] = $coursesArray;

//         $finalJson = json_encode(['courses' => $existingData['courses']], JSON_UNESCAPED_UNICODE);
//         $finalEscaped = $mysqli->real_escape_string($finalJson);

//         $updateQuery = "UPDATE tool_usage 
//                         SET session_data = '$finalEscaped', updated_at = '$now' 
//                         WHERE id = " . $existingRow['id'];
//         $mysqli->query($updateQuery);
//         logMessage("🔁 Updated tool_usage for tool_id $toolId");
//     } else {
//         $insertQuery = "INSERT INTO tool_usage (college_id, tool_id, start_date, session_data, created_at, updated_at)
//                         VALUES ($collegeId, $toolId, '$today', '$sessionEscaped', '$now', '$now')";
//         $mysqli->query($insertQuery);
//         logMessage("➕ Inserted tool_usage for tool_id $toolId");
//     }
// }

foreach ($groupedData as $toolId => $coursesArray) {
    $checkQuery = "SELECT id, session_data FROM tool_usage 
                   WHERE college_id = $collegeId AND tool_id = $toolId AND start_date = '$today'";
    $checkResult = $mysqli->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        // Update existing record (merge courses)
        $existingRow = $checkResult->fetch_assoc();
        $existingData = json_decode($existingRow['session_data'], true);

        if (!isset($existingData['courses'])) {
            $existingData['courses'] = [];
        }

        foreach ($coursesArray as $newCourse) {
            $found = false;
            foreach ($existingData['courses'] as &$existingCourse) {
                if ($existingCourse['coursename'] === $newCourse['coursename']) {
                    $found = true;

                    // Update existing course if any detail differs
                    if (
                        $existingCourse['total_activities'] !== $newCourse['total_activities'] ||
                        $existingCourse['teachers'] !== $newCourse['teachers'] ||
                        $existingCourse['students'] !== $newCourse['students'] ||
                        json_encode($existingCourse['activities']) !== json_encode($newCourse['activities'])
                    ) {
                        $existingCourse = $newCourse;
                    }
                    break;
                }
            }
            unset($existingCourse);

            // Add new course if not found
            if (!$found) {
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
        } else {
            logMessage("❌ Update failed for tool_id $toolId: " . $mysqli->error);
        }
    } else {
        // Insert new record
        $sessionJson = json_encode(['courses' => $coursesArray], JSON_UNESCAPED_UNICODE);
        $sessionEscaped = $mysqli->real_escape_string($sessionJson);

        $insertQuery = "INSERT INTO tool_usage (college_id, tool_id, start_date, session_data, created_at, updated_at)
                        VALUES ($collegeId, $toolId, '$today', '$sessionEscaped', '$now', '$now')";

        if ($mysqli->query($insertQuery)) {
            logMessage("➕ Inserted tool_usage for tool_id $toolId");
        } else {
            logMessage("❌ Insert failed for tool_id $toolId: " . $mysqli->error);
        }
    }
}

echo "📊 Tool usage data updated.\n";

// === SEND VPL FILES ===
$requestBasePath = "/var/www/toofan50data/vpl_data/";
$externalApiUrl = "https://sanchitapi.teleuniv.com/getrequestedfiles.php";

$vplActivities = $DB->get_records_sql("SELECT DISTINCT activityid FROM mdl_activity_status_tsl WHERE DATE(FROM_UNIXTIME(activity_start_time)) = CURDATE()");
foreach ($vplActivities as $act) {
    $cmid = $act->activityid;
    $result = $DB->get_record_sql("SELECT cm.instance FROM mdl_course_modules cm JOIN mdl_modules m ON cm.module = m.id WHERE cm.id = $cmid AND m.name = 'vpl'");
    if (!$result) continue;

    $vplid = $result->instance;
    $reqFolder = $requestBasePath . $vplid . "/required_files/";
    if (!is_dir($reqFolder)) continue;

    $files = glob($reqFolder . "*");
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        $originalFilename = basename($file);

        // ✅ Fetch course name from activity ID
        $courseQuery = "
            SELECT c.fullname
            FROM mdl_course_modules cm
            JOIN mdl_course c ON cm.course = c.id
            WHERE cm.id = $cmid
            LIMIT 1";
        $courseRecord = $DB->get_record_sql($courseQuery);
    
        if (!$courseRecord) {
            echo "⚠️ Could not find course for cmid $cmid\n";
            continue;
        }
    
        $coursename = $courseRecord->fullname;
    
        // ✅ Clean and format
        $safeCollege = preg_replace('/[^a-zA-Z0-9]/', '', $collegeName);
        $safeCourse = preg_replace('/[^a-zA-Z0-9]/', '', $coursename);
        $newFilename = "{$safeCollege}_{$safeCourse}_{$cmid}_{$originalFilename}";
    
        // ✅ Send via cURL
        $curlFile = new CURLFile($file);
        $curlFile->setPostFilename($newFilename);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $externalApiUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => [
                'file' => $curlFile,
                'vplid' => $vplid
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            logMessage("✅ Sent $filename (VPL ID: $vplid)");
        } else {
           logMessage("❌ Failed to send $filename (HTTP $httpCode)");
        }
    }
     // ✅ Send testcases file if it exists
$testcasePath = $requestBasePath . $vplid . "/execution_files/vpl_evaluate.cases";
if (is_file($testcasePath)) {
    $testcaseFilename = "vpl_evaluate.cases";

    // Reuse the same safe naming logic
    $safeCollege = preg_replace('/[^a-zA-Z0-9]/', '', $collegeName);
    $safeCourse = preg_replace('/[^a-zA-Z0-9]/', '', $coursename);
    $newFilename = "{$safeCollege}_{$safeCourse}_{$cmid}_{$testcaseFilename}";

    $curlFile = new CURLFile($testcasePath);
    $curlFile->setPostFilename($newFilename);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $externalApiUrl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'file' => $curlFile,
            'vplid' => $vplid
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode === 200) {
       logMessage("✅ Sent testcases file: $newFilename (VPL ID: $vplid)");
    } else {
        logMessage("❌ Failed to send testcases file (HTTP $httpCode)");
    }
} else {
   logMessage("⚠️ Testcase file not found for VPL ID $vplid");
}
 // ✅ Send app.tes file if it exists
  $app = $requestBasePath . $vplid . "/execution_files/app.spec.ts";
  // var_dump($app);
  if (is_file($app)) {
      $appFilename = "app.spec.ts";
  
      // Reuse the same safe naming logic
      $safeCollege = preg_replace('/[^a-zA-Z0-9]/', '', $collegeName);
      $safeCourse = preg_replace('/[^a-zA-Z0-9]/', '', $coursename);
      $newFilename = "{$safeCollege}_{$safeCourse}_{$cmid}_{$appFilename}";
  
      $curlFile = new CURLFile($app);
      $curlFile->setPostFilename($newFilename);
      $curl = curl_init();
      curl_setopt_array($curl, [
          CURLOPT_URL => $externalApiUrl,
          CURLOPT_POST => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POSTFIELDS => [
              'file' => $curlFile,
              'vplid' => $vplid
          ],
      ]);
  
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
  
      if ($httpCode === 200) {
         logMessage("✅ Sent app.spec.ts file: $newFilename (VPL ID: $vplid)");
      } else {
          logMessage("❌ Failed to send app.spec.ts file (HTTP $httpCode)");
      }
  } else {
      logMessage("⚠️ app.spec.ts file not found for VPL ID $vplid");
  }
}

echo "✅ All processing complete.\n";
