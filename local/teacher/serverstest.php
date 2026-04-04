<?php
require_once('../../config.php');
require_login();
$PAGE->set_context(context_system::instance());
// $PAGE->set_title('Course Material');
echo $OUTPUT->header();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Status Checker</title>
    <style>
        /* Additional CSS for centering the table */
        /* .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        } */
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Server Status Checker</h1>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jailserver IP Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="serverTableBody">
                    <!-- Table rows will be dynamically added here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // List of server URLs to check
        var servers = [
            "http://10.11.51.101:8081/OK",
            "http://10.11.51.102:8081/OK",
            "http://10.11.51.103:8081/OK"
            // Add more server URLs here
        ];

        // Function to check server status
        function checkServer(url, row) {
            window.open(url, '_blank');
        }

        // Populate the table with server IP addresses and buttons to test
        var tableBody = document.getElementById("serverTableBody");
        servers.forEach(function(server) {
            var row = tableBody.insertRow();
            var cellIpAddress = row.insertCell(0);
            cellIpAddress.innerText = server;
            var cellAction = row.insertCell(1);
            var testButton = document.createElement("button");
            testButton.innerText = "Test";
            testButton.classList.add("btn", "btn-primary"); // Add Bootstrap classes
            testButton.addEventListener("click", function() {
                checkServer(server, row);
            });
            cellAction.appendChild(testButton);
        });
    </script>
</body>
</html>

<?php
echo $OUTPUT->footer();
?>
