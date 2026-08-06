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
use core_tag_tag;
use context_course;

/**
 * Unit tests for qbank_tagassistant helper.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 Antigravity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \qbank_tagassistant\helper
 */
final class helper_test extends advanced_testcase {

    /**
     * Test get_context_top_tags returns tags in the specified context.
     */
    public function test_get_context_top_tags_returns_tags_in_context(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

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
        $context1 = context_course::instance($course1->id);

        $course2 = $this->getDataGenerator()->create_course();
        $context2 = context_course::instance($course2->id);

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
}
