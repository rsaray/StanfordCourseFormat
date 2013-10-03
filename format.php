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
 * @author Zhao
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.5
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($stanford = optional_param('stanford', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $stanford);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure all sections are created
$course = course_get_format($course)->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_stanford');

function fnRemoveLeftNav($buffer) {
    return (str_replace('<div id="region-pre" class="block-region">','<div id="region-pre" class="block-region block-region-none">' , $buffer));
}
    

if (!$PAGE->user_allowed_editing()) {
	ob_start("fnRemoveLeftNav");
}

if (!empty($displaysection)) {
	$renderer->stanford_print_single_section_page($course, null, null, null, null, $displaysection);
} else {
	$renderer->stanford_print_multiple_section_page($course, null, null, null, null);
}

if(!$PAGE->user_allowed_editing()){
    echo "<div id='region-sidebar'>";  
        
    echo "<ul>";
    $seciontNumber = 1;
    $sidbarArray = $DB->get_recordset_sql('SELECT * FROM {course_sections} WHERE course = ?',array($course->id));
    foreach ($sidbarArray as $value) {
        if($value->name != null && $value->section != 0){
            echo "<li class='section' id='section-".$seciontNumber."'><a>".$value->name."</a><ul style='display:none;'>";
            
        $sectionID = $DB->get_field ('course_sections', 'id', array('name'=>$value->name,'course'=>$value->course));

        $instance_rs = $DB->get_recordset_sql('SELECT * FROM {course_modules} WHERE module = ? AND course = ? AND section = ?',array(12,$value->course,$sectionID));
        
        foreach ($instance_rs as $value) {
            $title = $DB->get_field ('label', 'intro', array('id'=>$value->instance));
            $moduleid = $DB->get_field ('course_modules', 'id', array('course'=>$value->course,'module'=>12,'instance'=>$value->instance));
            // $title = str_replace("<p> </p>","",$title);
            preg_match_all("/<h4>(.*?)<\/h4>/is", $title, $gettitle);

            echo "<li class='module' id='module-".$moduleid."'><a href='#'>".$gettitle[0][0]."</a><ul class='progressBar'></ul></li>";
        }
        $instance_rs->close();
        
        $seciontNumber++;    
        echo "</ul></li>";
        }
        
    }
    $sidbarArray->close();
    echo "</ul></div>";
}
// Include course format js module
$PAGE->requires->js('/course/format/stanford/format.js');
