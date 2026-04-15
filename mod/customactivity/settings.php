<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // OpenAI API Key – CORRECT CLASS NAME
    $settings->add(new admin_setting_configpasswordunmask(
        'mod_customactivity/openai_api_key',
        'OpenAI API Key',
        'Enter your OpenAI API key (starts with sk-proj-...). Required for AI Smart Grading (o3-mini). Never share this key!',
        ''
    ));

    // Default AI evaluation limit
    $settings->add(new admin_setting_configtext(
        'mod_customactivity/default_ai_eval_limit',
        'Default AI Grading Limit per Activity',
        'How many times students can use AI grading in one activity (0 = unlimited)',
        '10',
        PARAM_INT
    ));
}