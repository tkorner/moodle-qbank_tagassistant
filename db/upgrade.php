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
 * Upgrade steps for qbank_tagassistant.
 *
 * @package    qbank_tagassistant
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for qbank_tagassistant.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_qbank_tagassistant_upgrade(int $oldversion): bool {
    if ($oldversion < 2026080800) {
        // v3.0.0 changed the context_tags cache key structure from one entry per
        // (context, limit) pair to one entry per context. Purge so no site is left
        // serving cache entries in the old, now-unused key format.
        \cache_helper::purge_by_definition('qbank_tagassistant', 'context_tags');

        upgrade_plugin_savepoint(true, 2026080800, 'qbank', 'tagassistant');
    }

    return true;
}
