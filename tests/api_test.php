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
 * API tests.
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\invalid_persistent_exception;
use core\task\manager;
use tool_dataprivacy\context_instance;
use tool_dataprivacy\api;
use tool_dataprivacy\data_registry;
use tool_dataprivacy\expired_context;
use tool_dataprivacy\data_request;
use tool_dataprivacy\local\helper;
use tool_dataprivacy\task\initiate_data_request_task;
use tool_dataprivacy\task\process_data_request_task;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * API tests.
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_devcourse_api_testcase extends advanced_testcase {

    /**
     * Set up for the tests.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test for tool_devcourse_api::insert and tool_devcourse_api::retrieve
     */
    public function test_insert() {
        $course = $this->getDataGenerator()->create_course();
        $entryid = tool_devcourse_api::insert((object)[
            'courseid' => $course->id,
            'name' => 'testname1',
            'completed' => 1,
            'priority' => 0,
            'description' => 'description plain'
        ]);
        $entry = tool_devcourse_api::retrieve($entryid);
        $this->assertEquals($course->id, $entry->courseid);
        $this->assertEquals('testname1', $entry->name);
        $this->assertEquals('description plain', $entry->description);
    }

    /**
     * Test for tool_devcourse_api::update
     */
    public function test_update() {
        $course = $this->getDataGenerator()->create_course();
        $entryid = tool_devcourse_api::insert((object)[
            'courseid' => $course->id,
            'name' => 'testname1'
        ]);
        tool_devcourse_api::update((object)[
            'id' => $entryid,
            'name' => 'testname2',
            'description' => 'description plain'
        ]);
        $entry = tool_devcourse_api::retrieve($entryid);
        $this->assertEquals($course->id, $entry->courseid);
        $this->assertEquals('testname2', $entry->name);
        $this->assertEquals('description plain', $entry->description);
    }

    /**
     * Test for tool_devcourse_api::delete
     */
    public function test_delete() {
        $course = $this->getDataGenerator()->create_course();
        $entryid = tool_devcourse_api::insert((object)[
            'courseid' => $course->id,
            'name' => 'testname1'
        ]);
        tool_devcourse_api::delete($entryid);
        $entry = tool_devcourse_api::retrieve($entryid, 0, IGNORE_MISSING);
        $this->assertEmpty($entry);
    }

    public function test_description_editor() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $entryid = tool_devcourse_api::insert((object)[
            'courseid' => $course->id,
            'name' => 'testname1',
            'description_editor' => [
                'text' => 'description formatted',
                'format' => FORMAT_HTML,
                'itemid' => file_get_unused_draft_itemid()
            ]
        ]);
        $entry = tool_devcourse_api::retrieve($entryid);
        $this->assertEquals('description formatted', $entry->description);
        tool_devcourse_api::update((object)[
            'id' => $entryid,
            'name' => 'testname2',
            'description_editor' => [
                'text' => 'description edited',
                'format' => FORMAT_HTML,
                'itemid' => file_get_unused_draft_itemid()
            ]
        ]);
        $entry = tool_devcourse_api::retrieve($entryid);
        $this->assertEquals($course->id, $entry->courseid);
        $this->assertEquals('testname2', $entry->name);
        $this->assertEquals('description edited', $entry->description);

    }
}
