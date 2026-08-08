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

use advanced_testcase;
use core_question\local\bank\question_bank_helper;
use core_tag_tag;
use context_module;

/**
 * Unit tests for qbank_tagassistant helper.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \qbank_tagassistant\helper
 */
final class helper_test extends advanced_testcase {
    /**
     * Create a course-level question bank and return its (module) context.
     *
     * A course-level "Question Bank Context" is a dedicated qbank activity module, not the
     * course context itself: question_categories created for a course context are transparently
     * redirected by Moodle to the course's default qbank module context. Tests must create
     * categories directly against that module context to match what a real question bank page
     * (and its $PAGE->context) actually uses.
     *
     * @param \stdClass $course
     * @return \context_module
     */
    private function create_qbank_context(\stdClass $course): context_module {
        $qbankcm = question_bank_helper::get_default_open_instance_system_type($course, true);
        return context_module::instance($qbankcm->id);
    }

    /**
     * Test get_context_top_tags returns tags in the specified context.
     */
    public function test_get_context_top_tags_returns_tags_in_context(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = $this->create_qbank_context($course);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category(['contextid' => $context->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        $q2 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);

        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context, ['Algebra', 'Geometrie']);
        core_tag_tag::set_item_tags('core_question', 'question', $q2->id, $context, ['Algebra']);

        helper::purge_context_tags_cache($context->id);
        $tags = helper::get_context_top_tags($context->id);

        $this->assertCount(2, $tags);
        $this->assertEquals('Algebra', $tags[0]['name']);
        $this->assertEquals(2, $tags[0]['count']);

        $this->assertEquals('Geometrie', $tags[1]['name']);
        $this->assertEquals(1, $tags[1]['count']);
    }

    /**
     * Test get_context_top_tags strictly respects context boundary.
     */
    public function test_get_context_top_tags_filters_by_context(): void {
        $this->resetAfterTest();

        $course1 = $this->getDataGenerator()->create_course();
        $context1 = $this->create_qbank_context($course1);

        $course2 = $this->getDataGenerator()->create_course();
        $context2 = $this->create_qbank_context($course2);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat1 = $qgenerator->create_question_category(['contextid' => $context1->id]);
        $cat2 = $qgenerator->create_question_category(['contextid' => $context2->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $cat1->id]);
        $q2 = $qgenerator->create_question('shortanswer', null, ['category' => $cat2->id]);

        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context1, ['Physik']);
        core_tag_tag::set_item_tags('core_question', 'question', $q2->id, $context2, ['Chemie']);

        helper::purge_context_tags_cache();

        $tags1 = helper::get_context_top_tags($context1->id);
        $this->assertCount(1, $tags1);
        $this->assertEquals('Physik', $tags1[0]['name']);

        $tags2 = helper::get_context_top_tags($context2->id);
        $this->assertCount(1, $tags2);
        $this->assertEquals('Chemie', $tags2[0]['name']);
    }

    /**
     * A question_bank_entry whose latest version is a draft (not 'ready') must still be counted
     * via its latest *ready* version, not silently dropped or double-counted.
     */
    public function test_get_context_top_tags_uses_latest_ready_version_not_draft(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = $this->create_qbank_context($course);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category(['contextid' => $context->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context, ['Biologie']);

        // Simulate a newer draft version of the same question_bank_entry. All three fields
        // must be written in a single update: moving questionbankentryid first would briefly
        // collide with the existing version 1 row on the (questionbankentryid, version)
        // unique index.
        $readyversion = $DB->get_record('question_versions', ['questionid' => $q1->id]);
        $draftquestion = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        $draftversion = $DB->get_record('question_versions', ['questionid' => $draftquestion->id]);
        $draftversion->questionbankentryid = $readyversion->questionbankentryid;
        $draftversion->version = $readyversion->version + 1;
        $draftversion->status = 'draft';
        $DB->update_record('question_versions', $draftversion);

        helper::purge_context_tags_cache($context->id);
        $tags = helper::get_context_top_tags($context->id);

        $this->assertCount(1, $tags);
        $this->assertEquals('Biologie', $tags[0]['name']);
        $this->assertEquals(1, $tags[0]['count']);
    }

    /**
     * A question_bank_entry with no 'ready' version at all must be excluded, not error out.
     */
    public function test_get_context_top_tags_excludes_entries_without_ready_version(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = $this->create_qbank_context($course);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category(['contextid' => $context->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context, ['Physik']);
        $DB->set_field('question_versions', 'status', 'draft', ['questionid' => $q1->id]);

        helper::purge_context_tags_cache($context->id);
        $tags = helper::get_context_top_tags($context->id);

        $this->assertCount(0, $tags);
    }

    /**
     * The cache must not silently orphan entries when different callers request different limits
     * for the same context: a single per-context cache entry backs all requested limits.
     */
    public function test_get_context_top_tags_respects_limit_after_cache_populated(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = $this->create_qbank_context($course);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category(['contextid' => $context->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        $q2 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context, ['Algebra']);
        core_tag_tag::set_item_tags('core_question', 'question', $q2->id, $context, ['Geometrie']);

        helper::purge_context_tags_cache($context->id);

        // Populate the cache via a call with the default limit, then request a smaller limit.
        $this->assertCount(2, helper::get_context_top_tags($context->id));
        $this->assertCount(1, helper::get_context_top_tags($context->id, 1));
    }

    /**
     * The event observer must purge the per-context cache so a newly tagged question is
     * reflected immediately rather than only after the cache's fallback TTL expires.
     */
    public function test_observer_purges_cache_on_question_created_event(): void {
        if (!class_exists(\core\event\question_created::class)) {
            $this->markTestSkipped('This Moodle version does not expose \core\event\question_created.');
        }

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = $this->create_qbank_context($course);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category(['contextid' => $context->id]);

        $q1 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        core_tag_tag::set_item_tags('core_question', 'question', $q1->id, $context, ['Chemie']);

        helper::purge_context_tags_cache($context->id);
        $primed = helper::get_context_top_tags($context->id);
        $this->assertEquals(1, $primed[0]['count']);

        // Tag a second question without purging the cache directly, only via the observer.
        $q2 = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);
        core_tag_tag::set_item_tags('core_question', 'question', $q2->id, $context, ['Chemie']);

        $event = \core\event\question_created::create([
            'objectid' => $q2->id,
            'context' => $context,
            'other' => ['categoryid' => $category->id],
        ]);
        \qbank_tagassistant\observer::question_changed($event);

        $fresh = helper::get_context_top_tags($context->id);
        $this->assertEquals(2, $fresh[0]['count']);
    }
}
