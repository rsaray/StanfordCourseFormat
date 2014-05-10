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
 * @since Moodle 2.6
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/renderer.php');

/**
* Stanford course format core renderer
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

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
public function stanford_course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER,$DB;
        
        // ChromePhp::log("stanford course section course module list");

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
                $modNext = new stdClass();
                $modNext->modname = '';
                $mod = $modinfo->cms[$modnumber];
                
                if(!empty($modinfo->sections[$section->section][$key+1])) {
                  $modNext = $modinfo->cms[$modinfo->sections[$section->section][$key+1]];    
                }

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }
                $instancename = $mod->get_formatted_name();
                if ($modulehtml = $this->stanford_course_section_cm($course,
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
            $resourcefirstchild = 0;
            foreach ($moduleshtml as $modnumber => $modulehtml) {
            	if($elementCounter == 0){
            		$resourcefirstchild = 1;
            	}
                $elementCounter ++;
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $output .= html_writer::tag('li', html_writer::link($movingurl, $this->output->render($movingpix)),
                            array('class' => 'movehere', 'title' => $strmovefull));
                }

                $mod = $modinfo->cms[$modnumber];

                $liclasses = array();
                
                if($resourcefirstchild == 1 && $mod->modname == 'resource'){
                	$liclasses[] = 'resourcefirstchild';
                	$resourcefirstchild = 0;
                }

                if($totalElement == 1) {
                  $liclasses[] = 'forumfirstchild';      
                }
                
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
                if($modulehtml[1] == 'lastchild' && $mod->modname == 'resource') {
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

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link cm_info::get_after_link()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function stanford_course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2a) The 'showavailability' option is not set (if that is set,
        //     we need to display the activity so we can show
        //     availability info)
        // or
        // 2b) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->uservisible &&
            (empty($mod->showavailability) || empty($mod->availableinfo))) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }
        $indentclassouter = 'mod-indent-outer';
        // if (!empty($mod->indent)) {
        //     $indentclassouter .= ' mod-indent-'.$mod->indent;
        //     if ($mod->indent > 15) {
        //         $indentclassouter .= ' mod-indent-huge';
        //     }
        // }
        $output .= html_writer::start_tag('div', array('class' => $indentclassouter));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent
        $output .= html_writer::start_tag('div');

        // Display the link to the module (or do nothing if module has no url)
//         $cmname = $this->course_section_cm_name($mod, $displayoptions);
        
        $cmname= $this->stanford_course_section_cm_name($course, $completioninfo, $mod, $displayoptions);
        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;


            if ($this->page->user_is_editing()) {
                $output .= ' ' . course_get_cm_rename_action($mod, $sectionreturn);
            }

            // Module can put text after the link (e.g. forum unread)
            $output .= $mod->get_after_link();

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->get_url();
        if (empty($url)) {
            $output .= $contentpart;
        }

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->get_after_edit_icons();
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        }

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before
        if (!empty($url)) {
            $output .= $contentpart;
        }

        // show availability info (if module is not available)
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        $output .= html_writer::end_tag('div'); // $indentclasses

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }

	/**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function stanford_course_section_cm_name($course, &$completioninfo, cm_info $mod, $displayoptions = array()) {
        global $CFG,$PAGE;
        $output = '';
        if (!$mod->uservisible &&
                (empty($mod->showavailability) || empty($mod->availableinfo))) {
            // nothing to be displayed to the user
            return $output;
        }
        $url = $mod->get_url();
        if (!$url) {
            return $output;
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        if($mod->modfullname == 'Rich Media') {
        	$altname = 'Video';
        }
        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        // For items which are hidden but available to current user
        // ($mod->uservisible), we show those as dimmed only if the user has
        // viewhiddenactivities, so that teachers see 'items which might not
        // be available to some students' dimmed but students do not see 'item
        // which is actually available to current student' dimmed.
        $linkclasses = '';
        $accesstext = '';
        $textclasses = '';
        if ($mod->uservisible) {
            $conditionalhidden = $this->is_cm_conditionally_hidden($mod);
            $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
                has_capability('moodle/course:viewhiddenactivities',
                        context_course::instance($mod->course));
            if ($accessiblebutdim) {
                $linkclasses .= ' dimmed';
                $textclasses .= ' dimmed_text';
                if ($conditionalhidden) {
                    $linkclasses .= ' conditionalhidden';
                    $textclasses .= ' conditionalhidden';
                }
                // Show accessibility note only if user can access the module himself.
                $accesstext = get_accesshide(get_string('hiddenfromstudents').':'. $mod->modfullname);
            }
        } else {
            $linkclasses .= ' dimmed';
            $textclasses .= ' dimmed_text';
        }

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->get_on_click(), ENT_QUOTES);

        $groupinglabel = '';
        if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', context_course::instance($mod->course))) {
            $groupings = groups_get_all_groupings($mod->course);
            $groupinglabel = html_writer::tag('span', '('.format_string($groupings[$mod->groupingid]->name).')',
                    array('class' => 'groupinglabel '.$textclasses));
        }

        if ($completioninfo === null) {
        	$completioninfo = new completion_info($course);
        }
        $completiondata = $completioninfo->get_data($mod, true);
        $newstate = $completiondata->completionstate == COMPLETION_COMPLETE? COMPLETION_INCOMPLETE: COMPLETION_COMPLETE;
        
        // Display link itself.
        $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')) . $accesstext .
                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
        if ($mod->uservisible) {
//             $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
//                     $groupinglabel;
        	if($mod->modfullname == 'Rich Media' || $mod->modfullname == 'Quiz' ){
        		if($newstate !== COMPLETION_COMPLETE){
              if(has_capability('mod/quiz:preview', context_course::instance($mod->course)) && $mod->modfullname == 'Quiz') {
                $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
                    $groupinglabel;
              }else {
          			$output .= html_writer::link("javascript:void(0);", $activitylink, array('class' => $linkclasses." done", 'onclick' => $onclick,'data-url'=>$url,'data-moduleid'=>$mod->id)) .
          			$groupinglabel;
              }
        		}else {
              if(has_capability('mod/quiz:preview', context_course::instance($mod->course)) && $mod->modfullname == 'Quiz') {
                $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
                    $groupinglabel;
              }else {
          			$output .= html_writer::link("javascript:void(0);", $activitylink, array('class' => $linkclasses, 'onclick' => $onclick,'data-url'=>$url,'data-moduleid'=>$mod->id)) .
          			$groupinglabel;
              }
        		}
        	}else if($mod->module == 22){
            $output .= html_writer::link("javascript:void(0);", $activitylink, array('class' => $linkclasses, 'onclick' => $onclick,'data-url'=>$url,'data-moduleid'=>$mod->id)) .
              $groupinglabel;
          }else {
        		$output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
        		$groupinglabel;
        	}
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->uservisible)
            $output .= html_writer::tag('div', $activitylink, array('class' => $textclasses)) .
                    $groupinglabel;
        }
        return $output;
    }    
}