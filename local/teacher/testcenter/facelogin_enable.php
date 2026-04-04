<?php
require_once('../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_title('Facelogin Access Control');
$PAGE->set_heading('Facelogin Access Control');
require_login();

// Only admins (roleid=3) allowed
if (!user_has_role_assignment($USER->id, 3)) {
    redirect($CFG->wwwroot);
}
$correct_otp = "1234"; // the OTP you expect

if (!isset($_GET['otp']) || $_GET['otp'] !== $correct_otp) {
    // OTP missing or incorrect
    echo "Access denied!";
    exit;
}
$cid = required_param('cid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $response = ['success' => false];

    if (isset($_POST['userids'], $_POST['status'])) {
        $userids = array_map('intval', (array)$_POST['userids']);
        $newStatus = $_POST['status'] == 1 ? '1' : '0';

        // Get facelogin field id
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'enablefacelogin'], IGNORE_MISSING);

        if ($fieldid) {
            foreach ($userids as $userid) {
                $existing = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid]);
                if ($existing) {
                    $existing->data = $newStatus;
                    $DB->update_record('user_info_data', $existing);
                } else {
                    $newdata = new stdClass();
                    $newdata->userid = $userid;
                    $newdata->fieldid = $fieldid;
                    $newdata->data = $newStatus;
                    $DB->insert_record('user_info_data', $newdata);
                }

                // 🟢 Reset sessions only when enabling
                if ($newStatus === '1') {
                    $DB->delete_records('sessions', ['userid' => $userid]);
                }
            }
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fetch users
$sql = "
SELECT u.id AS userid, u.username, u.firstname,
       COALESCE(uid.data, '0') AS facelogin_status
FROM {course} c
JOIN {context} ct ON c.id = ct.instanceid
JOIN {role_assignments} ra ON ra.contextid = ct.id
JOIN {user} u ON u.id = ra.userid
JOIN {role} r ON r.id = ra.roleid
LEFT JOIN {user_info_data} uid
       ON uid.userid = u.id
       AND uid.fieldid = (SELECT id FROM {user_info_field} WHERE shortname = 'enablefacelogin' LIMIT 1)
WHERE c.id = :cid AND r.id = 5
";
$users = $DB->get_records_sql($sql, ['cid' => $cid]);

echo $OUTPUT->header();
?>
<style>
.dashboard-table {
    width: 100%;
    margin: 20px auto;
    border-collapse: collapse;
    text-align: center;
}
.dashboard-table th, .dashboard-table td {
    padding: 10px;
    border: 1px solid #ddd;
}
.dashboard-table th {
    background: #ea6645;
    color: white;
}
.toggle-btn {
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 5px;
    color: white;
    border: none;
}
.toggle-enable { background: green; }
.toggle-disable { background: red; }
.search-box { margin: 15px 0; }
</style>

<div class="search-box" style="display: flex; justify-content: space-between; align-items: center; padding-right: 50px;">
    <div>
        <input type="text" id="searchInput" placeholder="Search by Hall Ticket or Name" style="padding:5px; width:250px;">
        <button id="toggleAll" class="toggle-btn">Loading...</button>
    </div>
</div>

<table class="dashboard-table" id="faceloginTable">
    <thead>
        <tr>
            <th>Hall Ticket</th>
            <th>Name</th>
            <th>Facelogin Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user->username) ?></td>
            <td><?= htmlspecialchars($user->firstname) ?></td>
            <td class="status-cell"><?= $user->facelogin_status == '1' ? 'Enabled' : 'Disabled' ?></td>
            <td>
                <button class="toggle-btn <?= $user->facelogin_status == '1' ? 'toggle-disable' : 'toggle-enable' ?>"
                        data-userid="<?= $user->userid ?>"
                        data-username="<?= htmlspecialchars($user->username) ?>"
                        data-status="<?= $user->facelogin_status ?>">
                    <?= $user->facelogin_status == '1' ? 'Disable Facelogin' : 'Enable Facelogin' ?>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const toggleAllBtn = document.getElementById("toggleAll");

    // 🔍 Search filter
    searchInput.addEventListener("keyup", function () {
        const value = this.value.toLowerCase();
        document.querySelectorAll("#faceloginTable tbody tr").forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
        });
    });

    async function updateFacelogin(userids, status) {
        const formData = new FormData();
        formData.append('ajax', '1');
        userids.forEach(id => formData.append('userids[]', id));
        formData.append('status', status);
        const response = await fetch(window.location.href, { method: 'POST', body: formData });
        return await response.json();
    }

    // 🔄 Set toggle-all button state
    const allEnabled = [...document.querySelectorAll("#faceloginTable tbody tr")].every(
        row => row.querySelector(".status-cell").textContent.trim() === "Enabled"
    );

    function setButtonState(enabled) {
        if (enabled) {
            toggleAllBtn.textContent = "Disable Facelogin";
            toggleAllBtn.classList.remove("toggle-enable");
            toggleAllBtn.classList.add("toggle-disable");
        } else {
            toggleAllBtn.textContent = "Enable Facelogin";
            toggleAllBtn.classList.remove("toggle-disable");
            toggleAllBtn.classList.add("toggle-enable");
        }
    }
    setButtonState(allEnabled);

    // 🔘 Single user toggle
    document.querySelectorAll("#faceloginTable .toggle-btn[data-userid]").forEach(btn => {
        btn.addEventListener("click", async function () {
            const userid = this.dataset.userid;
            const username = this.dataset.username;
            const currentStatus = parseInt(this.dataset.status);
            const newStatus = currentStatus === 1 ? 0 : 1;
            const confirmMsg = newStatus === 1 ? "enable" : "disable";
            if (!confirm(`Do you want to ${confirmMsg} facelogin for user: ${username}?`)) return;

            const res = await updateFacelogin([userid], newStatus);
            if (res.success) {
                alert("Facelogin status updated!");
                location.reload();
            } else {
                alert("Failed to update status!");
            }
        });
    });

    // 🔘 Enable/Disable All toggle
    toggleAllBtn.addEventListener("click", async () => {
        const newStatus = toggleAllBtn.textContent.includes("Enable") ? 1 : 0;
        const confirmMsg = newStatus === 1
            ? "Enable facelogin for all users and reset logins?"
            : "Disable facelogin for all users?";
        if (!confirm(confirmMsg)) return;

        const ids = [...document.querySelectorAll("#faceloginTable .toggle-btn[data-userid]")]
            .map(b => b.dataset.userid)
            .filter(id => id);

        const res = await updateFacelogin(ids, newStatus);
        if (res.success) {
            if (newStatus === 1)
                alert("Facelogin enabled for all users and logins have been reset!");
            else
                alert("Facelogin disabled for all users!");
            location.reload();
        } else {
            alert("Failed to update Facelogin for all users!");
        }
    });
});
</script>

<?php echo $OUTPUT->footer(); ?>
