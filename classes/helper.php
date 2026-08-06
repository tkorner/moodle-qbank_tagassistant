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

use cache;

/**
 * Helper class for qbank_tagassistant.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 Antigravity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Get the most frequently used question tags for a specific Question Bank context.
     *
     * @param int $contextid The context ID of the Question Bank.
     * @param int $limit Maximum number of top tags to return (default 15).
     * @return array Array of tag objects with id, name, and questioncount properties.
     */
    public static function get_context_top_tags(int $contextid, int $limit = 15): array {
        global $DB;

        if ($contextid <= 0) {
            return [];
        }

        $cache = cache::make('qbank_tagassistant', 'context_tags');
        $cachekey = "ctx_{$contextid}_limit_{$limit}";
        $cacheddata = $cache->get($cachekey);
        if ($cacheddata !== false) {
            return $cacheddata;
        }

        $sql = "SELECT t.id, t.rawname AS name, COUNT(DISTINCT qbe.id) AS questioncount
                  FROM {tag} t
                  JOIN {tag_instance} ti ON ti.tagid = t.id AND ti.itemtype = :itemtype AND ti.component = :component
                  JOIN {question} q ON q.id = ti.itemid
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                 WHERE qc.contextid = :contextid
                   AND qv.version = (
                       SELECT MAX(v.version)
                         FROM {question_versions} v
                        WHERE v.questionbankentryid = qbe.id
                          AND v.status = :status
                   )
              GROUP BY t.id, t.rawname
              ORDER BY questioncount DESC, t.rawname ASC";

        $params = [
            'itemtype' => 'question',
            'component' => 'core_question',
            'contextid' => $contextid,
            'status' => 'ready',
        ];

        $records = $DB->get_records_sql($sql, $params, 0, $limit);
        $result = [];
        foreach ($records as $record) {
            $result[] = [
                'id' => (int) $record->id,
                'name' => $record->name,
                'count' => (int) $record->questioncount,
            ];
        }

        $cache->set($cachekey, $result);
        return $result;
    }

    /**
     * Purge the tag assistant cache for a context or all contexts.
     *
     * @param int|null $contextid Specific context ID to purge, or null to purge all.
     */
    public static function purge_context_tags_cache(?int $contextid = null): void {
        $cache = cache::make('qbank_tagassistant', 'context_tags');
        if ($contextid !== null) {
            $cache->delete("ctx_{$contextid}_limit_15");
            $cache->delete("ctx_{$contextid}_limit_10");
            $cache->delete("ctx_{$contextid}_limit_20");
        } else {
            $cache->purge();
        }
    }
}
