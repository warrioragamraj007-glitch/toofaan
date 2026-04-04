<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit test cases' file
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/../vpl.class.php');
require_once(dirname(__FILE__).'/../editor/editor_utility.php');

require_login();
$id = required_param( 'id', PARAM_INT );

$vpl = new mod_vpl( $id );
// var_dump($vpl);
$vpl->prepare_page( 'forms/testcasesfile.php', [
        'id' => $id,
]);

$vpl->require_capability( VPL_MANAGE_CAPABILITY );
// $cm = $DB->get_record('course_modules', ['id' => $id], '*', MUST_EXIST);
$remaining_attempts = $DB->get_field_sql(
    "SELECT testcases_generate_limit FROM {course_modules} WHERE id = :cmid",
    ['cmid' => $id]
);
// var_dump($remaining_attempts);
$options = [];
$options['restrictededitor'] = false;
$options['save'] = true;
$options['run'] = false;
$options['debug'] = false;
$options['evaluate'] = false;
$options['ajaxurl'] = "testcasesfile.json.php?id={$id}&action=";
$options['download'] = "../views/downloadexecutionfiles.php?id={$id}";
$options['resetfiles'] = false;
$options['minfiles'] = 1;
$options['maxfiles'] = 1;
$options['saved'] = true;
$options['readOnlyFiles'] = [];

vpl_editor_util::generate_requires($vpl, $options);
$vpl->print_header( get_string( 'testcases', VPL ) );
$vpl->print_heading_with_help( 'testcases' );
echo'
<div style="text-align: right; margin-top: -30px;margin-bottom:10px">
    <button id="generate-btn" class="btn btn-primary">Generate Test Cases</button>
</div>

';

vpl_editor_util::print_tag();
vpl_editor_util::print_js_i18n();
?>
<!-- old modaal code start -->
<!-- <div id="generate-modal" style="
    display:none;
    position: fixed;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px) saturate(150%);
    padding: 32px 24px 24px 24px;
    z-index: 10000;
    box-shadow: 0 8px 40px 0 rgba(64, 54, 140, 0.16), 0 1.5px 8px 0 rgba(0,0,0,0.09);
    width: 90%;
    max-width: 560px;
    max-height: 80vh;
    overflow: auto;
    border-radius: 24px;
    font-family: 'Segoe UI', 'Arial', sans-serif;
    color: #222;
    border: 1.5px solid rgba(110,80,255,0.07);
    position: fixed;
">
    <button onclick="document.getElementById('generate-modal').style.display='none'"
        style="
        position:absolute; top:18px; right:18px; 
        background:rgba(230,230,255,0.24); 
        border:none; 
        font-size:28px; 
        cursor:pointer; 
        border-radius: 50%; 
        width: 38px; 
        height: 38px; 
        box-shadow:0 2px 8px rgba(64,64,140,0.10); 
        transition: background 0.2s;">
        <span style="color:#5548ea; font-weight:bold;">&times;</span>
    </button>

    <h3 style="
        margin-top: 0;
        letter-spacing: 1.5px;
        font-size: 1.5rem;
        color:rgba(69, 50, 245, 0.9);
        font-weight:700;
        text-shadow: 0 2px 8px rgba(50,50,140,0.10);
    ">Generate Test Cases</h3>

    <label style="margin-top:6px; font-weight:500;">Number of test cases:</label>
    <select id="testcase-count" style="
        border-radius: 8px;
        padding: 7px 20px 7px 12px;
        border: 1px solid #d5d9fb;
        font-size:1.06rem;
        background:rgba(247,247,255,0.7);
        margin-left: 8px;
        outline:none;
        box-shadow:0 1px 2px rgba(90,90,200,0.05);
        color: #4431b7;">
        <option value="3" selected>3</option>
        <option value="5">5</option>
        <option value="10">10</option>
        <option value="15">15</option>
    </select>
    <br><br>
    
<p id="attempts-left" style="color:#555; font-size:0.95rem; margin-top:10px;">
    Attempts left: <?php echo (int)$remaining_attempts; ?>
</p>

    <label style="font-weight:500;">Problem or Code:</label><br>
    <div style="position:relative;min-height:48px;">
        <textarea id="code-content"
              rows="10"
              style="
                  width:100%; 
                  min-height:72px; 
                  max-height:140px; 
                  overflow:auto;
                  background:rgba(241,242,255,0.87);
                  border-radius:10px;
                  border:1.3px solid #d6d7ff;
                  font-size:1.06rem;
                  font-family: 'JetBrains Mono', 'Menlo', 'Consolas', monospace;
                  color:#3c3970;
                  box-shadow:0 2px 10px rgba(44,44,200,0.04);
                  padding:10px 14px 10px 14px;
                  margin-top:2px;
                  resize: vertical;
                  outline: none;
              " readonly>Loading...</textarea> 
        <div id="ai-loader" style="
            display: none;
            position: absolute; left: 50%; top: 55%;
            transform: translate(-50%, -50%);
            z-index: 10;
            pointer-events: none;">
            <div style="
                width:38px; height:38px; 
                border-radius:50%;
                background:conic-gradient(from 45deg at 50% 50%, #8479f7 0% 60%, #f4eeff 70% 100%);
                animation: spin-loader 1.1s linear infinite;
                box-shadow: 0 4px 16px #aaa3fa66;
                position: relative;">
                <span style="
                    content:'';
                    display:block;
                    position:absolute;
                    top:5px; left:50%;
                    width:6px; height:6px; 
                    background:#fff;
                    border-radius:50%;
                    box-shadow: 0 0 8px 3px #baaeff;
                    transform:translateX(-50%);
                    animation: spark-pulse 1.2s ease-in-out infinite alternate;">
                </span>
            </div>
        </div>
        <style>
        @keyframes spin-loader { to { transform: rotate(360deg); } }
        @keyframes spark-pulse { 0% {opacity:.7;} 100% {opacity:1;box-shadow:0 0 12px 6px #cfc6fa;} }
        </style>
    </div>
    <br>

    <div style="display: flex; gap: 12px;">
        <button id="generate-confirm" class="btn btn-success" style="
            background: linear-gradient(90deg,#6c63ff 60%,#a98fff 100%);
            border:none;
            color: #fff;
            font-weight: 600;
            border-radius: 9px;
            padding: 9px 22px;
            box-shadow: 0 2px 8px 0 #786af218;
            transition: filter 0.18s;
            font-size: 1.09rem;
            letter-spacing: 0.5px;
            cursor:pointer;
        " >⚡ Generate</button>
        <button onclick="document.getElementById('generate-modal').style.display='none'" class="btn btn-secondary" style="
            background: #f7f6ff;
            color: #6c63ff;
            border:1.3px solid #b8b7fc;
            font-weight: 500;
            border-radius: 8px;
            padding: 9px 20px;
            margin-left: 0;
            font-size: 1.03rem;
            transition: filter 0.15s;
            cursor:pointer;
        ">Cancel</button>
    </div>

    <label style="margin-top: 18px; display:block; font-weight:500;">Generated Test Cases:</label>
    <button id="copy-testcases" title="Copy to clipboard"
        style="
            background:rgba(230,230,255,0.24); 
            border:none; 
            font-size:18px; 
            cursor:pointer; 
            margin-bottom:4px;
            margin-left: 4px;
            padding:4px 9px;
            border-radius: 7px;
            color: #3d33b3;
            box-shadow: 0 1px 6px #7c74e816;
            transition: background 0.16s;
        ">
        <i class="fa fa-copy"></i>
    </button>

<div id="testcase-loader" style="
    display: none;
    text-align: center;
    margin-top: 12px;
">
    <div style="
        width:38px; height:38px; 
        border-radius:50%;
        background:conic-gradient(from 45deg at 50% 50%, #8479f7 0% 60%, #f4eeff 70% 100%);
        animation: spin-loader 1.1s linear infinite;
        box-shadow: 0 4px 16px #aaa3fa66;
        margin: auto;
        position: relative;">
        <span style="
            content:'';
            display:block;
            position:absolute;
            top:5px; left:50%;
            width:6px; height:6px; 
            background:#fff;
            border-radius:50%;
            box-shadow: 0 0 8px 3px #baaeff;
            transform:translateX(-50%);
            animation: spark-pulse 1.2s ease-in-out infinite alternate;">
        </span>
    </div>
</div>
<div id="copy-toast" style="
    display: none;
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #4caf50;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    font-size: 0.95rem;
    z-index: 11000;
    opacity: 0;
    transition: opacity 0.3s ease;">
    ✅ Test cases copied!
</div>

    <pre id="testcase-output"
         style="margin-top:10px; background:rgba(248,247,255,0.89); padding:13px; border-radius:8px; max-height:220px; overflow:auto; font-size:1.02rem; font-family: 'JetBrains Mono', 'Menlo', 'Consolas', monospace; box-shadow:0 1px 8px 0 #afa8fc13;"></pre>

         <p style="margin-top:12px; font-size:0.85rem; color:#666;">
        ⚠️ Note: AI-generated test cases may not always be accurate. Please review them before use.
    </p>        </div> -->
<!-- old modal code end*********************** -->


 <div id="generate-modal" style="
    display:none;
    position: fixed;
    top: 7%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px) saturate(150%);
    padding: 32px 24px 28px 24px;
    margin-bottom: 20px; /* gap from bottom */
    z-index: 10000;
    box-shadow: 0 8px 40px 0 rgba(64, 54, 140, 0.16), 0 1.5px 8px 0 rgba(0,0,0,0.09);
    width: 95%;
    max-width: 700px;
    border-radius: 24px;
    font-family: 'Segoe UI', 'Arial', sans-serif;
    color: #222;
    border: 1.5px solid rgba(110,80,255,0.07);
">
    <button onclick="document.getElementById('generate-modal').style.display='none'"
        style="position:absolute; top:18px; right:18px; 
               background:rgba(230,230,255,0.24); border:none; 
               font-size:28px; cursor:pointer; border-radius: 50%; 
               width: 38px; height: 38px;">
        <span style="color:#5548ea; font-weight:bold;">&times;</span>
    </button>

    <h3 style="margin-top: 0; font-size: 1.6rem; color:#4532f5; font-weight:700;">
    AI Test Case Generator    </h3>

    <label>Number of test cases:</label>
    <select id="testcase-count" style="margin-left:8px; padding:5px; border-radius:6px;">
        <option value="3" selected>3</option>
        <option value="5">5</option>
        <option value="10">10</option>
        <option value="15">15</option>
    </select>

    <p id="attempts-left" style="color:#555; font-size:0.95rem; margin-top:10px;">
        Attempts left: <?php echo (int)$remaining_attempts; ?>
    </p>

    <label style="font-weight:500;">Problem or Code:</label><br>
    <textarea id="code-content"
              rows="8"
              style="width:100%; background:#f1f2ff; border-radius:8px; 
                     border:1px solid #d6d7ff; font-family:monospace; 
                     padding:10px; resize: vertical;"
              readonly>Loading...</textarea>
    <br>

    <div style="display: flex; gap: 12px; margin-top:10px;">
        <button id="generate-confirm" class="btn btn-success" style="
            background: linear-gradient(90deg,#6c63ff 60%,#a98fff 100%);
            border:none; color:#fff; font-weight:600;
            border-radius: 8px; padding: 9px 22px; cursor:pointer;">
            ⚡ Generate
        </button>
        <button onclick="document.getElementById('generate-modal').style.display='none'" 
                class="btn btn-secondary" style="background:#f7f6ff; color:#6c63ff;
                border:1px solid #b8b7fc; border-radius: 8px; padding: 9px 20px;">
            Cancel
        </button>
    </div>

    <label style="margin-top:18px; display:block; font-weight:500;">Generated Test Cases:</label>
    <button id="copy-testcases" title="Copy to clipboard" style="background:#eee; border:none; cursor:pointer; margin-bottom:4px;">
        <i class="fa fa-copy"></i>
    </button>

    <div id="testcase-loader" style="
    display: none;
    text-align: center;
    margin-top: 12px;
">
    <div style="
        width:38px; height:38px; 
        border-radius:50%;
        background:conic-gradient(from 45deg at 50% 50%, #8479f7 0% 60%, #f4eeff 70% 100%);
        animation: spin-loader 1.1s linear infinite;
        box-shadow: 0 4px 16px #aaa3fa66;
        margin: auto;
        position: relative;">
        <span style="
            content:'';
            display:block;
            position:absolute;
            top:5px; left:50%;
            width:6px; height:6px; 
            background:#fff;
            border-radius:50%;
            box-shadow: 0 0 8px 3px #baaeff;
            transform:translateX(-50%);
            animation: spark-pulse 1.2s ease-in-out infinite alternate;">
        </span>
    </div>
</div>
<style>
        @keyframes spin-loader { to { transform: rotate(360deg); } }
        @keyframes spark-pulse { 0% {opacity:.7;} 100% {opacity:1;box-shadow:0 0 12px 6px #cfc6fa;} }
        </style>
    <pre id="testcase-output" style="margin-top:10px; background:#f8f7ff; padding:13px;
         border-radius:8px; max-height:220px; overflow:auto; font-family:monospace;">
    </pre>

    <p style="margin-top:12px; font-size:0.85rem; color:#666;">
        ⚠️ Note: AI-generated test cases may not always be accurate. Please review them before use.
    </p>
</div>

<div id="copy-toast" style="
    display: none;
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #4caf50;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    font-size: 0.95rem;
    z-index: 11000;
    opacity: 0;
    transition: opacity 0.3s ease;">
    ✅ Test cases copied!
</div> 

<script>
// Show/hide AI loader depending on the textarea content
function showLoaderIfLoading() {
    var txt = document.getElementById('code-content').value.trim();
    document.getElementById('ai-loader').style.display = txt === "Loading..." ? "block" : "none";
}
// Call this function after updating code-content, or setInterval if loaded async
// setInterval(showLoaderIfLoading, 200);
</script>
<script>
        var attemptsLeft = <?php echo $remaining_attempts; ?>;

function showToast(message) {
    const toast = document.getElementById('copy-toast');
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => toast.style.opacity = '1', 50);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 2000);
}
document.getElementById('generate-btn').addEventListener('click', () => {
    console.log("Button clicked");
    fetch('requiredfiles.json.php?id=<?php echo $id; ?>&action=load')
        .then(res => res.json())
        .then(data => {
            console.log("Fetched response:", data);
            if (data.success && data.response.files.length > 0) {
                const content = data.response.files[0].contents;
                // Just assign the contents directly
document.getElementById('code-content').value = content;

            } else {
                document.getElementById('code-content').value = "No content found.";
            }
            document.getElementById('generate-modal').style.display = 'block';
        })
        .catch(err => {
            console.error("Error fetching file content:", err);
        });
});


// document.getElementById('generate-confirm').addEventListener('click', () => {
//     const code = document.getElementById('code-content').value;
//     const count = document.getElementById('testcase-count').value;
//     document.getElementById('testcase-loader').style.display = "block";
//     document.getElementById('testcase-output').textContent = "Generating...";
//     document.getElementById('generate-confirm').disabled = true; // prevent spam

//     fetch('generate_testcases_api.php', {
//         method: 'POST',
//         headers: {'Content-Type': 'application/json'},
//         body: JSON.stringify({ code, count,vplid: <?php echo $id; ?> })
//     })
//     .then(res => res.text())
//     .then(output => {
//         document.getElementById('testcase-output').textContent = output;
//         document.getElementById('testcase-loader').style.display = "none";
//     });
// });

document.getElementById('generate-confirm').addEventListener('click', () => {
    const code = document.getElementById('code-content').value;
    const count = document.getElementById('testcase-count').value;

    // Show loader and clear previous output
    document.getElementById('testcase-loader').style.display = "block";
    document.getElementById('testcase-output').textContent = "Generating...";

    fetch('generate_testcases_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code,
            count,
            vplid: <?php echo $id; ?> // course module ID
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('testcase-loader').style.display = "none";

        if (!data.success) {
            document.getElementById('testcase-output').textContent = data.error;
            document.getElementById('attempts-left').textContent = `Attempts left: ${data.remaining}`;
            document.getElementById('generate-confirm').disabled = true;
            return;
        }

        document.getElementById('testcase-output').textContent = data.output;
        document.getElementById('attempts-left').textContent = `Attempts left: ${data.remaining}`;
        document.getElementById('generate-confirm').disabled = (data.remaining <= 0);
    })
    .catch(err => {
        document.getElementById('testcase-loader').style.display = "none";
        document.getElementById('testcase-output').textContent = "⚠️ Failed to generate test cases.";
        console.error(err);
    });
});

document.getElementById('copy-testcases').addEventListener('click', () => {
    const testcases = document.getElementById('testcase-output').textContent;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(testcases)
            .then(() => alert("✅ Test cases copied to clipboard!"))
            .catch(err => console.error("Failed to copy:", err));
    } else {
        // Fallback for non-secure context
        const textarea = document.createElement("textarea");
        textarea.value = testcases;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            // alert("✅ Test cases copied to clipboard!");
            showToast("✅ Test cases copied!");
        } catch (err) {
            console.error("Fallback: Failed to copy", err);
        }
        document.body.removeChild(textarea);
    }
});


</script>
<?php

$vpl->print_footer_simple();
