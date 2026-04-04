<?php
require_once('config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $USER;

define('JAIL_SECRET', 'vpljailserver'); // must match Flask SECRET

// ------------------------------------------------------------
// Handle AJAX actions
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    require_sesskey();
    $action = required_param('action', PARAM_TEXT);
    $server = required_param('server', PARAM_RAW);

    $baseurl = rtrim($server, '/');
    $baseurl = preg_replace('/:8081$/', ':9081', $baseurl);

    // --- Probe server status ---
    if ($action === 'probe') {
        $url = "$baseurl/status?token=" . JAIL_SECRET;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $response) {
            $data = json_decode($response, true);
            $state = strtolower($data['status'] ?? '');
            if ($state === 'active') {
                $status = 'Ready';
            } elseif (in_array($state, ['inactive', 'failed'])) {
                $status = 'Stopped';
            } else {
                $status = 'Unknown';
            }

            // ✅ Only return status (no folder_count here)
            echo json_encode([
                'ok' => true,
                'status' => $status
            ]);
        } else {
            echo json_encode(['ok' => false, 'status' => 'Not reachable']);
        }
        exit;
    }

    // --- Control (start, stop, restart) ---
    if (in_array($action, ['start', 'stop', 'restart'])) {
        $url = "$baseurl/control?action=$action&token=" . JAIL_SECRET;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200) {
            echo ucfirst($action) . " executed successfully.";
        } else {
            echo ucfirst($action) . " failed (HTTP $code).";
        }
        exit;
    }

    // --- Folder count only ---
    if ($action === 'foldercount') {
        $url = "$baseurl/foldercount?token=" . JAIL_SECRET;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        echo $response;
        exit;
    }

    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// ------------------------------------------------------------
// Normal Page UI
// ------------------------------------------------------------
$PAGE->set_url(new moodle_url('/mod/vpl/admin/jailmanager.php'));
$PAGE->set_title('VPL Jail Manager');
$PAGE->set_heading('VPL Jail Server Manager');

echo $OUTPUT->header();

$configRecord = $DB->get_record('config_plugins', [
    'plugin' => 'mod_vpl',
    'name'   => 'jail_servers'
]);

$servers = [];
if ($configRecord && !empty($configRecord->value)) {
    $lines = preg_split('/\r\n|\r|\n/', trim($configRecord->value));
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $servers[] = $line;
    }
}

echo html_writer::start_tag('div', ['class' => 'card p-3']);
echo html_writer::tag('h4', 'VPL Jail Servers');
echo html_writer::start_tag('table', ['class' => 'generaltable']);
echo html_writer::start_tag('tr');
echo html_writer::tag('th', '#');
echo html_writer::tag('th', 'Server');
echo html_writer::tag('th', 'Status');
echo html_writer::tag('th', 'Folder Count');
echo html_writer::tag('th', 'Actions');
echo html_writer::end_tag('tr');

$i = 1;
foreach ($servers as $server) {
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $i);
    echo html_writer::tag('td', s($server));
    echo html_writer::tag('td', html_writer::tag('span', 'Checking...', ['class' => 'status', 'data-server' => $server]));
    echo html_writer::tag('td', html_writer::tag('span', '-', ['class' => 'count', 'data-server' => $server]));

    $btns  = html_writer::tag('button', 'Start', ['class' => 'btn btn-success start-btn', 'data-server' => $server, 'disabled' => true]) . ' ';
    $btns .= html_writer::tag('button', 'Stop', ['class' => 'btn btn-danger stop-btn', 'data-server' => $server, 'disabled' => true]) . ' ';
    $btns .= html_writer::tag('button', 'Restart', ['class' => 'btn btn-warning restart-btn', 'data-server' => $server, 'disabled' => true]) . ' ';
    $btns .= html_writer::tag('button', 'Count Folders', ['class' => 'btn btn-info count-btn', 'data-server' => $server, 'disabled' => true]);
    echo html_writer::tag('td', $btns);
    echo html_writer::end_tag('tr');
    $i++;
}

echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

// ------------------------------------------------------------
// JavaScript section
// ------------------------------------------------------------
$script = <<<JS
const PAGE_URL = window.location.href;
const SESSKEY = '{$USER->sesskey}';

async function postAction(action, server) {
    const res = await fetch(PAGE_URL, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action, server, sesskey:SESSKEY})
    });
    return await res.text();
}

async function probeServer(server) {
    const res = await fetch(PAGE_URL, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'probe', server, sesskey:SESSKEY})
    });
    return await res.json();
}

async function updateFolderCount(server){
    const res = await fetch(PAGE_URL, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'foldercount', server, sesskey:SESSKEY})
    });
    const data = await res.json();
    const count = (data.count !== undefined && data.count !== null) ? data.count : '-';
    document.querySelector('.count[data-server="'+server+'"]').textContent = count;
}

function setButtons(server, state) {
    const startBtn = document.querySelector('.start-btn[data-server="'+server+'"]');
    const stopBtn = document.querySelector('.stop-btn[data-server="'+server+'"]');
    const restartBtn = document.querySelector('.restart-btn[data-server="'+server+'"]');
    const countBtn = document.querySelector('.count-btn[data-server="'+server+'"]');

    if (state === 'running') {
        startBtn.setAttribute('disabled','disabled');
        stopBtn.removeAttribute('disabled');
        restartBtn.removeAttribute('disabled');
        countBtn.removeAttribute('disabled');
    } else if (state === 'stopped') {
        startBtn.removeAttribute('disabled');
        stopBtn.setAttribute('disabled','disabled');
        restartBtn.removeAttribute('disabled','disabled');
        countBtn.removeAttribute('disabled');
    } else {
        startBtn.setAttribute('disabled','disabled');
        stopBtn.setAttribute('disabled','disabled');
        restartBtn.setAttribute('disabled','disabled');
        countBtn.setAttribute('disabled','disabled');
    }
}

async function refreshAll() {
    const rows = document.querySelectorAll('.status');
    for (const el of rows) {
        const server = el.dataset.server;
        el.textContent = 'Checking...';

        try {
            const data = await probeServer(server);
            if (data.ok) {
                el.textContent = data.status;
                // ✅ Always fetch folder count separately
                await updateFolderCount(server);

                // Update button states
                if (data.status === 'Ready') {
                    setButtons(server, 'running');
                } else if (data.status === 'Stopped') {
                    setButtons(server, 'stopped');
                } else {
                    setButtons(server, 'unknown');
                }
            } else {
                el.textContent = 'Not reachable';
                document.querySelector('.count[data-server="'+server+'"]').textContent = '-';
                setButtons(server, 'unknown');
            }
        } catch (err) {
            el.textContent = 'Error';
            document.querySelector('.count[data-server="'+server+'"]').textContent = '-';
            setButtons(server, 'unknown');
        }
    }
}

document.addEventListener('click', async e=>{
    const t = e.target;
    if(!t.dataset.server) return;
    const server = t.dataset.server;

    if(t.classList.contains('start-btn')) alert(await postAction('start',server));
    if(t.classList.contains('stop-btn')) alert(await postAction('stop',server));
    if(t.classList.contains('restart-btn')) alert(await postAction('restart',server));
    if(t.classList.contains('count-btn')) await updateFolderCount(server); // directly updates UI
    refreshAll();
});

refreshAll();
setInterval(refreshAll, 120000); // every 5 minutes
JS;

echo html_writer::script($script);
echo $OUTPUT->footer();
?>
