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
 * Library functions and callbacks for qbank_tagassistant.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 Antigravity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Callback before HTML head output to inject tag assistant on question editing pages.
 */
function qbank_tagassistant_before_standard_html_head(): void {
    global $PAGE;

    if (!$PAGE || !$PAGE->url) {
        return;
    }

    $pagepath = $PAGE->url->get_path();
    $isquestionedit = strpos($pagepath, 'question/bank/editquestion/question.php') !== false;
    $islegacyedit = strpos($pagepath, 'question/question.php') !== false;

    if ($isquestionedit || $islegacyedit) {
        $context = $PAGE->context;
        if (!$context) {
            return;
        }

        $toptags = \qbank_tagassistant\helper::get_context_top_tags($context->id, 15);
        if (empty($toptags)) {
            return;
        }

        $headingtext = get_string('topquestionbanktags', 'qbank_tagassistant');

        $PAGE->requires->js_call_amd('qbank_tagassistant/tag_chips', 'init', [[
            'tags' => $toptags,
            'targetSelector' => 'select#id_tags, select[name="tags[]"]',
            'headingText' => $headingtext,
        ]]);
    }
}
