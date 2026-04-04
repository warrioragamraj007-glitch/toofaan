<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module newmodule
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_newmodule
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/course/lib.php');
defined('MOODLE_INTERNAL') || die();



/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */

function getStatus($userid,$courseid)
{
	 global $DB;
	$res = $DB->get_record('watchlist', array('userid'=>$userid,'courseid'=>$courseid), 'status', IGNORE_MISSING);
	return $res->status;
}
function updateStatus($status,$userid,$courseid)
{
	 global $DB;
	$res = $DB->get_record('watchlist', array('userid'=>$userid,'courseid'=>$courseid), 'id', IGNORE_MISSING);
	if($res->id)
	{

		$record = new stdClass();
		$record->userid   = $userid;
		$record->courseid = $courseid;
		$record->status   = $status;
		$record->last_activated = date ("Y-m-d H:i:s", time());

		//$record->activityid   = $activityid;
		$res = $DB->get_record('watchlist', array('userid'=>$userid,'courseid'=>$courseid), 'counter', IGNORE_MISSING);
		$counter=$res->counter;
		if($status==1)
		$record->counter  = $counter++;
		$sql="update {watchlist} set counter=$counter, status=$status where userid=$userid and courseid=$courseid";
		$lastinsertid = $DB->execute($sql);
	}
	else
	{
		$record = new stdClass();
		$record->userid   = $userid;
		$record->courseid = $courseid;
		$record->status   = $status;
		//$record->activityid   = $activityid;

		$record->last_activated = date ("Y-m-d H:i:s", time());
		$record->counter  = 1;
		$lastinsertid = $DB->insert_record('watchlist', $record, false);


	}
	
}
function getAllWatchlistRecordByCourse($courseid,$status)
{
	 global $DB;
	$result = $DB->get_records('watchlist',array('status'=>$status,'courseid'=>$courseid));
	return $result;
}

function getAllWatchlistRecordByUser($userid)
{
	 global $DB;
	$result = $DB->get_records('watchlist',array('userid'=>$userid));
	return $result;
}


function getAllWatchlistCountByUser($userid)
{
	global $DB;
	$result = $DB->get_record_sql("select sum(counter) as wcount from mdl_watchlist where userid=$userid");
	if($result->wcount!='')
	return $result->wcount;
	else
		return 0;
}


function updateAct($actid,$status)
{
	 global $DB;
	$res = $DB->get_record('activity_status', array('activityid'=>$actid), 'id', IGNORE_MISSING);
	if($res->id)
	{

		$record = new stdClass();
		$record->activityid   = $actid;
		$record->status   = $status;
		$record->activity_start_time   = date( "Y-m-d H:i:s",time());
		$sql="update {activity_status} set  status=$status where activityid=$actid";
		$lastinsertid = $DB->execute($sql);
	}
	else
	{
		$record = new stdClass();
		$record->activityid   = $actid;
		$record->status   = $status;
		$record->activity_start_time   = date( "Y-m-d H:i:s",time());
		$lastinsertid = $DB->insert_record('activity_status', $record, false);


	}
	
}
function getActStatus($actid){
	global $DB;
	$res = $DB->get_record('course_modules', array('id'=>$actid), 'completionexpected', IGNORE_MISSING);
	if($res->completionexpected!=0)
	{
		return 1; // activity completed
	}
	else
	 return 0; // activity not completed

}
