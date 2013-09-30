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
require_once($CFG->dirroot.'/course/renderer.php');

/**
 * The core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class stanford_course_renderer extends core_course_renderer {
    const COURSECAT_SHOW_COURSES_NONE = 0; /* do not show courses at all */
    const COURSECAT_SHOW_COURSES_COUNT = 5; /* do not show courses but show number of courses next to category name */
    const COURSECAT_SHOW_COURSES_COLLAPSED = 10;
    const COURSECAT_SHOW_COURSES_AUTO = 15; /* will choose between collapsed and expanded automatically */
    const COURSECAT_SHOW_COURSES_EXPANDED = 20;
    const COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT = 30;

    /**
     * A cache of strings
     * @var stdClass
     */
    protected $strings;

    /**
     * Override the constructor so that we can initialise the string cache
     *
     * @param moodle_page $page
     * @param string $target
     */
    public function __construct(moodle_page $page, $target) {
        $this->strings = new stdClass;
        parent::__construct($page, $target);
        
    }

    public function stanford_course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER,$DB;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $key => $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                $modNextModNumber = $modinfo->sections[$section->section][$key+1];
                if(!empty($modNextModNumber)) {
                  $modNext = $modinfo->cms[$modNextModNumber];    
                }

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }
                $instancename = $mod->get_formatted_name();
                if ($modulehtml = $this->course_section_cm($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {

                    $moduleshtml[$modnumber][0] = $modulehtml;
                    if($modNext->modname == "label"){
                      $moduleshtml[$modnumber][1] = 'lastchild';  
                    }else {
                      $moduleshtml[$modnumber][1] = 'normalchild';
                    }
                    $moduleshtml[$modnumber][3] = $instancename;
                    
                }
            }
        }

        if (!empty($moduleshtml) || $ismoving) {

            $output .= html_writer::start_tag('ul', array('class' => 'section img-text'));

            $totalElement = count($moduleshtml);
            $elementCounter = 0;
            $firstOrLastChild = false;
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                $elementCounter ++;
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $output .= html_writer::tag('li', html_writer::link($movingurl, $this->output->render($movingpix)),
                            array('class' => 'movehere', 'title' => $strmovefull));
                }

                $mod = $modinfo->cms[$modnumber];

                $liclasses = array();
                if($mod->modname == 'linqto') {
                  $liclasses[] = 'navitem';      
                }

                if($firstOrLastChild) {
                  $liclasses[] = 'firstchild';
                  $firstOrLastChild = false;
                }

                if($mod->modname == 'label') {
                   $firstOrLastChild = true;
                }

                if($modulehtml[1] == 'lastchild' && $elementCounter!=1) {
                  $liclasses[] = 'lastchild';
                }

                if($totalElement == $elementCounter) {
                  $liclasses[] = 'lastchild';
                }

                if((strpos($modulehtml[3],'Final Exam (Complete all exercises to unlock)') !== false) || (strpos($modulehtml[3],'Course Evaluation (Complete all exercises to unlock)') !== false)){
                    $liclasses[] = 'final';
                }
                
                if($mod->modname == "google") {
                    $googleSql = 'SELECT g.display from {google} g where id =(select cm.instance from {course_modules} cm where id = ?)';
                    $googlepopup = $DB->get_field_sql($googleSql, array($mod->id));
                    if($googlepopup == 6) {
                        $liclasses[] = "googlepopup";
                    }
                }

                if (count($liclasses)>0) {
                    $strLiclasses = implode(' ', $liclasses);
                }else {
                  $strLiclasses = '';
                }
                
                $modclasses = 'activity '. $mod->modname.' '.$strLiclasses.' '. ' modtype_'.$mod->modname. ' '. $mod->get_extra_classes();
                $output .= html_writer::start_tag('li', array('class' => $modclasses, 'id' => 'module-'. $mod->id, 'title'=>$modulehtml[3]));
                $output .= $modulehtml[0];
                $output .= html_writer::end_tag('li');
                
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $output .= html_writer::tag('li', html_writer::link($movingurl, $this->output->render($movingpix)),
                        array('class' => 'movehere', 'title' => $strmovefull));
            }

            $output .= html_writer::end_tag('ul'); // .section
        }

        return $output;
    }

}
