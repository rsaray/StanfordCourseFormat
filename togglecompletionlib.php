<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once('ChromePhp.php');

$operation = optional_param('op', '', PARAM_ALPHANUM);
$userid = optional_param('userid', 0, PARAM_INT);
$moduleid = optional_param('cmid', 0, PARAM_INT);

// if (!$cm = get_coursemodule_from_id('quiz', $moduleid)) {
//     print_error('invalidcoursemodule');
// }
// if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
//     print_error('coursemisconf');
// }

// // Check login and get context.
// require_login($course, false, $cm);
// $context = context_module::instance($cm->id);
// require_capability('mod/quiz:view', $context);

/* Updating quiz completion */
function toggle_quiz_completion($userid,$moduleid) {
	global $DB;

	$count_quiz_sql = 'SELECT count(*) FROM {quiz_attempts} WHERE userid = ? AND quiz = (SELECT cm.instance FROM {course_modules} cm WHERE cm.id = ?)';
	$quiz_sql = 'SELECT * FROM {quiz_attempts} WHERE userid = ? AND quiz = (SELECT cm.instance FROM {course_modules} cm WHERE cm.id = ?)';

	$counter = $DB->count_records_sql($count_quiz_sql, array($userid,$moduleid));
	
	if($counter === 0) {
		echo "empty";	
	}else {
		$quiz_rs = $DB->get_recordset_sql($quiz_sql, array($userid,$moduleid));
		foreach ($quiz_rs as $record) {
			if($record->state == 'finished'){
				ChromePhp::log($record);
				/* TODO: 1) toggle check for the current quiz module */
				
				/* TODO: 2) unlock modules that relate to the quiz module */
			}else if($record->state == 'inprogress') {
				break;
			}
		}
		$quiz_rs->close();
	}	

}

function auto_completion() {
    // global $CFG, $USER;

    // $attemptobj = quiz_attempt::create($attemptid);
    
    // $course = $attemptobj->get_course();
    // $cm = $attemptobj->get_cm();
    // $continueseq = $attemptobj->get_quiz()->reviewspecificfeedback);

    // $targetstate=COMPLETION_COMPLETE;
    // if (isguestuser() or !confirm_sesskey()) {
    //     print_error('error');
    // }

    // // Set up completion object and check it is enabled.
    // $completion = new completion_info($course);
    // if (!$completion->is_enabled()) {
    //     $error_log = "not able to complete: ".$error_log;
    //     throw new moodle_exception('completionnotenabled', 'completion');
    // }

    // if($cm->completion != COMPLETION_TRACKING_MANUAL) {
    //     $error_log = "not COMPLETION_TRACKING_MANUAL: ".$error_log;
    //     throw new moodle_exception('cannotmanualctrack', 'completion');
    // }

    // // $completion->update_state($cm, $targetstate);
    // $current = $completion->get_data($cm, false, $USER->id);
    // $newstate = ($targetstate == COMPLETION_UNKNOWN) ? $completion->internal_get_state($cm, $USER->id, $current) : $targetstate;
    // $current->forced = ($targetstate == COMPLETION_UNKNOWN) ? '0' : '1';
    // $current->completionstate = $newstate;
    // $current->timemodified    = time();
    // $completion->internal_set_data($cm, $current);

    // $error_log = "success: ".$error_log;
    // error_log(date('r') . " - $error_log\n", 3, $CFG->logroot . "stanford_quiz_completion.log");
    // if($continueseq==0){
    //     echo "<span id='status' style='display:none'>Completion:OK</span>";
    //     echo '<script type="text/javascript">parent.quizSlideUpFromLinear(); </script>';
    //     die();
    // }

}

// function finish_state() {
// 	global $DB;
// 	$userFinishedModulesArray = array();
// 	$userFinishedModules = 'SELECT * FROM {course_modules_completion} where userid = ?';
// 	$userFinishedModulesResult = $DB->get_recordset_sql($userFinishedModules,array($USER->id));
// 	foreach ($userFinishedModulesResult as $value) {
// 		array_push($userFinishedModulesArray, $value->coursemoduleid);
// 	}
// 	$userFinishedModulesResult->close();

// 	$moduleset = array();
// 	$sql = 'SELECT * FROM {course_modules_availability} where sourcecmid = ?';
// 	$courseModulesql = 'SELECT * FROM {course_modules_availability} where coursemoduleid = ?';

// 	$rs = $DB->get_recordset_sql($sql,array($_GET['id']));

// 	foreach ($rs as $value) {

// 		$courseModuleset = array();
// 		$courseModulesResult = $DB->get_recordset_sql($courseModulesql,array($value->coursemoduleid));
// 		foreach ($courseModulesResult as $value) {
// 			array_push($courseModuleset, $value->sourcecmid);
// 		}
// 		$courseModulesResult->close();	
	
// 		$moduleset[$value->coursemoduleid] = $courseModuleset;
	
// 	}
// 	$rs->close();
// 	$returnResults = array();
// 	foreach ($moduleset as $key => $value) {
// 		$containsSearch = count(array_intersect($userFinishedModulesArray, $value)) == count($value);
// 		if($containsSearch) {
// 			array_push($returnResults, $key);
// 		}
// 	}
// 	return json_encode($returnResults);
// }

/* update Video */
function toogle_video_completion() {
	global $DB;

}

if($operation == 'quiz') {
	toggle_quiz_completion($userid,$moduleid);
}
