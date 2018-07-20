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
 * tool_devcourse upgrade script.
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Run all upgrade steps between the current DB version and the current version on disk.
 *
 * @param int $oldversion The old version of atto equation in the DB.
 * @return bool
 */
function xmldb_tool_devcourse_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018072005) {

        // Define table tool_devcourse to be created.
        $table = new xmldb_table('tool_devcourse');

        // Adding fields to table tool_devcourse.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('completed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('priority', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table tool_devcourse.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for tool_devcourse.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Devcourse savepoint reached.
        upgrade_plugin_savepoint(true, 2018072005, 'tool', 'devcourse');
    }

    return true;
}
