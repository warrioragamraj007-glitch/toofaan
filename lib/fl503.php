<?php
// flib.php - Place in /lib/ folder
// Clean output buffer to prevent any unwanted output
ob_clean();

// Prevent any HTML output
define('NO_OUTPUT_BUFFERING', true);

// Include Moodle configuration (for root directory placement)
require_once('../config.php');

// Set JSON headers before any output
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Function to send JSON response and exit

// Security constants - must match JavaScript
define('SALT_PATTERN', 'X7mK9pQ3');
define('SALT_INTERVAL', 16);
define('REQUEST_TIMEOUT', 300); // 5 minutes in seconds

function deobfuscateImageData($obfuscatedData) {
    $saltPattern = SALT_PATTERN;
    $saltInterval = SALT_INTERVAL;
    $saltLength = strlen($saltPattern);
    
    // Remove salt pattern from data
    $cleaned = '';
    $chunkSize = $saltInterval + $saltLength;
    
    $pos = 0;
    while ($pos < strlen($obfuscatedData)) {
        // Extract the actual data chunk
        $chunk = substr($obfuscatedData, $pos, $saltInterval);
        $cleaned .= $chunk;
        
        // Skip the salt pattern
        $pos += $saltInterval + $saltLength;
    }
    
    return $cleaned;
}
function send_json_response($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    send_json_response(['success' => false, 'message' => 'Method not allowed']);
}

try {
    // Get raw POST data
    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        throw new Exception('No input data received');
    }
    
    // Decode JSON input
    $input = json_decode($rawInput, true);
    
    if ($input === null) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $username = trim($input['username'] ?? '');
    $imageData = $input['image_data'] ?? '';
    //$logintoken = $input['logintoken'] ?? '';
    $imageData = deobfuscateImageData($imageData);  
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    if (empty($imageData)) {
        throw new Exception('Image data is required');
    }
    
    // Verify the login token (CSRF protection)
    //require_sesskey($logintoken);
    
    // Step 1: Verify face with your face recognition service
   //$faceVerificationUrl = 'http://faceapi.teleuniv.in:8505/verify';
    $faceData = [
        'username' => $username,
        'image_data' => $imageData
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($faceData),
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);
    
    
    $faceResponse = file_get_contents($CFG->faceloginapi, false, $context);
    
    if ($faceResponse === false) {
        throw new Exception('Face verification service unavailable');
    }
    
    $faceResult = json_decode($faceResponse, true);
    
    if ($faceResult === null) {
        throw new Exception('Invalid response from face verification service');
    }
    
    if (!$faceResult['success']) {
        throw new Exception($faceResult['message'] ?? 'Face verification failed');
    }
    
    // Step 2: Get user from Moodle database
    $user = get_complete_user_data('username', $username);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Step 3: Check if user account is active
    if ($user->suspended || $user->deleted || !$user->confirmed) {
        throw new Exception('User account is not active');
    }
    
    // Step 4: Perform the login
    complete_user_login($user);
    

    
    // Determine redirect URL
    $redirecturl = optional_param('wantsurl', '', PARAM_LOCALURL);
    if (empty($redirecturl)) {
        $redirecturl = $CFG->wwwroot . '/';
    }
    
    send_json_response([
        'success' => true,
        'message' =>$faceResult['message'],
        'redirect_url' => $redirecturl,
        'user_fullname' => fullname($user)
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Face authentication error: ' . $e->getMessage());
    
    send_json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>