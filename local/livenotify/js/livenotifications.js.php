<?php
header('Content-type: text/javascript');
define('AJAX_SCRIPT', false);

require_once(__DIR__ . '/../../../config.php');
require_login();

global $USER;

// === SECRET KEY - MUST MATCH server.js ===
$secret = 'a8f3d92e8c4b9e0a7c6f1b9a4e72c93f8d21c90a6d5b1f2e8c7a9b0d1e3f4c5';

// === Get enrolled courses ===
$enrolledcourses = enrol_get_users_courses($USER->id, true);
$courseMap = [];
$courseTokens = [];

foreach ($enrolledcourses as $course) {
    $name = format_string($course->fullname);
    // Escape single quotes for JS safety
    $name = str_replace("'", "\\'", $name);
    $courseMap[$course->id] = $name;

    $payload = [
        'userid'   => (int)$USER->id,
        'courseid' => (int)$course->id,
        'role'     => 'student',
        'exp'      => time() + 600  // 10 minutes expiry
    ];

    $payloadJson = json_encode($payload);


    $payloadB64 = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');


    $signature = hash_hmac('sha256', $payloadB64, $secret);

    $courseTokens[$course->id] = $payloadB64 . '.' . $signature;
}

$enrolledCourseIds = array_keys($courseMap);

// Output JS constants
echo "const STUDENT_ID = " . (int)$USER->id . ";\n";
echo "const courseMap = " . json_encode($courseMap) . ";\n";
echo "const enrolledCourseIds = " . json_encode($enrolledCourseIds) . ";\n";
echo "const courseTokens = " . json_encode($courseTokens) . ";\n";

echo "const WS_SERVER_URL = 'ws://' + window.location.hostname + ':8081';\n\n";
?>

(function() {
    if (enrolledCourseIds.length === 0) {
        console.log('No enrolled courses - live notifications disabled');
        return;
    }

    let messageHistory = JSON.parse(localStorage.getItem('studentMessageHistory')) || [];
    let currentToast = null;
    const sockets = {};

    function showToast(title, message, time) {
        if (currentToast) currentToast.remove();

        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <button class="notification-close" onclick="this.parentElement.classList.add('hide'); setTimeout(() => this.parentElement.remove(), 300);">×</button>
            <div class="notification-icon">🔔</div>
            <div class="notification-title">${escapeHtml(title)}</div>
            <div class="notification-message">${escapeHtml(message)}</div>
            <div class="notification-time">🕒 ${time}</div>
        `;
        document.body.appendChild(toast);
        currentToast = toast;

        setTimeout(() => {
            if (currentToast === toast) {
                toast.classList.add('hide');
                setTimeout(() => {
                    if (currentToast === toast) {
                        toast.remove();
                        currentToast = null;
                    }
                }, 300);
            }
        }, 10000);
    }

    function addToHistory(title, message, timeStr) {
        messageHistory.unshift({ title, message, timeStr });
        if (messageHistory.length > 50) messageHistory.pop();
        localStorage.setItem('studentMessageHistory', JSON.stringify(messageHistory));
        updateHistoryUI();
        updateBadge();
    }

    function updateHistoryUI() {
        const content = document.getElementById('historyContent');
        if (!content) return;

        if (messageHistory.length === 0) {
            content.innerHTML = `<div class="empty-state"><div class="empty-state-icon">📭</div><div class="empty-state-text">No messages yet</div></div>`;
            return;
        }

        content.innerHTML = messageHistory.map(item => `
            <div class="history-item">
                <div class="history-item-time">🕐 ${item.timeStr}</div>
                <div style="font-weight:700; margin:8px 0 4px; color:#e67e22;">${escapeHtml(item.title)}</div>
                <div class="history-item-message">${escapeHtml(item.message)}</div>
            </div>
        `).join('');
    }

    function updateBadge() {
        const badge = document.getElementById('messageBadge');
        if (badge) {
            const count = messageHistory.length;
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    function toggleHistory() {
        const panel = document.getElementById('historyPanel');
        const overlay = document.getElementById('overlay');
        if (panel && overlay) {
            panel.classList.toggle('open');
            overlay.classList.toggle('show');
        }
    }

    function clearHistory() {
        if (confirm('Clear all message history?')) {
            messageHistory = [];
            localStorage.removeItem('studentMessageHistory');
            updateHistoryUI();
            updateBadge();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // === CSS (unchanged - beautiful as always) ===
    const css = document.createElement('style');
    css.textContent = `
    .notification-toast{position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#ffffff;padding:32px 44px;border-radius:16px;box-shadow:0 16px 40px rgba(0,0,0,0.15);max-width:520px;width:90%;z-index:99999;animation:slideDown .5s ease-out;border:3px solid #e67e22;border-top:8px solid #001f3f;}
    .notification-toast.hide{animation:slideUp .3s ease-in forwards;}
    @keyframes slideDown{from{transform:translateX(-50%) translateY(-100px);opacity:0}to{transform:translateX(-50%) translateY(0);opacity:1}}
    @keyframes slideUp{to{transform:translateX(-50%) translateY(-100px);opacity:0}}
    .notification-icon{font-size:56px;text-align:center;margin-bottom:18px;color:#e67e22;}
    .notification-title{font-size:22px;font-weight:700;color:#001f3f;text-align:center;margin-bottom:12px;}
    .notification-message{font-size:17px;color:#333;line-height:1.6;text-align:center;margin-bottom:18px;}
    .notification-time{font-size:14px;color:#666;text-align:center;}
    .notification-close{position:absolute;top:12px;right:16px;background:none;border:none;font-size:26px;color:#999;cursor:pointer;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
    .notification-close:hover{background:#f0f0f0;color:#e67e22;}

    .history-button{position:fixed;bottom:30px;right:30px;width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#e67e22,#ff4500);border:none;cursor:pointer;box-shadow:0 8px 24px rgba(230,126,34,0.5);display:flex;align-items:center;justify-content:center;font-size:30px;color:white;z-index:9999;}
    .message-badge{position:absolute;top:-8px;right:-8px;background:#001f3f;color:white;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;border:3px solid white;}

    .history-panel{position:fixed;right:-420px;top:0;width:420px;height:100vh;background:#ffffff;box-shadow:-8px 0 30px rgba(0,0,0,0.2);z-index:99999;transition:right .4s ease;border-left:4px solid #e67e22;}
    .history-panel.open{right:0;}
    .history-header{padding:28px;background:#001f3f;color:white;display:flex;justify-content:space-between;align-items:center;}
    .history-title{font-size:21px;font-weight:700;display:flex;align-items:center;gap:12px;}
    .close-panel-btn{background:rgba(255,255,255,0.2);border:none;color:white;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:22px;display:flex;align-items:center;justify-content:center;}
    .close-panel-btn:hover{background:rgba(255,255,255,0.3);}
    .history-content{flex:1;overflow-y:auto;padding:24px;}
    .history-item{background:#f9f9f9;padding:20px;border-radius:14px;margin-bottom:16px;border-left:6px solid #e67e22;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
    .history-item-time{font-size:13px;color:#e67e22;font-weight:600;margin-bottom:10px;}
    .empty-state{text-align:center;padding:100px 20px;color:#888;}
    .empty-state-icon{font-size:80px;margin-bottom:24px;opacity:0.6;}
    .empty-state-text{font-size:18px;font-weight:500;}
    .clear-history-btn{margin:20px;padding:16px;background:#001f3f;color:white;border:none;border-radius:10px;cursor:pointer;font-weight:600;width:calc(100% - 40px);font-size:16px;}
    .clear-history-btn:hover{background:#e67e22;}
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99998;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(6px);}
    .overlay.show{opacity:1;pointer-events:auto;}
    @media (max-width:600px){.history-panel{width:100%;right:-100%;}.notification-toast{padding:28px 32px;}}
    `;
    document.head.appendChild(css);

    // === HTML Structure ===
    const html = `
        <button id="historyButton" class="history-button" title="Message History">
            📋
            <span id="messageBadge" class="message-badge" style="display:none;">0</span>
        </button>

        <div id="historyPanel" class="history-panel">
            <div class="history-header">
                <div class="history-title">📜 Message History</div>
                <button class="close-panel-btn">×</button>
            </div>
            <div class="history-content" id="historyContent">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-text">No messages yet</div>
                </div>
            </div>
            <button class="clear-history-btn">🗑️ Clear History</button>
        </div>

        <div id="overlay" class="overlay"></div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);

    // === Event Listeners ===
    document.getElementById('historyButton')?.addEventListener('click', toggleHistory);
    document.getElementById('overlay')?.addEventListener('click', toggleHistory);
    document.querySelector('.close-panel-btn')?.addEventListener('click', toggleHistory);
    document.querySelector('.clear-history-btn')?.addEventListener('click', clearHistory);

    updateHistoryUI();
    updateBadge();

    function reconnectWebSocket(courseId, delay = 8000) {
    console.warn(`🔁 Reconnecting to ${courseMap[courseId]} in ${delay / 1000}s...`);
    setTimeout(() => {
        connectWebSocket(courseId);
    }, delay);
}
    // === WebSocket Connections ===
    enrolledCourseIds.forEach(courseId => {
        const token = courseTokens[courseId];
        if (!token) {
            console.warn(`Missing token for course ${courseId}`);
            return;
        }

        const wsUrl = `${WS_SERVER_URL}/?token=${encodeURIComponent(token)}`;
        const ws = new WebSocket(wsUrl);

        ws.onopen = () => {
            console.log(`🟢 STUDENT connected to course ${courseId}: ${courseMap[courseId]}`);
        };

        ws.onerror = (err) => {
            console.error(`WebSocket error on course ${courseId}:`, err);
        };

       ws.onclose = (e) => {
    console.warn(`🔴 Disconnected from ${courseMap[courseId]} (code: ${e.code})`);
    reconnectWebSocket(courseId);
};

        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'notify' && data.message) {
                    const courseName = courseMap[courseId] || 'Teacher';
                    const timeStr = data.time || new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                    showToast(courseName, data.message.trim(), timeStr);
                    addToHistory(courseName, data.message.trim(), timeStr);
                }
            } catch (err) {
                console.error('Invalid message received:', event.data, err);
            }
        };

        sockets[courseId] = ws;
    });

})();