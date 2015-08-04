<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Local xray lang file
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
 
/**
 * Extend navigations block.
 */
function local_xray_extends_navigation(global_navigation $nav) {

	global $PAGE, $COURSE;

	if(is_callable('mr_on') && mr_on("xray", "_MR_LOCAL")) {
		
		if($COURSE->id != SITEID && has_capability('local/xray:view', $PAGE->context)) {
		
			$plugin = "local_xray";
			
			// TODO:: Check capabilities for access to each report.

			// Reports to show in course-view.	
			if($PAGE->pagetype == "course-view-".$COURSE->format) {
				
				// Add links on block navigation.
				$extranavigation = $PAGE->navigation->add(get_string('navigation_xray', $plugin));	
				
				// Activity report.
				$url = new moodle_url('/local/xray/view.php', array("controller" => "activityreport",
						                                            "courseid"   => $COURSE->id));
					
				$extranavigation->add(get_string('activityreport', $plugin),$url);
					
				// Discussion report.
				$url = new moodle_url('/local/xray/view.php', array("controller" => "discussionreport",
						                                            "courseid"   => $COURSE->id));
				$extranavigation->add(get_string('discussionreport', $plugin),$url);
				
				// Endogenic Plagiarism.
				$url = new moodle_url('/local/xray/view.php', array("controller" => "discussionendogenicplagiarism",
						                                            "courseid"   => $COURSE->id));				
				$extranavigation->add(get_string('discussionendogenicplagiarism', $plugin),$url);	
				
				// Risk.
				$url = new moodle_url('/local/xray/view.php', array("controller" => "risk",
						                                            "courseid"   => $COURSE->id));
				
				$extranavigation->add(get_string('risk', $plugin),$url);				
			}
			

			// Report to show in forum-view.
			if($PAGE->pagetype == "mod-forum-view") {
				
				// Add links on block navigation.
				$extranavigation = $PAGE->navigation->add(get_string('navigation_xray', $plugin));
				
				// Discussion report individual forum.
				$url = new moodle_url('/local/xray/view.php', array("controller" => "discussionreportindividualforum",
						                                            "courseid"   => $COURSE->id,
						                                            "forum" => $PAGE->context->instanceid));
				
				$extranavigation->add(get_string('discussionreportindividualforum', $plugin),$url);					
			}

		}

	}
}
