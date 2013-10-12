<?php
require_once('../../../config.php');

global $DB;

$userid = optional_param('userid', 0, PARAM_INT);
$moduleid = optional_param('userid', 0, PARAM_INT);

if (!$cm = get_coursemodule_from_id('quiz', $moduleid)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }

// Check login and get context.
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/quiz:view', $context);

$quiz_sql = 'SELECT * FROM `formats`.`mdl_quiz_attempts` WHERE userid = ? AND quiz = (SELECT cm.instance FROM `mdl_course_modules` cm WHERE cm.id = ?)';
$ta_feedback_rs = $DB->get_recordset_sql($quiz_sql, array($userid,$moduleid));

