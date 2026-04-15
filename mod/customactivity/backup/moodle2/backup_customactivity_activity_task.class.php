<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/customactivity/backup/moodle2/backup_customactivity_stepslib.php');

class backup_customactivity_activity_task extends backup_activity_task {
    protected function define_my_settings() {}
    protected function define_my_steps() {
        $this->add_step(new backup_customactivity_activity_structure_step('customactivity_structure', 'customactivity.xml'));
    }
    public static function encode_content_links($content) {
        global $CFG;
        $pattern = '/(' . preg_quote($CFG->wwwroot, '/') . '\/mod\/customactivity\/view.php\?id=)([0-9]+)/';
        $replacement = '$@CUSTOMACTIVITYVIEWBYID*$2@$';
        $content = preg_replace($pattern, $replacement, $content);
        return $content;
    }
}
