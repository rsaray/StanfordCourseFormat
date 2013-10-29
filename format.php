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
    echo '<div id="showallitems"><a id="showallitemsaction" href="javascript:void(0);">Show All Course Modules</a></div>';
    echo "<div id='region-sidebar'>";  
    echo "<ul>";
    
    $subsidebarSQL = 'SELECT cm.id,l.name,cm.section FROM {label} l LEFT JOIN {course_modules} cm ON l.id = cm.instance LEFT JOIN {course_sections} cs ON cs.id = cm.instance WHERE cm.module = 12 AND cm.course = ?';
    $subsidebarArray = array();
    $subsidebar = $DB->get_recordset_sql($subsidebarSQL,array($course->id));
    foreach ($subsidebar as $key => $value) {
        $subsidebarArray[$value->section][] = array($value->id,$value->name);
    }
    $subsidebar->close();
    $sidbarArray = $DB->get_recordset_sql('SELECT * FROM {course_sections} WHERE course = ?  AND section <> 0 AND NAME <> "NULL"',array($course->id));
    foreach ($sidbarArray as $value) {
        echo "<li class='section' data-id='section-".$value->section."'><a>".$value->name."</a><ul style='display:none;'>";
        $subSideBarItemArray = $subsidebarArray[$value->id];
        foreach ($subSideBarItemArray as $key => $value) {
           echo "<li class='module' data-id='module-".$value[0]."'><a href='#'><h4>".$value[1]."</h4></a><ul class='progressBar'></ul></li>";
        }
        echo "</ul></li>";
    }
    $sidbarArray->close();
    echo "</ul></div>";
}

echo "<div id='dropdownvideopage' style='display:none;'>";
echo '<span class="videotitle"></span>';
echo '<a href="javascript:void(0)" class="slideUpButton" data-moduleid="" data-moduletype=""></a>';
// if($detect->isMobile()){
//     echo '<div style="height: 100%;-webkit-overflow-scrolling:touch;overflow: scroll;"><iframe id="videochat" style="width:100%;height:100%;" src="" frameborder="0"></iframe></div>';  
// }else {
    echo '<div style="height: 100%;"><iframe id="videochat" style="width:100%;height:100%;" src="" frameborder="0"></iframe></div>';
// }

echo "</div>";
    
// Include course format js module
$PAGE->requires->js('/course/format/stanford/format.js');
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
// echo '<script type="text/javascript" src="format/stanford/js/main.js"></script>';
$PAGE->requires->js('/course/format/stanford/js/main.js');
