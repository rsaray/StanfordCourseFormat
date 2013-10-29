<?php
require_once('../../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once('ChromePhp.php');

$operation = optional_param('op', '', PARAM_ALPHANUM);
$moduleid = optional_param('cmid', 0, PARAM_INT);


$userid = $USER->id;

if (!$cm = get_coursemodule_from_id('quiz', $moduleid)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

// Check login and get context.
require_login($course, false, $cm);

/* Updating quiz completion */
function toggle_quiz_completion($userid,$moduleid) {
	global $DB ;

	$count_quiz_sql = 'SELECT count(*) FROM {quiz_attempts} WHERE userid = ? AND quiz = (SELECT cm.instance FROM {course_modules} cm WHERE cm.id = ?)';
	$quiz_sql = 'SELECT * FROM {quiz_attempts} WHERE userid = ? AND quiz = (SELECT cm.instance FROM {course_modules} cm WHERE cm.id = ?)';

	$counter = $DB->count_records_sql($count_quiz_sql, array($userid,$moduleid));

	if($counter === 0) {
		echo "empty";	
	}else {
		
		$quiz_rs = $DB->get_recordset_sql($quiz_sql, array($userid,$moduleid));
		foreach ($quiz_rs as $record) {
			
			if($record->state == 'finished'){
				/* toggle check for the current quiz module */
				auto_completion($moduleid);
				break;			
			}else if($record->state == 'inprogress') {
				break;
			}
		}
		$quiz_rs->close();
	}
}

function auto_completion($cmid) {
	global $DB;
	$targetstate = 1;

	switch($targetstate) {
	    case COMPLETION_COMPLETE:
	    case COMPLETION_INCOMPLETE:
	        break;
	    default:
	        print_error('unsupportedstate');
	}

	// Get course-modules entry
	$cm = get_coursemodule_from_id(null, $cmid, null, true, MUST_EXIST);
	$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

	// Set up completion object and check it is enabled.
	$completion = new completion_info($course);
	if (!$completion->is_enabled()) {
	    throw new moodle_exception('completionnotenabled', 'completion');
	}

	if($cm->completion != COMPLETION_TRACKING_MANUAL) {
	    error_or_ajax('cannotmanualctrack');
	}

	$completion->update_state($cm, $targetstate);
	echo 'OK';
}

function error_or_ajax($message) {
    echo get_string($message, 'error');
    exit;
}

/* update Video */
function toogle_video_completion() {
	global $DB;

}

if($operation == 'quiz') {
	toggle_quiz_completion($userid,$moduleid);
}
