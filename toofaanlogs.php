<?php
function logMessage($message, $logDir = '/var/www/html/toofaan/toofaan_logs/') {
    // Ensure directory exists
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Build filename based on date
    $date = date('Y-m-d');
    $logFile = $logDir . "toofaan{$date}.log";

    // Format log entry
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message\n";

    // Append to daily log file
    file_put_contents($logFile, $entry, FILE_APPEND);
}
?>
