<?php
require('../config.php');
require_once($CFG->dirroot . '/login/forgot_password_form.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/user/lib.php');

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     echo '<pre>';
//     echo "POST DATA:\n";
//     print_r($_POST);
//     echo '</pre>';
// }
if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/'));
}

$context = context_system::instance();
$PAGE->set_url('/login/forgot_password.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

$site = get_site();
$PAGE->set_title($site->fullname . ': Forgot password');
$PAGE->set_heading($site->fullname);

/**
 * Keep digits only.
 *
 * @param string $phone
 * @return string
 */
function local_normalize_phone(string $phone): string {
    return preg_replace('/\D+/', '', trim($phone));
}

/**
 * Return only last N digits.
 *
 * @param string $phone
 * @param int $n
 * @return string
 */
function local_last_n_phone_digits(string $phone, int $n = 5): string {
    $digits = local_normalize_phone($phone);

    if ($digits === '') {
        return '';
    }

    return strlen($digits) <= $n ? $digits : substr($digits, -$n);
}

/**
 * Hint format like xxx48.
 *
 * @param string $phone
 * @return string
 */
function local_mask_phone_hint(string $phone): string {
    $digits = local_last_n_phone_digits($phone, 5);

    if ($digits === '') {
        return '';
    }

    return 'xxx' . substr($digits, -2);
}

// Posted values.
$postedstep = optional_param('step', 0, PARAM_INT);
$posteduserid = optional_param('userid', 0, PARAM_INT);
$submitbutton = optional_param('submitbutton', '', PARAM_RAW_TRIMMED);
$postednewpassword = optional_param('newpassword', '', PARAM_RAW);
$postedconfirmpassword = optional_param('confirmpassword', '', PARAM_RAW);
$verified = optional_param('verified', 0, PARAM_INT);

// If reset form submitted, force step 2.
if ($posteduserid > 0 && $submitbutton === 'Reset Password') {
    $postedstep = 2;
}

if ($posteduserid > 0 && $postednewpassword !== '' && $postedconfirmpassword !== '') {
    $postedstep = 2;
}

// Fresh open from Lost password link should always start from verification form.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $postedstep !== 2) {
    unset($SESSION->verified_forgot_password_userid);
}

$sessionuserid = !empty($SESSION->verified_forgot_password_userid)
    ? (int)$SESSION->verified_forgot_password_userid
    : 0;

// Decide which form to show.
if ($postedstep === 2 || $sessionuserid > 0) {
    $step = 2;
    $userid = $posteduserid ?: $sessionuserid;
} else {
    $step = 1;
    $userid = 0;
}

$mform = new login_forgot_password_form(null, [
    'step' => $step,
    'userid' => $userid,
    'verified' => $verified
]);

if ($mform->is_cancelled()) {
    unset($SESSION->verified_forgot_password_userid);
    redirect(new moodle_url('/login/index.php'));
}

if ($data = $mform->get_data()) {
    if ($submitbutton === 'Reset Password') {
        $data->step = 2;
    } elseif ($submitbutton === 'Verify') {
        $data->step = 1;
    }

    // Safety: if reset fields are posted, treat as step 2.
    if (!empty($data->userid) && !empty($data->newpassword) && !empty($data->confirmpassword)) {
        $data->step = 2;
    }

    // STEP 1: Verify username + parent phone.
    if ((int)$data->step === 1) {
        $username = trim(core_text::strtolower($data->username));
        $parentphone = trim($data->parentphone);

        $user = $DB->get_record('user', [
            'username' => $username,
            'deleted' => 0,
            'suspended' => 0,
            'mnethostid' => $CFG->mnet_localhost_id
        ]);

        if (!$user) {
            echo $OUTPUT->header();
            echo $OUTPUT->notification('Invalid username or parent phone number.', 'notifyproblem');
            $form = new login_forgot_password_form(null, ['step' => 1]);
            $form->display();
            echo $OUTPUT->footer();
            exit;
        }

        $submittedphone = local_last_n_phone_digits($parentphone, 5);
        $dbphone = local_last_n_phone_digits((string)$user->phone2, 5);

        if ($dbphone !== '' && $submittedphone !== '' && $submittedphone === $dbphone) {
            $SESSION->verified_forgot_password_userid = (int)$user->id;
            redirect(new moodle_url('/login/forgot_password.php', [
                'step' => 2,
                'userid' => (int)$user->id,
                'verified' => 1
            ]));
        } else {
            $maskedphone = local_mask_phone_hint($dbphone);

            echo $OUTPUT->header();
            if ($maskedphone !== '') {
              echo $OUTPUT->notification(
    'Invalid username or parent phone number.<br>
     <strong>Hint:</strong> Registered parent phone ends with <strong>' . s($maskedphone) . '</strong>',
    'notifyproblem'
);
            } else {
                echo $OUTPUT->notification(
                    'Invalid username or parent phone number.',
                    'notifyproblem'
                );
            }

            $form = new login_forgot_password_form(null, ['step' => 1]);
            $form->display();
            echo $OUTPUT->footer();
            exit;
        }
    }

    // STEP 2: Reset password.
    if ((int)$data->step === 2) {
        $userid = (int)$data->userid;

        if (empty($SESSION->verified_forgot_password_userid) ||
            (int)$SESSION->verified_forgot_password_userid !== $userid) {
            echo $OUTPUT->header();
            echo $OUTPUT->notification('Session expired or invalid access.', 'notifyproblem');
            $form = new login_forgot_password_form(null, ['step' => 1]);
            $form->display();
            echo $OUTPUT->footer();
            exit;
        }

        $user = $DB->get_record('user', [
            'id' => $userid,
            'deleted' => 0,
            'suspended' => 0
        ], '*', MUST_EXIST);

        $newpassword = trim($data->newpassword);
        $errmsg = '';

        if (!check_password_policy($newpassword, $errmsg)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(
        'Please enter a password that matches the below requirements.',
        'notifyproblem'
    );
    $form = new login_forgot_password_form(null, [
        'step' => 2,
        'userid' => $userid,
        'verified' => 1
    ]);
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

        update_internal_user_password($user, $newpassword);

        unset($SESSION->verified_forgot_password_userid);

        echo $OUTPUT->header();
      redirect(
    new moodle_url('/login/index.php'),
    'Password has been reset successfully.',
    2
);
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();