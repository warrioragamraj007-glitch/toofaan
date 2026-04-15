<?php
namespace mod_customactivity\event;
defined('MOODLE_INTERNAL') || die();

class submission_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'customactivity';
    }

    public static function get_name() {
        return get_string('eventsubmissioncreated', 'mod_customactivity');
    }

    public function get_description() {
        return 'User with id '. $this->userid .' created a submission for customactivity with id '. $this->objectid;
    }

    public function get_url() {
        return new \moodle_url('/mod/customactivity/view.php', ['id' => $this->contextinstanceid]);
    }
}
