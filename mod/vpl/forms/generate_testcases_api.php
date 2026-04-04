<?php
require_once('../../../config.php');
require_login();
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$cmid = $data['vplid'];
$code = $data['code'];
$count = intval($data['count']);
global $DB;
$current = $DB->get_field_sql(
    "SELECT testcases_generate_limit FROM {course_modules} WHERE id = :cmid",
    ['cmid' => $cmid]
);

// Default to 3 if null
if (is_null($current)) {
    $current = 3;
}

if ($current <= 0) {
    echo json_encode([
        'success' => false,
        'error' => "You’ve reached the maximum number of test case generations allowed for this activity.",
        'remaining' => 0
    ]);
    exit;
}
// Decrement and save
// Decrement and update
$newlimit = $cm->testcases_generate_limit - 1;

$DB->execute(
    "UPDATE {course_modules} 
        SET testcases_generate_limit = :newlimit 
      WHERE id = :cmid",
    ['newlimit' => $newlimit, 'cmid' => $cmid]
);


$apiKey = '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'; // Load from config/env in production
// $prompt = <<<EOT
// You are a strict and intelligent test case generator for a Moodle-based VPL evaluation system.

// 🎯 Your job:
// - Understand the given problem or code
// - Identify the logic using ANY example, including:
//   - "Sample Input", "Input", "Example", "Example Input", "Sample Input-1", "Input Format", etc.
//   - You must handle variations in headers, spacing, dashes, etc.
// - Internally simulate the logic to compute VERIFIED outputs
// - Generate exactly $count test cases using the required format

// ---

// ✅ TEST CASE FORMAT:
// case=1  
// input=...  
// output=...

// case=2  
// input=...  
// output=...

// ---

// 🔒 STRICT RULES:
// 1. DO NOT include code, explanations, markdown, or commentary — only test cases.
// 2. Parse sample inputs from ANY clear layout (even if it's labeled "Input-1" or formatted with lines).
// 3. Input values must match the actual program input (stdin format).
// 4. Output values must match what the correct program would print.
// 5. You MUST simulate and validate output for each test case — do NOT guess.
// 6. If the problem truly has no usable logic or examples, then respond ONLY with:
// ⚠️ Unable to generate test cases: Problem statement or sample input/output is missing or invalid.

// ---

// Here is the problem or code:
// $code
// EOT;

$prompt = <<<EOT
You are a highly accurate test case generator for a Moodle-based VPL evaluation system.

## Objective
- Analyze the provided problem statement or code.
- Derive the exact logic required to solve it.
- Internally execute the logic step by step — do not guess.
- Produce exactly $count fully validated test cases.

## Output format (no extra text, no explanations):
case=1
input=<exact input as stdin>
output=<exact output as program would print>

case=2
input=<exact input as stdin>
output=<exact output as program would print>

...

## Non-negotiable rules:
1. DO NOT provide explanations, comments, or code.
2. Every output must match the true execution result of the provided code.
3. Only generate inputs that strictly follow the program’s expected input format.
4. Never assume logic — simulate or calculate it fully before writing outputs.
5. If the problem statement does not have enough information to derive correct outputs, reply exactly:
⚠️ Unable to generate test cases: Problem statement or sample input/output is missing or invalid.

## Problem or code to analyze:
$code
EOT;



$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $apiKey"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
'model' => 'o3-mini', 
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
   // 'temperature' => 0.3
]));

$response = curl_exec($ch);
curl_close($ch);

// if ($response) {
//     $result = json_decode($response, true);
//     echo $result['choices'][0]['message']['content'] ?? 'No response';
// } else {
//     echo 'Failed to call API.';
// }

$output = '';
if ($response) {
    $result = json_decode($response, true);
    $output = $result['choices'][0]['message']['content'] ?? 'No response';
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to call OpenAI API.', 'remaining' => $current]);
    exit;
}
// Decrement attempt limit
$newlimit = $current - 1;
$DB->execute(
    "UPDATE {course_modules} SET testcases_generate_limit = :newlimit WHERE id = :cmid",
    ['newlimit' => $newlimit, 'cmid' => $cmid]
);

// Return success
echo json_encode([
    'success' => true,
    'output' => $output,
    'remaining' => $newlimit
]);