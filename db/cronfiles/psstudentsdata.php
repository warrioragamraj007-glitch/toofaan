<?php
// RUN this cron every 15 days 
$servername = "teleuniv.net.in";
$username = "tele";
$password = "";
$dbname = "kmitsanjaya";
$college='KMIT';
// Set the URL of the endpoint you want to POST data to
// $url = 'https://psapi.kmitonline.com/psacademic/syncacademicscore';

// Create connection
$mysqli = new mysqli($servername, $username, $password,$dbname);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "Connected successfully";
// also add coursename in payload 
$coursenames = ['Tantrik-SDC','TOOFAAN-PS2-SDC','TOOFAAN-PS3-SDC']; 

// var_dump("h");
$payload = []; // Initialize an empty array to store the final data

foreach ($coursenames as $course) {
    $sql = "SELECT sm.htno as htno,
            SUM(p.todaysmeangrade) / (SELECT COUNT(DISTINCT datelabel) 
            FROM app_tessallator_performance 
            WHERE subject LIKE '%$course%' AND currentyear = p.currentyear) AS mean_grade,
            p.currentyear
            FROM app_tessallator_performance p
            JOIN app_student_master sm ON p.rollno = sm.rollno
            WHERE p.subject LIKE '%$course%'
            GROUP BY p.rollno, p.currentyear";
    
    $sqlresult = $mysqli->query($sql);

    if ($sqlresult && $sqlresult->num_rows > 0) {
        while ($row = $sqlresult->fetch_assoc()) {
            $htno = $row['htno'];
            $meanGrade = round($row['mean_grade'], 2);
            $currentYear = $row['currentyear'];

            // Check if the student already exists in the payload
            if (!isset($payload[$htno])) {
                // Initialize the student's data if not already present
                $payload[$htno] = [
                    'college' => 'KMIT',
                    'htno' => $htno,
                    'studentyear' => $currentYear,
                    'platform' => 'Trinetra',
                    'tantrik' => null,
                    'toofan' => null,
                    'prashnamanch' => null
                ];
            }

            // Update the relevant course grade
            if ($course == 'Tantrik-SDC') {
                $payload[$htno]['tantrik'] = $meanGrade;
            } elseif ($course == 'TOOFAAN-PS3-SDC' || $course == 'TOOFAAN-PS2-SDC') {
                $payload[$htno]['toofan'] = $meanGrade;
            }
        }
    }
}

// Convert the associative array to a regular indexed array if needed
var_dump($payload);
$payload = array_values($payload);
// var_dump($payload);

                    
                    // Convert the payload to JSON format
                    $jsonData = json_encode($payload);
                    // var_dump($jsonData);
        


        //         }
        //     }
        // }
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
//     }
// }
// }



?>
