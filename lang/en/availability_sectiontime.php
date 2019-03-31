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
 * Language strings.
 *
 * @package availability_sectiontime
 * @copyright 2016 Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['privacy:metadata'] = "The Section Time Availability condition do not manipulate any data belonging to users";

$string['description'] = 'Prevent access until a certain amount of time has been spent into a given section.';
$string['pluginname'] = 'Restriction by timespent in a course section';
$string['full_sectiontime'] = 'Available when time spent in section <strong>{$a->name}</strong> exceeds <strong>{$a->timespent}</strong>';
$string['short_sectiontime'] = 'Available after <strong>{$a->timespent}</strong> spent in section <strong>{$a->name}</strong>';
$string['title'] = 'Time spent (section)';
$string['insection'] = ' in section ';
$string['conditiontitle'] = 'Over minutes spent ';
$string['error_nulltimespent'] = 'You must enter a number';
$string['error_nosection'] = 'You must choose a reference course section';
$string['elapsedinsection'] = 'you have spent in this section: {$a} ';

