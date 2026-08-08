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

namespace qbank_tagassistant\hook;

use core\hook\output\before_standard_html_head as head_hook;

/**
 * Hook listener for before_standard_html_head targeting Moodle 5.1+.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_standard_html_head {
    /** @var bool Flag to prevent double execution. */
    public static bool $rendered = false;

    /**
     * Callback for before_standard_html_head hook.
     *
     * @param object|null $hook The hook instance.
     */
    public static function callback(?object $hook = null): void {
        global $PAGE;

        if (self::$rendered || !$PAGE) {
            return;
        }

        $pagetype = $PAGE->pagetype ?? '';
        $isquestioneditor = ($pagetype === 'question-bank-editquestion-question' ||
                             $pagetype === 'question-question' ||
                             $pagetype === 'question-bank-manage' ||
                             $pagetype === 'mod_quiz-edit' ||
                             strpos($pagetype, 'question-type-') === 0 ||
                             strpos($pagetype, 'question-bank-') === 0);

        if (!$isquestioneditor) {
            return;
        }

        $context = $PAGE->context;
        if (!$context || $context->contextlevel == CONTEXT_SYSTEM) {
            return;
        }

        $caneditall = has_capability('moodle/question:editall', $context);
        $caneditmine = has_capability('moodle/question:editmine', $context);
        if (!$caneditall && !$caneditmine) {
            return;
        }

        $toptags = \qbank_tagassistant\helper::get_context_top_tags($context->id, 15);
        if (empty($toptags)) {
            return;
        }

        self::$rendered = true;
        $headingtext = get_string('topquestionbanktags', 'qbank_tagassistant');

        $PAGE->requires->js_call_amd('qbank_tagassistant/tag_chips', 'init', [[
            'tags' => $toptags,
            'targetSelector' => 'select#id_tags, select[name="tags[]"]',
            'headingText' => $headingtext,
        ]]);
    }
}
