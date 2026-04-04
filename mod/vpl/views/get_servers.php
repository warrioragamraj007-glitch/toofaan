<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/vpl/vpl.class.php');
require_once($CFG->dirroot . '/mod/vpl/jail/jailserver_manager.class.php');
require_once($CFG->dirroot . '/mod/vpl/jail/running_processes.class.php');

// Now you can use Moodle's global variables like $DB, $PAGE, etc.
global $PAGE, $COURSE, $DB;

// Your existing code...

// Get the VPL instance
$id = required_param('id',0, PARAM_INT);
$vpl = new mod_vpl($id);

// Get the list of servers
$serverss = vpl_jailserver_manager::get_server_list(get_currentjailservers($vpl));

// Write the list of servers to a text file
$file = '/path/to/your/servers.txt'; // Set the path to your text file
file_put_contents($file, implode("\n", $serverss));

// Your existing code...
