<?php
require_once('../../config.php');
require_login();

$cid    = required_param('cid', PARAM_INT);
$aid    = required_param('aid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

global $DB;

if (!$DB->record_exists('webinar_attendance', [
    'cid' => $cid,
    'aid' => $aid,
    'userid' => $userid
])) {
    $record = (object)[
        'cid' => $cid,
        'aid' => $aid,
        'userid' => $userid,
        'attendance' => 1,
        'updatedon' => time()
    ];
    $DB->insert_record('webinar_attendance', $record);
}

echo json_encode(['status' => 'ok']);
exit;
