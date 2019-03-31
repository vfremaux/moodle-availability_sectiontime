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

$string['privacy:metadata'] = "La condition de disponibilité par temps passé sur section ne détient aucune donnée utilisateur.";

$string['description'] = 'Empêche l\'accès avant q\'un certain temps soit passé dans une certaine section.';
$string['pluginname'] = 'Restriction sur temps passé dans une certaine section';
$string['full_sectiontime'] = 'Disponible lorsque le temps passé dans la section <strong>{$a->name}</strong> excède <strong>{$a->timespent}</strong>';
$string['short_sectiontime'] = 'Disponible après <strong>{$a->timespent}</strong> passés dans la section <strong>{$a->name}</strong>';
$string['title'] = 'Temps passé (section)';
$string['insection'] = ' minutes passées dans la section ';
$string['conditiontitle'] = 'Plus de ';
$string['error_nulltimespent'] = 'Vous devez entrer un nombre';
$string['error_nosection'] = 'Vous devez choisir une section de référence';
$string['elapsedinsection'] = 'Temps passé dans cette section : {$a} ';

