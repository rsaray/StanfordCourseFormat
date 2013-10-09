<?php
require_once('../../../config.php');
include 'Mobile_Detect.php';


require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
// $u        = optional_param('u', 0, PARAM_INT);         // URL instance id
// $redirect = optional_param('redirect', 0, PARAM_BOOL);

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
    
        echo '<!DOCTYPE html>
<html  dir="ltr" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="user-scalable=yes, initial-scale=1.0, maximum-scale=2.0, width=device-width" />
<meta name="keywords" content="" />';    

$headeroutput = '<meta http-equiv="pragma" content="no-cache" />';
$headeroutput .= '<meta http-equiv="expires" content="0" />';
    // $headeroutput .= '<link rel="stylesheet" href="silverlight/css/boilerplate.css">';
echo $headeroutput;

echo "</head><body id='pageLecture'>";
?>
	<video controls src="<?php echo $exturl; ?>"></video>
<?php
echo '<script type="text/javascript" src="silverlight/js/libs/pdfobject.js"></script>
<script type="text/javascript" src="silverlight/js/lecTure.js"></script>
<script type="text/javascript" src="silverlight/js/pdfservice.js"></script>';
echo "</body></html>";
    


