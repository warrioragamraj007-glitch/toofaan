<?php
require('../../config.php');
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
global $CFG;
global $DB;

    // Check if it's an AJAX request
    if (isset($_POST['selected_course']) && isset($_POST['selected_topic'])) {
        $courseid = (int)$_POST['selected_course'];
        $topicid = (int)$_POST['selected_topic'];
    
        // Assuming 'resource' is the module ID for the file activity in Moodle
        $file_module_id = $DB->get_field('modules', 'id', array('name' => 'resource'));
    
        // Fetch file activities for the selected topic
        $fileActivities = get_array_of_file_activities_in_topic($courseid, $topicid);
        $fileDataArray = array();
    
        if (!empty($fileActivities)) {
            foreach ($fileActivities as $activity) {
                
                if (!is_activity_deletion_in_progress($activity->id)) {
                    $resource_name = get_resource_name($activity->instance);
                    $visible = $DB->get_field('course_modules', 'visible', ['id' => $activity->id]);
                    $fileData = array(
                        'fileName' => $resource_name,
                        'downloadLink' => generate_download_url($activity->id),
                        'editLink' => generate_edit_url($activity->id),
                        'fileId' => $activity->id,
                        'isVisible' => (bool) $visible
                    );
                $fileDataArray[] = $fileData;
                
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($fileDataArray);
        exit;
    } elseif (isset($_POST['selected_course'])) {
        $courseid = (int)$_POST['selected_course'];
        $secQuery = "SELECT * FROM {course_sections} WHERE course = :courseid AND name IS NOT NULL";
        $sections_obj = $DB->get_records_sql($secQuery, array('courseid' => $courseid));
        $topics = "<option value='0'>Select Topic</option>";
        foreach ($sections_obj as $section) {
            if (!empty($section->name)) {
                $sectionn=$section->section;
                //$topics .= "<option value='" . $section->id . "'>" . $section->name . "</option>";
                $topics .= "<option value='" . $section->id . "' data-section='" . $sectionn . "'>" . $section->name . "</option>";
            }
        }
        echo $topics;
    } else {
        echo 'No course selected.';
    }
    function get_array_of_file_activities_in_topic($courseid, $topicid)
    {
        global $DB;
        $file_module_id = $DB->get_field('modules', 'id', array('name' => 'resource'));
        $sql = "SELECT cm.id, m.name, cm.instance
                FROM {course_modules} cm
                JOIN {modules} m ON cm.module = m.id
                JOIN {course_sections} cs ON cm.section = cs.id
                WHERE cm.course = ? AND m.id = ? AND cs.course = ? AND cs.id = ?";
    
        return $DB->get_records_sql($sql, array($courseid, $file_module_id, $courseid, $topicid));
    }
    function get_resource_name($resourceid)
    {
        global $DB;
    
        $sql = "SELECT name
                FROM {resource}
                WHERE id = ?";
    
        return $DB->get_field_sql($sql, array($resourceid));
    }
    
    function is_activity_deletion_in_progress($activity_id)
    {
        global $DB;
        // Check if deletioninprogress is 1
        return $DB->record_exists('course_modules', ['id' => $activity_id, 'deletioninprogress' => 1]);
    }
    
    function generate_download_url($activity_id)
    {
        global $CFG;
        return $CFG->wwwroot . '/mod/resource/view.php?id=' . $activity_id; // Modify this based on your specific Moodle setup
    }
    
    function generate_edit_url($activity_id)
    {
        global $CFG;
        $edit_url = $CFG->wwwroot . "/course/modedit.php?update={$activity_id}&return=0&sr=0";
        
        return $edit_url;  
    }