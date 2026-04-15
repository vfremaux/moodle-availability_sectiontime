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
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_sectiontime;

// phpcs:disable moodle.Commenting.ValidTags.Invalid

use coding_exception;
use core_availability\condition as core_condition;
use core_availability\info;
use context_system;
use context_course;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/use_stats/locallib.php');

/**
 * Week from course start condition.
 *
 * @package availability_coursetime
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends core_condition {

    /** @var int $sectionid */
    private $sectionid;

    /** @var int $timespent (Unix epoch seconds) for condition. */
    private $timespent;

    /** @var bool $allow */
    protected $allow;

    /** @var int $current */
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
            throw new coding_exception('Missing or invalid ->s for section condition');
        }

        if (isset($structure->t)) {
            $this->timespent = $structure->t;
        } else {
            throw new coding_exception('Missing or invalid ->t for time condition');
        }

        $this->current = '<span class="sectiontime-uncomplete">0m 0s</span>';
    }

    /**
     * Saves availability data.
     */
    public function save() {
        return (object)['type' => 'sectiontime',
                's' => $this->sectionid,
                't' => $this->timespent];
    }

    /**
     * Checks the target is available
     * @param bool $not
     * @param \core_availability\info $info
     * @param bool $grabthelot
     * @param int $userid
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        global $DB;

        $systemcontext = context_system::instance();
        if (has_capability('moodle/site:config', $systemcontext)) {
            return true;
        }

        // Check condition.
        if (!$section = $DB->get_record('course_sections', ['id' => $this->sectionid])) {
            return true;
        }

        if (!$DB->get_record('course', ['id' => $section->course])) {
            return true;
        }

        $coursecontext = context_course::instance($section->course);
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

    /**
     * Checks the target is globally available
     * @param bool $not
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function is_available_for_all($not = false) {
        global $USER, $DB;

        $config = get_config('availability_sectiontime');

        // Check condition.
        $now = self::get_time();

        $section = $DB->get_record('course_sections', ['id' => $this->sectionid]);
        $course = $DB->get_record('course', ['id' => $section->course]);

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

    /**
     * Gets a condition description for printing
     * @param bool $full
     * @param bool $not
     * @param info $info
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_description($full, $not, info $info) {
        return $this->get_either_description($not, false);
    }

    /**
     * Gets a condition description for printing
     * @param bool $full
     * @param bool $not
     * @param info $info
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_standalone_description(
            $full, $not, info $info) {
        return $this->get_either_description($not, true);
    }

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function get_either_description($not, $standalone) {
        global $DB;

        $satag = $standalone ? 'short_' : 'full_';

        $section = $DB->get_record('course_sections', ['id' => $this->sectionid], 'id,name,section');
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

    /**
     * Debug String.
     * @return int
     */
    protected function get_debug_string() {
        return $this->timespent.' in '.$this->sectionid;
    }

    /**
     * After restore function.
     * @param int $restoreid
     * @param int $courseid
     * @param \base_logger $logger
     * @param string $name
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update_after_restore($restoreid, $sectionid, \base_logger $logger, $name) {
        return true;
    }

    /**
     * Is this availability condition used ?
     */
    public static function use_sectiontime($courseid) {
        global $DB;

        $sectionavails = $DB->get_records('course_sections', ['course' => $courseid], 'id', 'id,availability');
        if ($sectionavails) {
            foreach ($sectionavails as $fa) {
                if (preg_match('/\bsectiontime\b/', $fa->availability)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check the section time grabbing it in cache if present.
     * @param object $section
     * @param ibt $userid
     */
    public function check($section, $userid) {
        global $DB;

        static $cache = []; // Request scope cache.
        static $cachecurrent = []; // Request scope cache.

        $config = get_config('availability_sectiontime');

        $now = self::get_time();

        $cachekey = $section->id.'_'.$userid;

        if (!array_key_exists($cachekey, $cache)) {

            $course = $DB->get_record('course', ['id' => $section->course]);
            $logs = use_stats_extract_logs($course->startdate, $now, $userid, $course->id);
            // Explicit transmission of course, as availability my be checked before require_login() sets course up.
            $aggregate = use_stats_aggregate_logs($logs, $course->startdate, $now, '', false, $course);
            // Timespent stored in minutes.

            // Timespent stored in minutes.
            if ($config->sectiondurationsource) {
                $this->allow = $aggregate['section'][$section->id]->elapsed >= $this->timespent * 60;
                $mins = floor(($aggregate['section'][$section->id]->elapsed ?? 0) / 60);
                $secs = ($aggregate['section'][$section->id]->elapsed ?? 0) - 60 * $mins;

                if ($this->timespent * 60 > ($aggregate['section'][$section->id]->elapsed ?? 0)) {
                    // Mark with red/uncomplete.
                    $this->current = '<span class="sectiontime-uncomplete">'.$mins.'m '.$secs.'s</span>';
                } else {
                    $this->current = '<span class="sectiontime-complete">'.$mins.'m '.$secs.'s</span>';
                }
            } else {
                $this->allow = ($aggregate['realsection'][$section->id]->elapsed ?? 0) >= $this->timespent * 60;
                $mins = floor(($aggregate['realsection'][$section->id]->elapsed ?? 0) / 60);
                $secs = ($aggregate['realsection'][$section->id]->elapsed ?? 0) - 60 * $mins;

                if ($this->timespent * 60 > ($aggregate['realsection'][$section->id]->elapsed ?? 0)) {
                    // Mark with red/uncomplete.
                    $this->current = '<span class="sectiontime-uncomplete">'.$mins.'m '.$secs.'s</span>';
                } else {
                    $this->current = '<span class="sectiontime-complete">'.$mins.'m '.$secs.'s</span>';
                }
            }
            $cache[$cachekey] = $this->allow;
            $cachecurrent[$cachekey] = $this->current;
        } else {
            $this->allow = $cache[$cachekey];
            $this->current = $cachecurrent[$cachekey];
        }
    }
}
