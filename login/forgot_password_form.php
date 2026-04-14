<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Forgot password form using username + parent phone number.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once('lib.php');

class login_forgot_password_form extends moodleform {

    /**
     * Define the forgot password form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->setDisableShortforms(true);

        // Hide "Required" text at bottom and fix label width for both step 1 and step 2.
       $mform->addElement('html', '
<style>
    .mform .form-group.row .col-md-3,
    .mform .form-group.row .col-form-label,
    .mform .fitem .fitemtitle {
        width: 220px !important;
        min-width: 150px !important;
        max-width: 1500px !important;
        flex: 0 0 150px !important;
    }

    .mform .form-group.row .col-md-3 label,
    .mform .form-group.row .col-form-label,
    .mform .fitem .fitemtitle label {
        white-space: nowrap !important;
        word-break: normal !important;
    }

    .mform .fitem .felement,
    .mform .form-group.row .felement,
    .mform .form-group.row .col-md-9 {
        flex: 0 0 calc(100% - 150px) !important;
        max-width: calc(100% - 150px) !important;
    }

    .mform .required {
        display: none !important;
    }
</style>
');

        $step = isset($this->_customdata['step']) ? (int)$this->_customdata['step'] : 1;
        $userid = isset($this->_customdata['userid']) ? (int)$this->_customdata['userid'] : 0;
        $directreset = !empty($this->_customdata['directreset']);
        $verified = !empty($this->_customdata['verified']);

        if ($step === 1) {
            $mform->addElement('header', 'verifyuser', 'Verify Student');

            $mform->addElement('text', 'username', get_string('username'), ['size' => 25]);
            $mform->setType('username', PARAM_RAW_TRIMMED);
            $mform->addRule('username', get_string('required'), 'required', null, 'client');

            $mform->addElement('text', 'parentphone', 'Parent Phone', [
                'maxlength' => 20,
                'size' => 25,
                'placeholder' => 'Enter full parent phone number'
            ]);
            $mform->setType('parentphone', PARAM_RAW_TRIMMED);
            $mform->addRule('parentphone', 'Parent phone number is required', 'required', null, 'client');

            $mform->addElement('hidden', 'step', 1);
            $mform->setType('step', PARAM_INT);

            $mform->addElement('submit', 'submitbutton', 'Verify');
        }

        if ($step === 2) {
            $mform->addElement('header', 'resetpasswordheader', 'Reset Password');

            // Add here
    $mform->addElement('html', '
    <div class="alert alert-info">
    <strong>Password requirements:</strong><br>
    • Minimum 8 characters<br>
    • At least 1 uppercase letter<br>
    • At least 1 special character ( *, -, # )
    </div>
    ');

            if ($verified) {
                $mform->addElement('html', html_writer::div(
                    'Verification successful. Please set a new password.',
                    'alert alert-success'
                ));
            }

            if ($directreset) {
                $mform->addElement('text', 'username', get_string('username'), ['size' => 25]);
                $mform->setType('username', PARAM_RAW_TRIMMED);
                $mform->addRule('username', get_string('required'), 'required', null, 'client');

                $mform->addElement('text', 'parentphone', 'Parent Phone', [
                    'maxlength' => 20,
                    'size' => 25,
                    'placeholder' => 'Enter full parent phone number'
                ]);
                $mform->setType('parentphone', PARAM_RAW_TRIMMED);
                $mform->addRule('parentphone', 'Parent phone number is required', 'required', null, 'client');
            }

            $mform->addElement('password', 'newpassword', 'New Password');
            $mform->setType('newpassword', PARAM_RAW);
            $mform->addRule('newpassword', 'New password is required', 'required', null, 'client');

            $mform->addElement('password', 'confirmpassword', 'Confirm Password');
            $mform->setType('confirmpassword', PARAM_RAW);
            $mform->addRule('confirmpassword', 'Confirm password is required', 'required', null, 'client');

            $mform->addElement('hidden', 'step', 2);
            $mform->setType('step', PARAM_INT);

            $mform->addElement('hidden', 'userid', $userid);
            $mform->setType('userid', PARAM_INT);

            $mform->addElement('submit', 'submitbutton', 'Reset Password');
        }
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $step = isset($data['step']) ? (int)$data['step'] : 1;
        $directreset = !empty($this->_customdata['directreset']);

        if ($step === 1 || ($step === 2 && $directreset)) {
            $parentphone = trim($data['parentphone'] ?? '');
            $normalizedparentphone = preg_replace('/\D+/', '', $parentphone);

            if ($parentphone === '') {
                $errors['parentphone'] = 'Parent phone number is required';
            } else if ($normalizedparentphone === '' || strlen($normalizedparentphone) < 5) {
                $errors['parentphone'] = 'Enter a valid parent phone number with at least 5 digits';
            }

            if (empty(trim($data['username'] ?? ''))) {
                $errors['username'] = get_string('required');
            }
        }

        if ($step === 2) {
            $newpassword = $data['newpassword'] ?? '';
            $confirmpassword = $data['confirmpassword'] ?? '';

            if ($newpassword === '') {
                $errors['newpassword'] = 'New password is required';
            }

            if ($confirmpassword === '') {
                $errors['confirmpassword'] = 'Confirm password is required';
            }

            if ($newpassword !== '' && $confirmpassword !== '' && $newpassword !== $confirmpassword) {
                $errors['confirmpassword'] = 'Passwords do not match';
            }
        }

        return $errors;
    }
}