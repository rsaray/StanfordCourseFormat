<?php
require_once('../../../config.php');
require_once('ChromePhp.php');
include 'Mobile_Detect.php';


require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);        // Course module ID

if ($id) {
    $cm = get_coursemodule_from_id('url', $id, 0, false, MUST_EXIST);
    $url = $DB->get_record('url', array('id'=>$cm->instance), '*', MUST_EXIST);
}else {
	print_error('missingparameter');
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/url:view', $context);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Make sure URL exists before generating output - some older sites may contain empty urls
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
$exturl = trim($url->externalurl);
if (empty($exturl) or $exturl === 'http://') {
    url_print_header($url, $cm, $course);
    url_print_heading($url, $cm, $course);
    url_print_intro($url, $cm, $course);
    notice(get_string('invalidstoredurl', 'url'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
// unset($exturl);

/* start getting supplemental */
$supplementsArray = array();

// Get all labels' id for the current course's modules
$course_section_labels_sql = "SELECT * 
								FROM mdl_course_modules 
							   WHERE module = (SELECT m.id FROM {modules} m WHERE m.name = 'label') 
									 AND section = (SELECT cm.section FROM {course_modules} cm WHERE cm.id = :id)";
$course_section_labels = $DB->get_recordset_sql($course_section_labels_sql,array('id'=>$id));

// Trying to get the first label id from the labels array
$section_label_ids = array();
$course_section_labels_key = 0;
$first_label = '';
foreach ($course_section_labels as $value) {
	if($course_section_labels_key == 0) {
		$first_label = $value->id;
	}
	$course_section_labels_key++;
	array_push($section_label_ids, $value->id);
}
$course_section_labels->close();

// getting modules' id for the current section
$section_sequence_sql = "SELECT cs.sequence 
						   FROM {course_sections} cs 
						  WHERE id = (SELECT cm.section FROM {course_modules} cm WHERE cm.id = :id)";
$section_sequence = $DB->get_record_sql($section_sequence_sql, array('id'=>$id));
$piecesOfSequence = explode(",", $section_sequence->sequence);

// only get resources(PDFs) before the first label 
$piecesOfSequenceItems = array();
foreach ($piecesOfSequence as $value) {
	if($first_label == $value){
		break;
	}
	$piecesOfSequenceItems[] = $value;
}
if(count($piecesOfSequenceItems) > 0) {
	$piecesOfSequenceItemString = join(',',$piecesOfSequenceItems);

	$moduleslideGroup = "SELECT cm.*, m.revision 
	 					   FROM {course_modules} cm 
	 					   JOIN {modules} md ON md.id = cm.module 
	 					   JOIN {resource} m ON m.id = cm.instance 
 					  LEFT JOIN {course_sections} cw ON cw.id = cm.section 
					      WHERE cm.id IN (".$piecesOfSequenceItemString.") 
					      		AND md.name = "resource" AND cm.course = :course";

	$moduleslideGrouprs = $DB->get_recordset_sql($moduleslideGroup,array('course'=>$course->id));

	// pushing the resouces(PDFs) URLs to supplementsArray
	foreach ($moduleslideGrouprs as $key => $value) {
		$rescontext = get_context_instance(CONTEXT_MODULE, $value->id);
		$resfs = get_file_storage();
		$resfiles = $resfs->get_area_files($rescontext->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is quite inefficient!!
		if (count($resfiles) < 1) {
			resource_print_filenotfound($resource, $cm, $course);
			die;
		} else {
			$file = reset($resfiles);
			unset($resfiles);
		}
		$filename=$file->get_filename();
		$rest = substr($filename, 0, -4);
		if(!in_array($rest, $verifySingleRecord)) {
			$verifySingleRecord[] = $rest;
			if(substr($filename,-3)=="pdf"){
				$supplementnewex=",true";
			}
			$path = $CFG->wwwroot.'/pluginfile.php/'.$rescontext->id.'/mod_resource/content/'.$value->revision.$file->get_filepath().$filename;
			$unitArray = array('url'=>$path,'name'=>$rest,'urlid'=>$value->id);
			$supplementsArray[] = $unitArray;
		}
		
	}
	$moduleslideGrouprs->close();
}

// END: General resource 

// Looking for videos' resources(PDFs)
$targetSequece = array();
$finallyTarget = array();

for($slIndex = 0; $slIndex < count($section_label_ids); $slIndex++) {
	$haschild = '';
	$locker = false;
	for($sqIndex = 0; $sqIndex < count($piecesOfSequence); $sqIndex++){
		if(($section_label_ids[$slIndex] == $piecesOfSequence[$sqIndex]) && ($locker == false)){
			$haschild = true;
			$locker = true;
		}
		if($haschild == true && $haschild != ''){
			array_push($targetSequece, $piecesOfSequence[$sqIndex]);
		}
		if($piecesOfSequence[$sqIndex] == $section_label_ids[$slIndex+1]){
			$haschild = false;	
		}
	}
	foreach ($targetSequece as $value) {
		if($value == $PAGE->cm->id){
			$finallyTarget = $targetSequece;
		}
	}
	$targetSequece = array();		
}

$finallocker = false;

// $finalTarget has all the all the modules' id between two labels 
if(count($finallyTarget) > 0){
	$finallyTargetString = join(',',$finallyTarget);
	$finallyTargetGroup = "SELECT cm.*, m.revision 
							 FROM {course_modules} cm 
							 JOIN {modules} md ON md.id = cm.module 
							 JOIN {resource} m ON m.id = cm.instance 
						LEFT JOIN {course_sections} cw ON cw.id = cm.section 
							WHERE cm.id IN ('.$finallyTargetString.') 
								  AND md.name = 'resource' 
								  AND cm.course = :course";

	$finallyTargetGroups = $DB->get_recordset_sql($finallyTargetGroup,array('course'=>$course->id));

	foreach ($finallyTarget as $value1) {
		
		if($finallocker == true) {
			foreach ($finallyTargetGroups as $finallyTargetGroupItem) {
				if($value1 == $finallyTargetGroupItem->id && ($finallyTargetGroupItem->indent ==3 || $finallyTargetGroupItem->indent ==1)){
					$rescontext = get_context_instance(CONTEXT_MODULE, $finallyTargetGroupItem->id);
		
					$resfs = get_file_storage();
					$resfiles = $resfs->get_area_files($rescontext->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is quite inefficient!!
					if (count($resfiles) < 1) {
						resource_print_filenotfound($resource, $cm, $course);
						die;
					} else {
						$file = reset($resfiles);
						unset($resfiles);
					}
					$filename=$file->get_filename();
					$rest = substr($filename, 0, -4);
					if(!in_array($rest, $verifySingleRecord)) {
						$verifySingleRecord[] = $rest;
						if(substr($filename,-3)=="pdf"){
							$supplementnewex=",true";
						}
							$path = $CFG->wwwroot.'/pluginfile.php/'.$rescontext->id.'/mod_resource/content/'.$finallyTargetGroupItem->revision.$file->get_filepath().$filename;
							$unitArray = array('url'=>$path,'name'=>$rest,'urlid'=>$finallyTargetGroupItem->id);
							$supplementsArray[] = $unitArray;
					}
				}
			}
		}
		if($value1 == $PAGE->cm->id) {
			$finallocker = true;
		}
	}
}

/* END supplemental */

if(count($supplementsArray) > 0){ 
	$haveSupplements = true;
}else {
	$haveSupplements = false;
}


    
echo '<!DOCTYPE html>
<html  dir="ltr" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="user-scalable=yes, initial-scale=1.0, maximum-scale=2.0, width=device-width" />
<meta name="keywords" content="" />';    

$headeroutput = '<meta http-equiv="pragma" content="no-cache" />';
$headeroutput .= '<meta http-equiv="expires" content="0" />';
$headeroutput .= '<link rel="stylesheet" href="css/stanford-jquery-ui.css">';
$headeroutput .= '<link rel="stylesheet" href="css/main.css">';
$headeroutput .= '<link rel="stylesheet" href="css/zPlayer.css">';
echo $headeroutput;

echo "</head><body id='pageLecture'>";
?>
	<div id="videoContainer" class="mobile">
		<video id="html5tester" autoplay poster="" class="video-js" preload="metadata" onloadedmetadata="zPlayer.showDuration();" onplay="zPlayer.showCurrentTime()">
	        <source src="<?php echo $exturl; ?>" />
	    </video>
	    <div id="video-controls">
	        <div id="progress">
	            <div class="video_progress">
	                <div id="video_progress_bar_ruler"></div>
	                <div class="video_progress_bar"></div>
	                <a id="progressbar_handle" href="javascript:void(0);"></a>
	            </div>
	            <div id="fake_progress_bar"></div>
	        </div>

	        
	        <div id="timer-container">
	        	<div class="arrow-right pause"></div>
	            <div class="video-duration">00:00:00</div>
	            <div id="timer-seperator">&nbsp;|&nbsp;</div>
	            <div class="video-timer">00:00:00</div>
	        </div>
	        <div id="user-prefs">
	            <div id="fullscreenIcon"><a id="fullscreen-btn" href="javascript:void(0);" onclick="zPlayer.launchFullScreen();">Full Screen</a></div>
	            <div id="video-volume-controls">
	                <div id="volume-slider" class="hidebar"></div>
	                <a id="volume-btn" href="javascript:void(0);">Volume</a>
	            </div>
	            <div class="video-resolution">
	                <ul class="hidebar">
	                </ul>
	                <a href="javascript:void(0);">Resolution</a>
	            </div>
	            <div class="video-speed">
	                <ul class="hidebar">
	                    <li data-value="4"><a href="#">4.0X</a></li>
	                    <li data-value="3"><a href="#">3.0X</a></li>
	                    <li data-value="2"><a href="#">2.0X</a></li>
	                    <li data-value="1.8"><a href="#">1.8X</a></li>
	                    <li data-value="1.6"><a href="#">1.6X</a></li>
	                    <li data-value="1.4"><a href="#">1.4X</a></li>
	                    <li data-value="1.2"><a href="#">1.2X</a></li>
	                    <li data-value="1" class="selected"><a href="#">1.0X</a></li>
	                </ul>
	                <a href="javascript:void(0);">Speed</a>
	            </div>

	            <div class="video-cc">
	                <a href="javascript:void(0);">CC</a>
	            </div>
	        </div>
	    </div>
	</div>
	<div id="supplementalBlock">
		<?php 
			if(count($supplementsArray) > 3) {
				$hideliornot = "none";
				echo "<span class='pdf-prev pdf-tile-nav active'>&lsaquo;&lsaquo;</span>";	
			}else {
				$hideliornot = "block";
			}
			
			echo '<ul id="sidebarTab-pdf-sub-nav" class="pdf-tile-nav">';
			
			if($haveSupplements) {
				
				foreach ($supplementsArray as $key => $value) {
					$tmpModValue = $key % 3;
					$totalElement = count($supplementsArray) - 1;
					if($tmpModValue === 0) {
						if($key == 0) {
							echo "<li class='firstchild currentchild' style='display:block;'><ul>";	
						}else if($key === $totalElement){
							echo "<li class='lastchild' style='display:none;'><ul>";	
						}else{
							echo "<li style='display:none;'><ul>";	
						}
					}
					if($key == 0) {
						$actived = "actived";
					}else {
						$actived = "";
					}

					echo '<li class="sidebarTab pdf-sub-elem '.$actived.'" data-strlen="'.$valueLength.'" data-url="'.$value['url'].'" data-urlid="'.$value['urlid'].'" style="display:block;"><a href="javascript: void(0)" onclick="switchpdf($(this));">'.$value['name'].'</a></li>';
					if($tmpModValue === 2 || $key === $totalElement) {
						echo "</ul></li>";
					}
				}
			} 
			
			echo '</ul>';
			if(count($supplementsArray) > 3) {
				echo "<span class='pdf-next pdf-tile-nav inactive'>&rsaquo;&rsaquo;</span>";	
			}
			if($haveSupplements) { 
			?>
				<ul id="viewmodes">
					<li class="mode T " onclick="popupPDF();"></li>
				</ul>
				
			<?php
				echo "<div class='pdfsidebarTab'></div>";
			}else {
				echo "<div class='pdfsidebarTab'><div class='supplementView'><strong>No supplementary materials are available for this video.</strong></div></div>";
			}
		?>
	</div>
<?php
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
echo '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>';
echo '<script type="text/javascript" src="js/pdfobject.js"></script>';
echo '<script type="text/javascript" src="js/pdfserver.js"></script>';
echo '<script type="text/javascript" src="js/zPlayer.js"></script>';
echo "</body></html>";
    


