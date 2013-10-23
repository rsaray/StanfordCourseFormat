<?php
	/**
	* webservice created by zhao.
	*/
	require_once('../../../config.php');
	require_once('ChromePhp.php');
	
	global $DB;
	$userFinishedModulesArray = array();
	$userFinishedModules = 'SELECT * FROM {course_modules_completion} where userid = ?';
	$userFinishedModulesResult = $DB->get_recordset_sql($userFinishedModules,array($USER->id));
	foreach ($userFinishedModulesResult as $value) {
		array_push($userFinishedModulesArray, $value->coursemoduleid);
	}
	$userFinishedModulesResult->close();

	$moduleset = array();
	$sql = 'SELECT * FROM {course_modules_availability} where sourcecmid = ?';
	$courseModulesql = 'SELECT * FROM {course_modules_availability} where coursemoduleid = ?';

	$rs = $DB->get_recordset_sql($sql,array($_GET['id']));

	foreach ($rs as $value) {

		$courseModuleset = array();
		$courseModulesResult = $DB->get_recordset_sql($courseModulesql,array($value->coursemoduleid));
		foreach ($courseModulesResult as $value) {
			array_push($courseModuleset, $value->sourcecmid);
		}
		$courseModulesResult->close();	
	
		$moduleset[$value->coursemoduleid] = $courseModuleset;
	
	}
	$rs->close();
	$returnResults = array();
	foreach ($moduleset as $key => $value) {
		$containsSearch = count(array_intersect($userFinishedModulesArray, $value)) == count($value);
		
		if($containsSearch) {
			array_push($returnResults, $key);
		}
	}
	// ChromePhp::log($returnResults);
	echo json_encode($returnResults);
?>