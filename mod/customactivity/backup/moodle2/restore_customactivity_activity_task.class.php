<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/customactivity/backup/moodle2/restore_customactivity_stepslib.php');

class restore_customactivity_activity_task extends restore_activity_task {
    protected function define_my_settings() {}
    protected function define_my_steps() {
        $this->add_step(new restore_customactivity_activity_structure_step('customactivity_structure', 'customactivity.xml'));
    }
    public static function define_decode_contents() {
        return [ new restore_decode_content('customactivity', ['intro'], 'customactivity') ];
    }
    public static function define_decode_rules() {
        return [ new restore_decode_rule('CUSTOMACTIVITYVIEWBYID', '/mod/customactivity/view.php?id=$1') ];
    }
}
