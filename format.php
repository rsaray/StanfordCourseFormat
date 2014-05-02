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
 * stanford course format.  Display the whole course as "stanford" made of modules.
 *
 * @package format_stanford
 * @copyright 2006 The Open University
 * @author N.D.Freear@open.ac.uk, and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');


// Horrible backwards compatible parameter aliasing..
if ($stanford = optional_param('stanford', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $stanford);
    debugging('Outdated stanford param passed to course/view.php', DEBUG_DEVELOPER);
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

if (!empty($displaysection)) {
    $renderer->stanford_print_single_section_page($course, null, null, null, null, $displaysection);
} else {
    $renderer->stanford_print_multiple_section_page($course, null, null, null, null);
}


// if(!$PAGE->user_allowed_editing()){
//     // echo ta_feedback($USER->id,$course->id);
//     // ob_start("remove_left_nav");
//     echo left_nav_bar($course->id);
// }

echo output_dropdown();
echo "<div id='cohortkeyContainer' ><a><img src='format/stanford/css/img/icon_close.png' /></a><iframe src='' width='1000' height='500'></iframe></div>";

// Include course format js module
$PAGE->requires->js('/course/format/stanford/format.js');
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
$PAGE->requires->js('/course/format/stanford/js/main.js');
// echo '<script type="text/javascript" src="format/stanford/left-bar.js"></script>';
