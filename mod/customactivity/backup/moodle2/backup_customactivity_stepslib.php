<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/backup_activity_structure_step.class.php');

class backup_customactivity_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        $customactivity = new backup_nested_element('customactivity', ['id'], [
            'name',
            'intro',
            'introformat',
            'question',
            'correctanswer',
            'maxattempts',
            'timecreated',
            'timemodified'
        ]);

        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'userid',
            'answer',
            'iscorrect',
            'timecreated'
        ]);

        $customactivity->add_child($submissions);
        $submissions->add_child($submission);

        $customactivity->set_source_table('customactivity', ['id' => backup::VAR_ACTIVITYID]);

        $submission->set_source_table('customactivity_submissions', [
            'customactivityid' => backup::VAR_PARENTID
        ]);

        $customactivity->annotate_files('mod_customactivity', 'intro', null);

        $submission->annotate_ids('user', 'userid');

        return $this->prepare_activity_structure($customactivity);
    }
}
