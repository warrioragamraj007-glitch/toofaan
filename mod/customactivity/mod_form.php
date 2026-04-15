<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_customactivity_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG;
        $mform = $this->_form;

        // Activity name
        $mform->addElement('text', 'name', get_string('customactivityname', 'customactivity'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        // Max attempts
        $mform->addElement('text', 'maxattempts', get_string('maxattempts', 'customactivity'), ['size' => 4]);
        $mform->setType('maxattempts', PARAM_INT);
        $mform->setDefault('maxattempts', 3);

        // AI grading limit
        $mform->addElement('text', 'ai_eval_limit', get_string('ai_eval_limit', 'customactivity'), ['size' => 6]);
        $mform->setType('ai_eval_limit', PARAM_INT);
        $mform->setDefault('ai_eval_limit', 0);

        // ===============================
        // Repeatable Questions
        // ===============================
        $repeatarray = [];

        // Question text (LABEL contains {no})
        $repeatarray[] = $mform->createElement(
            'textarea',
            'questiontext',
            get_string('question', 'customactivity') . ' {no}',
            'rows="5" cols="80"'
        );

        // Model answer
        $repeatarray[] = $mform->createElement(
            'textarea',
            'modelanswer',
            get_string('modelanswer', 'customactivity'),
            'rows="3" cols="80"'
        );

        // Hidden question ID
        $repeatarray[] = $mform->createElement('hidden', 'questionid', 0);

        $repeatoptions = [];
        $repeatoptions['questiontext']['type'] = PARAM_RAW;
        $repeatoptions['modelanswer']['type']  = PARAM_RAW;
        $repeatoptions['questionid']['type']   = PARAM_INT;

        $this->repeat_elements(
            $repeatarray,
            1, // initial count
            $repeatoptions,
            'question_repeats',
            'add_question_button',
            1,
            get_string('addanotherquestion', 'customactivity'),
            true
        );

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Populate existing questions while editing
     */
    public function set_data($defaultvalues) {
        global $DB;

        if (!empty($defaultvalues->instance)) {
            $questions = $DB->get_records(
                'customactivity_questions',
                ['customactivityid' => $defaultvalues->instance],
                'qno ASC'
            );

            $i = 0;
            foreach ($questions as $q) {
                $defaultvalues->questiontext[$i] = $q->questiontext;
                $defaultvalues->modelanswer[$i]  = $q->modelanswer;
                $defaultvalues->questionid[$i]   = $q->id;
                $i++;
            }

            $defaultvalues->question_repeats = max(1, count($questions));
        }

        parent::set_data($defaultvalues);
    }

    /**
     * Validation
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $hasquestion = false;
        if (!empty($data['questiontext'])) {
            foreach ($data['questiontext'] as $qt) {
                if (trim($qt) !== '') {
                    $hasquestion = true;
                    break;
                }
            }
        }

        if (!$hasquestion) {
            $errors['questiontext[0]'] = get_string('required');
        }

        return $errors;
    }
}
