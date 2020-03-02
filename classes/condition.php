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
 * Date condition.
 *
 * @package availability_sectiontime
 * @copyright 2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_sectiontime;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/use_stats/locallib.php');

/**
 * Week from course start condition.
 *
 * @package availability_coursetime
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int Time (Unix epoch seconds) for condition. */
    private $sectionid;
    private $timespent;

    protected $allow;

    protected $current;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get section to check time.
        if (isset($structure->s)) {
            $this->sectionid = $structure->s;
        } else {
            throw new \coding_exception('Missing or invalid ->s for section condition');
        }

        if (isset($structure->t)) {
            $this->timespent = $structure->t;
        } else {
            throw new \coding_exception('Missing or invalid ->t for time condition');
        }

        $this->current = '<span class="sectiontime-uncomplete">0m 0s</span>';
    }

    public function save() {
        return (object)array('type' => 'sectiontime',
                's' => $this->sectionid,
                't' => $this->timespent);
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;

        $systemcontext = \context_system::instance();
        if (has_capability('moodle/site:config', $systemcontext)) {
            return true;
        }

        // Check condition.
        $now = self::get_time();

        if (!$section = $DB->get_record('course_sections', array('id' => $this->sectionid))) {
            return true;
        }

        if (!$course = $DB->get_record('course', array('id' => $section->course))) {
            return true;
        }

        $coursecontext = \context_course::instance($section->course);
        if (has_capability('moodle/course:manageactivities', $coursecontext)) {
            // People who can edit course do not need playing condition.
            return true;
        }

        $this->check($section, $userid);

        if ($not) {
            $this->allow = !$this->allow;
        }

        return $this->allow;
    }

    public function is_available_for_all($not = false) {
        global $CFG, $USER, $DB;

        $config = get_config('availability_sectiontime');

        // Check condition.
        $now = self::get_time();

        $section = $DB->get_record('course_sections', array('id' => $this->sectionid));
        $course = $DB->get_record('course', array('id' => $section->course));

        $logs = use_stats_extract_logs($course->startdate, $now, $USER->id, $course->id);
        $aggregate = use_stats_aggregate_logs($logs, $course->startdate, $now);

        // Timespent stored in minutes.
        if ($config->sectiondurationsource) {
            $allow = $aggregate['section'][$section->id]->elapsed >= $this->timespent * 60;
        } else {
            $allow = $aggregate['realsection'][$section->id]->elapsed >= $this->timespent * 60;
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, false);
    }

    public function get_standalone_description(
            $full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, true);
    }

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     */
    protected function get_either_description($not, $standalone) {
        global $DB;

        $satag = $standalone ? 'short_' : 'full_';

        $section = $DB->get_record('course_sections', array('id' => $this->sectionid), 'id,name,section');
        if (empty($section->name)) {
            $section->name = 'Section '.$section->section;
        }
        $hours = floor($this->timespent / 60);
        $mins = $this->timespent - $hours * 60;
        $timespentstr = '';
        if ($hours) {
            $timespentstr .= $hours.'h ';
        }
        $timespentstr .= $mins.'m ';
        $section->timespent = $timespentstr;
        $section->current = $this->current;

        return get_string($satag . 'sectiontime', 'availability_sectiontime', $section);
    }

    /**
     * Gets time. This function is implemented here rather than calling time()
     * so that it can be overridden in unit tests. (Would really be nice if
     * Moodle had a generic way of doing that, but it doesn't.)
     *
     * @return int Current time (seconds since epoch)
     */
    protected static function get_time() {
        return time();
    }

    protected function get_debug_string() {
        return $this->timespent.' in '.$this->sectionid;
    }

    public function update_after_restore($restoreid, $sectionid, \base_logger $logger, $name) {
        return true;
    }

    public static function use_sectiontime($courseid) {
        global $DB;

        $sectionavailabilities = $DB->get_records('course_sections', ['course' => $courseid], 'id', 'id,availability');
        if ($sectionavailabilities) {
            foreach ($sectionavailabilities as $fa) {
                if (preg_match('/\bsectiontime\b/', $fa->availability)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function check($section, $userid) {
        global $CFG, $DB;

        $now = self::get_time();

        $course = $DB->get_record('course', ['id' => $section->course]);
        require_once($CFG->dirroot.'/blocks/use_stats/locallib.php');
        $logs = use_stats_extract_logs($course->startdate, $now, $userid, $course->id);
        // Explicit transmission of course, as availability my be checked before require_login() sets course up.
        $aggregate = use_stats_aggregate_logs($logs, $course->startdate, $now, '', false, $course);
        // Timespent stored in minutes.

        $this->allow = @$aggregate['section'][$section->id]->elapsed >= $this->timespent * 60;
        $mins = floor((0 + @$aggregate['section'][$section->id]->elapsed) / 60);
        $secs = (0 + @$aggregate['section'][$section->id]->elapsed) - 60 * $mins;
        if ($this->timespent * 60 > @$aggregate['section'][$section->id]->elapsed) {
            // mark with red/uncomplete.
            $this->current = '<span class="sectiontime-uncomplete">'.$mins.'m '.$secs.'s</span>';
        } else {
            $this->current = '<span class="sectiontime-complete">'.$mins.'m '.$secs.'s</span>';
        }
    }

}
