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
require_once("$CFG->dirroot/mod/skillsaudit/locallib.php");

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
	return skillsaudit_get_user_summary($COURSE, $USER);
}
