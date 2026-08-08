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

namespace qbank_tagassistant;

/**
 * Event observer for qbank_tagassistant.
 *
 * Invalidates the context_tags cache whenever a question is created, updated, or deleted,
 * since any of these can change the tag/question-count aggregation for the affected context.
 * This is the primary cache-invalidation mechanism; the cache's own short TTL (see db/caches.php)
 * is only a fallback safety net, not the intended invalidation path.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Purge the cached top-tags list for the context affected by a question event.
     *
     * @param \core\event\base $event The triggered event.
     */
    public static function question_changed(\core\event\base $event): void {
        $contextid = null;
        try {
            $contextid = $event->get_context()->id;
        } catch (\Throwable $e) {
            $contextid = null;
        }

        helper::purge_context_tags_cache($contextid);
    }
}
