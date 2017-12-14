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
 * Filter showing a summary of all skillsaudit results for the current course
 *
 * @package    filter
 * @subpackage skillsaudit
 * @copyright  2017 pddring pddring@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/gradelib.php");

class filter_skillsaudit extends moodle_text_filter {

    /**
     * @var array global configuration for this filter
     *
     * This might be eventually moved into parent class if we found it
     * useful for other filters, too.
     */
    protected static $globalconfig;

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
		$search = '/\[SkillsAuditSummary\]/';
		$text = preg_replace_callback($search, 'filter_skillsaudit_callback', $text);
        return $text;
    }
	
	public static function get_rating_bar($percentage, $label) {
		/*$background = 'linear-gradient(to right,red,hsl(' . round($percentage * 120.0 / 100.0) .',100%,50%))';
		return '<span class="conf_ind_cont" title="' . $percentage . '"><span class="conf_ind" style="width:' . $percentage . '%; background: ' . $background . '"></span>';*/
		
		$h = 120 * $percentage / 100;
		$d = 180 - (180 * $percentage / 100);
		$style = 'background-color: hsl(' . $h . ',100%,50%);transform:rotate(' . $d . 'deg)';
		$html = '<span class="wrist"><span class="thumb" style="' . $style . '"></span></span><p>' . $label . ' <span class="summary_value">' . $percentage . '%</span></p>';
		return $html;
	}

}
/**
 * Replace [SkillsAuditSummary] with a summary of all skillsaudit results
 *
 *
 * @param  $link
 * @return string
 */
function filter_skillsaudit_callback($text) {
	global $COURSE, $USER;
	$table = new html_table();
	$table->attributes['class'] = 'generaltable mod_index';
	
	$strname = get_string('modulenameplural', 'mod_skillsaudit');
	$table->align = array ('center', 'left', 'center', 'center', 'center');
	

	$modinfo = get_fast_modinfo($COURSE);
	
	$strongest = array('row'=>array(), 'total'=>0);
	$weakest = array('row'=>array(), 'total'=>100);
	
	foreach ($modinfo->instances['skillsaudit'] as $cm) {
		$row = array();	
		$class = $cm->visible ? null : array('class' => 'dimmed');
	
		$row[] = html_writer::link(new moodle_url('/mod/skillsaudit/view.php', array('id' => $cm->id)),
					'<img src="' . $cm->get_icon_url() . '"> ' . $cm->get_formatted_name(), $class);
					
		$grading_info = grade_get_grades($COURSE->id, 'mod', 'skillsaudit', $cm->instance, array($USER->id));
 
		$grade_item_grademax = $grading_info->items[0]->grademax;
		$confidence = intval($grading_info->items[0]->grades[$USER->id]->grade);
		$coverage = intval($grading_info->items[2]->grades[$USER->id]->grade);
		$total = $coverage * $confidence / 100;
		
		$row[] = filter_skillsaudit::get_rating_bar($coverage, 'Coverage');
		$row[] = filter_skillsaudit::get_rating_bar($confidence, 'Confidence');
		
		if($total > $strongest['total']) {
			$strongest['total'] = $total;
			array_unshift($row, '<h3>Your strongest area:</h3> ');
			$strongest['row'] = $row;
		}
		
		if($total < $weakest['total']) {
			$weakest['total'] = $total;
			array_unshift($row, '<h3>Suggested target:</h3> ');
			$weakest['row'] = $row;
		}
	}
	
	$table->data[] = $strongest['row'];
	$table->data[] = $weakest['row'];
	/*$coverage = 0;
	$confidence = 0;
	$table->data[] = array('<h3>Total</h3>', '', filter_skillsaudit::get_rating_bar($coverage, 'Coverage'), filter_skillsaudit::get_rating_bar($coverage, 'Coverage'));*/
	
	return html_writer::table($table);
}
