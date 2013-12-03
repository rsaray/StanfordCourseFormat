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
 * Renderer for outputting the topics course format.
 *
 * @package stanford course formate
 * @copyright 2013 Stanford University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.5
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once('ChromePhp.php');
include 'Mobile_Detect.php';

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

if(strpos($exturl, '.mp4') === false){
	// echo $exturl;
	redirect($exturl);
}
// unset($exturl);

/* start getting supplemental */
$supplementsArray = array();

$supplementsArray = lecture_supplemental($course->id,$id);

$supplementsarraycount = count($supplementsArray);

    
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
if(get_user_browser() !=='firefox'){

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

	<!-- <div id="supplementalBlock"> -->
	<?php 
}else {
	
	echo '<div id="videoContainer" class="mobile" style="height: 102%;">';
	echo '<object height="621" width="956" type="application/x-shockwave-flash" data="js/StrobeMediaPlayback.swf">
                <param name="movie" value="js/StrobeMediaPlayback.swf"></param>
                <param name="flashvars" value="src='.$exturl.'"></param>
                <param name="allowFullScreen" value="true"></param>
                <param name="allowscriptaccess" value="always"></param>
                <param name="wmode" value="direct"></param>
            </object>';
	echo '</div>';
	
}
	$outputsupplementcontent ='';
	$outputsupplementcontent .= html_writer::start_tag('div',array('id'=>'supplementalBlock'));

	$outputsupplementcontent .= html_writer::start_tag('ul',array('id'=>'sidebarTab-pdf-sub-nav','class'=>'pdf-tile-nav'));

	if($supplementsarraycount>0) {
		foreach ($supplementsArray as $key => $value) {
			$tmpModValue = $key % 3;
			$totalElement = count($supplementsArray) - 1;

			if($tmpModValue === 0) {
				if($key == 0) {
					$outputsupplementcontent .= "<li class='firstchild currentchild' style='display:block;'><ul>";	
				}else if($key === $totalElement){
					$outputsupplementcontent .= "<li class='lastchild' style='display:none;'><ul>";	
				}else{
					$outputsupplementcontent .= "<li style='display:none;'><ul>";	
				}
			}
			$activedclass = 'sidebarTab pdf-sub-elem ';
			if($key == 0) {
				$activedclass .= "actived ";
			}
			$outputsupplementcontent .= html_writer::start_tag('li',array('class'=>$activedclass,'data-url'=>$value['url'],'data-urlid'=>$value['urlid'],'style'=>'display:block'));
			$outputsupplementcontent .= html_writer::link('javascript:void(0)',$value['name'],array('onclick'=>'switchpdf($(this))'));
			$outputsupplementcontent .= html_writer::end_tag('li');
			if($tmpModValue === 2 || $key === $totalElement) {
				$outputsupplementcontent .= html_writer::end_tag('ul');
				$outputsupplementcontent .= html_writer::end_tag('li');
			}
		}
	} 
	
	$outputsupplementcontent .= html_writer::end_tag('ul');
	if($supplementsarraycount > 3) {
		$outputsupplementcontent = html_writer::tag('span','&lsaquo;&lsaquo;',array('class'=>'pdf-prev pdf-tile-nav active')) . $outputsupplementcontent .
									html_writer::tag('span','&rsaquo;&rsaquo;',array('class'=>'pdf-next pdf-tile-nav inactive'));
	}
	
	if($supplementsarraycount > 0) { 
		$outputsupplementcontent .= html_writer::start_tag('ul',array('id' =>'viewmodes'));
		// TODO: need to implement a drag/drop switch button
		$outputsupplementcontent .= html_writer::tag('li','',array('class'=>'mode T','onclick'=>"popupPDF();"));
		$outputsupplementcontent .= html_writer::end_tag('ul');
		$outputsupplementcontent .= html_writer::tag('div','',array('class'=>'pdfsidebarTab'));
	}else {
		$outputsupplementcontent .= html_writer::tag('div',"<div class='supplementView'><strong>No supplementary materials are available for this video.</strong></div>",array('class'=>'pdfsidebarTab'));
	}
	$outputsupplementcontent .= html_writer::end_tag('div');
	echo $outputsupplementcontent;
	
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
echo '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>';
echo '<script type="text/javascript" src="js/pdfobject.js"></script>';
echo '<script type="text/javascript" src="js/pdfserver.js"></script>';
echo '<script type="text/javascript" src="js/zPlayer.js"></script>';
echo "</body></html>";
    


