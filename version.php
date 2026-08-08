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
 * Version details for qbank_tagassistant.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026080800;
// Requires Moodle 5.1+ only (4.5/5.0 support dropped in v3.0.0 to drop the legacy
// non-hook code path entirely). Confirmed against real Moodle 5.1.5 core
// (version.php $version = 2025100605.00) and Moodle 5.2.1 core via Docker testing.
$plugin->requires  = 2025100600;
$plugin->component = 'qbank_tagassistant';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v3.0.0';
