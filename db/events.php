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
 * Event observers for qbank_tagassistant.
 *
 * NOTE: '::class' resolves to a string at parse time without requiring the class to be
 * loadable, so if a future Moodle core release renames/removes one of these event classes,
 * registration stays safe (the observer simply never fires for that event) rather than
 * fataling. The cache's short TTL (db/caches.php) is the fallback safety net for that case.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => \core\event\question_created::class,
        'callback' => [\qbank_tagassistant\observer::class, 'question_changed'],
    ],
    [
        'eventname' => \core\event\question_updated::class,
        'callback' => [\qbank_tagassistant\observer::class, 'question_changed'],
    ],
    [
        'eventname' => \core\event\question_deleted::class,
        'callback' => [\qbank_tagassistant\observer::class, 'question_changed'],
    ],
];
