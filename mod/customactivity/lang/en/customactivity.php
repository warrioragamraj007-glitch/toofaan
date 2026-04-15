<?php
defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Custom Activity';
$string['modulenameplural'] = 'Custom Activities';
$string['pluginname'] = 'Custom Activity';
$string['pluginadministration'] = 'Custom Activity administration';

$string['modulename_help'] = 'An advanced activity where teachers can add multiple open-ended questions. All answers are automatically graded by AI with instant scores (0–100) and detailed feedback.';

// Capabilities
$string['customactivity:addinstance'] = 'Add a new Custom Activity';
$string['customactivity:view'] = 'View Custom Activity';
$string['customactivity:submit'] = 'Submit answers';
$string['customactivity:viewsubmissions'] = 'View all student submissions';

// Activity settings (main form)
$string['customactivityname'] = 'Name';
$string['customactivityname_help'] = 'The name of the activity shown on the course page.';

$string['intro'] = 'Description';
$string['intro_help'] = 'Optional introduction or instructions shown above the questions.';

$string['maxattempts'] = 'Maximum attempts per question';
$string['maxattempts_help'] = 'How many times a student can submit an answer for each question. Set to 0 for unlimited attempts.';

$string['ai_eval_limit'] = 'AI grading limit';
$string['ai_eval_limit_help'] = 'Maximum number of times AI can grade answers in this activity (all students & questions combined). Set to 0 for unlimited.';



// Multiple questions section
$string['questions'] = 'Questions';
$string['addquestion'] = 'Add another question';
$string['addquestions'] = 'Add questions';
$string['questiontext'] = 'Question';
$string['modelanswer'] = 'Model answer (optional)';
$string['modelanswer_help'] = 'Provide an example of a perfect answer. This helps the AI grade more accurately and give better feedback. Students will not see this.';
$string['noquestions'] = 'No questions added yet. Click "Add another question" to begin.';
$string['questionnumber'] = 'Question {$a}';
$string['removequestion'] = 'Remove question';
$string['questiondeleted'] = 'Question deleted';

// Student view
$string['submitanswer'] = 'Submit answer';
$string['nextquestion'] = 'Next question';
$string['previousquestion'] = 'Previous question';
$string['saveandcontinue'] = 'Save and continue';
$string['completelater'] = 'Finish later';
$string['activitycomplete'] = 'All questions completed!';
$string['attemptsused'] = 'Attempts used: {$a->used} of {$a->max}';
$string['noattemptsleft'] = 'No attempts remaining for this question.';
$string['youranswerwas'] = 'Your answer:';
$string['grade'] = 'Score: {$a}/100';
$string['feedback'] = 'AI Feedback';
$string['tryagain'] = 'Try again';

// Teacher / grading
$string['viewsubmissions'] = 'View submissions';
$string['submissions'] = 'Submissions';
$string['student'] = 'Student';
$string['question'] = 'Question';
$string['answer'] = 'Answer';
$string['score'] = 'Score';
$string['evaluatedon'] = 'Graded on';
$string['regradewithai'] = 'Re-grade with AI';
$string['manualgrade'] = 'Manual grade';

// Export
$string['downloadcsv'] = 'Export all answers as CSV';
$string['exportcsv'] = 'Download CSV';

// Admin settings
$string['openai_api_key'] = 'OpenAI API Key';
$string['openai_api_key_desc'] = 'Enter your OpenAI API key (starts with sk-...). Keep this secret!';
$string['default_ai_eval_limit'] = 'Default AI grading limit';
$string['default_ai_eval_limit_desc'] = 'Default value used when creating new activities (0 = unlimited).';

// Messages
$string['ai_eval_limit_reached'] = 'AI grading limit reached for this activity. New submissions will not be graded automatically.';
$string['gradinginprogress'] = 'AI is grading your answer...';
$string['gradedbyai'] = 'Graded by AI';

// Privacy API
$string['privacy:metadata:customactivity_submissions'] = 'Information about student answers in Custom Activity.';
$string['privacy:metadata:customactivity_submissions:userid'] = 'The ID of the user who submitted the answer.';
$string['privacy:metadata:customactivity_submissions:questionid'] = 'The question that was answered.';
$string['privacy:metadata:customactivity_submissions:answer'] = 'The text of the student\'s answer.';
$string['privacy:metadata:customactivity_submissions:grade'] = 'The score given by AI (0–100).';
$string['privacy:metadata:customactivity_submissions:feedback'] = 'Detailed feedback from the AI.';
$string['privacy:metadata:customactivity_submissions:attemptno'] = 'Which attempt this was.';
$string['privacy:metadata:customactivity_submissions:timecreated'] = 'When the answer was submitted.';