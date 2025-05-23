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
 * Version info.
 *
 * @package availability_sectiontime
 * @copyright 2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025022100;
$plugin->requires = 2022041900;
$plugin->component = 'availability_sectiontime';
$plugin->release = '4.5.0 (Build 2025022100)';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = array('block_use_stats' => '2019290300');
$plugin->supported = [401, 405];

// Non moodle attribute.
$plugin->codeincrement = '4.5.0002';