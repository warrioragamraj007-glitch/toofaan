<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('customactivity', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$activity = $DB->get_record('customactivity', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/customactivity/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($activity->name));


if (!has_capability('mod/customactivity:viewsubmissions', $context)) {
    $PAGE->set_pagelayout('popup'); // removes breadcrumb + top nav
}
echo $OUTPUT->header();

/* ================================================================
   ADMIN / TEACHER VIEW
================================================================ */
if (has_capability('mod/customactivity:viewsubmissions', $context)) {
    echo "<h2>Admin View: " . format_string($activity->name) . "</h2>";

    $questions = $DB->get_records('customactivity_questions',
        ['customactivityid' => $activity->id], 'qno ASC');

    if ($questions) {
        foreach ($questions as $q) {
            echo "<div style='padding:15px; margin:10px; border-left:4px solid #4CAF50; background:#f9f9f9;'>";
            echo "<strong>Q{$q->qno}:</strong> " . format_text($q->questiontext);
            if (!empty(trim($q->modelanswer))) {
                echo "<p><strong>Model Answer:</strong> " . format_text($q->modelanswer) . "</p>";
            }
            echo "</div>";
        }
    } else {
        echo $OUTPUT->notification("No questions added.", "warning");
    }

    echo $OUTPUT->single_button(
        new moodle_url('/mod/customactivity/submissions.php', ['id' => $cm->id]),
        "View All Submissions",
        'get'
    );

    echo $OUTPUT->footer();
    exit;
}

/* ================================================================
   STUDENT VIEW
================================================================ */

// Check if student already completed (finalgrade table)
$completed = $DB->get_record('customactivity_submissions', [
    'customactivityid' => $activity->id,
    'userid' => $USER->id,
     'questionid'       => 0
]);

if ($completed) {
    echo '<div style="max-width:800px;margin:60px auto;padding:40px;background:#d4edda;border:3px solid #28a745;border-radius:12px;text-align:center;">';
    echo '<h1 style="color:#155724;">Activity Completed!</h1>';
    echo '<h2 style="font-size:52px;color:#28a745;">Grade: ' . intval($completed->grade) . ' / 100</h2>';


    if ($completed->feedback) {
        echo '<div style="margin-top:30px;background:white;padding:25px;border-radius:10px;">';
        echo '<strong>AI Feedback:</strong><br>' . format_text($completed->feedback);
        echo '</div>';
    }

    echo '</div>';
    echo $OUTPUT->footer();
    exit;
}

// Load questions
$questions = $DB->get_records('customactivity_questions',
    ['customactivityid' => $activity->id], 'qno ASC');

if (empty($questions)) {
    echo $OUTPUT->notification("No questions added.", "warning");
    echo $OUTPUT->footer();
    exit;
}

$questionlist = array_values($questions);
$total = count($questionlist);

// Determine current question
$currentindex = optional_param('qindex', 0, PARAM_INT);
$currentindex = max(0, min($currentindex, $total - 1));
$question = $questionlist[$currentindex];

// Load student's temp save
$submission = $DB->get_record('customactivity_submissions', [
    'customactivityid' => $activity->id,
    'questionid'       => $question->id,
    'userid'           => $USER->id
]);

$tempsave  = $submission ? $submission->tempsave : "";
$timespent = $submission ? (int)$submission->timespent : 0;
?>

<h2 style="text-align:center;margin-top:20px;"><?php echo format_string($activity->name); ?></h2>

<div style="max-width:900px;margin:0 auto;padding:20px;">

    <div style="text-align:center;margin:30px 0;font-size:22px;color:#2c3e50;">
        <strong>Question <?php echo $currentindex + 1; ?> of <?php echo $total; ?></strong>
    </div>

    <div style="padding:30px;background:white;border-left:6px solid #3498db;border-radius:10px;margin-bottom:30px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <strong style="font-size:20px;">Q<?php echo $question->qno ?>:</strong><br><br>
        <?php echo format_text($question->questiontext); ?>
    </div>

    <form method="post" action="submit.php" id="answerForm">

        <textarea id="answer" name="answer"
                  style="width:100%;height:250px;padding:20px;font-size:17px;border:2px solid #bdc3c7;border-radius:10px;resize:vertical;"
                  placeholder="Type your answer here..."><?php echo s($tempsave); ?></textarea>

        <input type="hidden" name="id" value="<?php echo $cm->id; ?>">
        <input type="hidden" name="qindex" value="<?php echo $currentindex; ?>">
        <input type="hidden" name="timespent" id="timespent" value="<?php echo $timespent; ?>">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

        <div style="text-align:center;margin:25px 0;font-size:19px;color:#7f8c8d;">
            Time spent on this question: <strong><span id="timer">0</span> seconds</strong>
        </div>

        <div style="text-align:center;margin-top:40px;">

            <?php if ($currentindex > 0): ?>
                <a href="?id=<?php echo $cm->id; ?>&qindex=<?php echo $currentindex - 1; ?>"
                   class="btn btn-outline-secondary"
                   style="padding:14px 40px;font-size:18px;margin:0 15px;">
                    Previous
                </a>
            <?php endif; ?>

            <?php if ($currentindex < $total - 1): ?>
                <button type="submit" name="next" value="1"
                        class="btn btn-primary btn-lg"
                        style="padding:16px 60px;font-size:20px;margin:0 15px;">
                    Save & Next →
                </button>
            <?php else: ?>
                            <button type="submit" name="finalsubmit" value="1"
                class="btn btn-success btn-lg"
                style="padding:12px 40px;font-size:18px;margin:0 10px;
                    background:#27ae60;border:none;
                    box-shadow:0 4px 10px rgba(0,0,0,0.2);">
                Submit & Get Grade
            </button>
            <?php endif; ?>

        </div>
    </form>
</div>

<script>
let timeSpent = <?php echo $timespent; ?>;
setInterval(() => {
    timeSpent++;
    document.getElementById("timer").innerText = timeSpent;
}, 1000);

// Auto-save every 5 sec
setInterval(() => {
    const txt = document.getElementById("answer").value;
    fetch("tempsave.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=<?php echo $cm->id; ?>&q=<?php echo $question->id; ?>&txt=" +
              encodeURIComponent(txt) + "&sesskey=<?php echo sesskey(); ?>"
    });
}, 5000);

document.getElementById("answerForm").addEventListener("submit", function () {
    document.getElementById("timespent").value = timeSpent;
});
</script>

<?php echo $OUTPUT->footer(); ?>
