<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Inject the live notifications script ONLY for students
 */
function local_livenotify_extend_settings_navigation(settings_navigation $settingsnav, context $context = null) {
    global $PAGE, $USER, $DB;

    // 1. Must be logged in and not guest
    if (!isloggedin() || isguestuser()) {
        return;
    }

    // 2. Must be enrolled in at least one course
    $courses = enrol_get_users_courses($USER->id, true);
    if (empty($courses)) {
        return;
    }

    // 3. Check if user has the 'student' archetype role in ANY enrolled course
    $studentRoleId = $DB->get_field('role', 'id', ['archetype' => 'student']);
    if (!$studentRoleId) {
        return; // No student role defined in the site
    }

    $isStudent = false;
    foreach ($courses as $course) {
        $coursecontext = context_course::instance($course->id);
        if (user_has_role_assignment($USER->id, $studentRoleId, $coursecontext->id)) {
            $isStudent = true;
            break;
        }
    }

    // If not a student in any course → do NOT load the script
    if (!$isStudent) {
        return;
    }

    // Only real students reach here → load the notifications
    $PAGE->requires->js('/local/livenotify/js/livenotifications.js.php');
}