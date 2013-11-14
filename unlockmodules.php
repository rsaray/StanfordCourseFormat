<?php
	/**
	* webservice created by zhao.
	*/
	require_once('../../../config.php');
	require_once('ChromePhp.php');
	
	$id = optional_param('id', 0, PARAM_INT);
	global $DB;
	$userFinishedModulesArray = array();
	$userFinishedModules = "SELECT * 
						      FROM {course_modules_completion} 
						     where userid = :userid";
	$userFinishedModulesResult = $DB->get_recordset_sql($userFinishedModules,array('userid'=>$USER->id));
	foreach ($userFinishedModulesResult as $value) {
		array_push($userFinishedModulesArray, $value->coursemoduleid);
	}
	$userFinishedModulesResult->close();

	$moduleset = array();
	$sql = "SELECT * 
			  FROM {course_modules_availability} cma2 
			 WHERE coursemoduleid IN (SELECT cma1.coursemoduleid 
			 							FROM {course_modules_availability} cma1 
			 						   WHERE cma1.sourcecmid = :sourcecmid)";
	$courseModulesAvailableArray = $DB->get_recordset_sql($sql,array('sourcecmid'=>$id));
	foreach ($courseModulesAvailableArray as $key => $value) {
		$moduleset[$value->coursemoduleid][] = $value->sourcecmid;
	}
	$courseModulesAvailableArray->close();
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