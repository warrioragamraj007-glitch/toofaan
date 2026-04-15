<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/restore_activity_structure_step.class.php');

class restore_customactivity_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = [];
        $paths[] = new restore_path_element('customactivity', '/activity/customactivity');
        $paths[] = new restore_path_element('customactivity_submission', '/activity/customactivity/submissions/submission');

        return $this->prepare_activity_structure($paths);
    }

    protected function process_customactivity($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newid = $DB->insert_record('customactivity', $data);
        $this->apply_activity_instance($newid);
    }

    protected function process_customactivity_submission($data) {
        global $DB;
        $data = (object)$data;
        $data->customactivityid = $this->get_new_parentid('customactivity');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $DB->insert_record('customactivity_submissions', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_customactivity', 'intro', null);
    }
}
