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
 * Class tool_devcourse_api
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class tool_devcourse_api for various api methods
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_devcourse_api {

    /**
     * Retrieve an entry
     *
     * @param int $id id of the entry
     * @param int $courseid optional course id for validation
     * @param int $strictness
     * @return stdClass|bool retrieved object or false if entry not found and strictness is IGNORE_MISSING
     */
    public static function retrieve(int $id, int $courseid = 0, int $strictness = MUST_EXIST) {
        global $DB;
        $params = ['id' => $id];
        if ($courseid) {
            $params['courseid'] = $courseid;
        }
        return $DB->get_record('tool_devcourse', $params, '*', $strictness);
    }

    /**
     * Update an entry
     *
     * @param stdClass $data
     */
    public static function update(stdClass $data) {
        global $DB, $PAGE;
        if (empty($data->id)) {
            throw new coding_exception('Object data must contain property id');
        }
        if (isset($data->description_editor)) {
            $data = file_postupdate_standard_editor($data, 'description',
                self::editor_options(), $PAGE->context, 'tool_devcourse', 'entry', $data->id);
        }
        // Only fields name, completed, priority, description, descriptionformat can be modified.
        $updatedata = array_intersect_key((array)$data,
            ['id' => 1, 'name' => 1, 'completed' => 1, 'priority' => 1,
                'description' => 1, 'descriptionformat' => 1]);
        $updatedata['timemodified'] = time();
        $DB->update_record('tool_devcourse', $updatedata);

        // Trigger event.
        $entry = self::retrieve($data->id);
        $event = \tool_devcourse\event\entry_updated::create([
            'context' => context_course::instance($entry->courseid),
            'objectid' => $entry->id
        ]);
        $event->add_record_snapshot('tool_devcourse', $entry);
        $event->trigger();
    }

    /**
     * Insert an entry
     *
     * @param stdClass $data
     * @return int id of the new entry
     */
    public static function insert(stdClass $data) : int {
        global $DB;
        if (empty($data->courseid)) {
            throw new coding_exception('Object data must contain property courseid');
        }
        $insertdata = array_intersect_key((array)$data,
            ['courseid' => 1, 'name' => 1, 'completed' => 1, 'priority' => 1,
                'description' => 1, 'descriptionformat' => 1]);
        $insertdata['timemodified'] = $insertdata['timecreated'] = time();
        $entryid = $DB->insert_record('tool_devcourse', $insertdata);

        // Now when we know id update the description and save the files.
        if (isset($data->description_editor)) {
            $context = context_course::instance($data->courseid);
            $data = file_postupdate_standard_editor($data, 'description',
                self::editor_options(), $context, 'tool_devcourse', 'entry', $entryid);
            $updatedata = ['id' => $entryid, 'description' => $data->description,
                'descriptionformat' => $data->descriptionformat];
            $DB->update_record('tool_devcourse', $updatedata);
        }

        // Trigger event.
        $event = \tool_devcourse\event\entry_created::create([
            'context' => context_course::instance($data->courseid),
            'objectid' => $entryid
        ]);
        $event->trigger();

        return $entryid;
    }

    /**
     * Delete an entry
     *
     * @param int $id
     */
    public static function delete(int $id) {
        global $DB;
        if (!$entry = self::retrieve($id, 0, IGNORE_MISSING)) {
            return;
        }
        $DB->delete_records('tool_devcourse', ['id' => $id]);

        // Trigger event.
        $event = \tool_devcourse\event\entry_deleted::create([
            'context' => context_course::instance($entry->courseid),
            'objectid' => $entry->id
        ]);
        $event->add_record_snapshot('tool_devcourse', $entry);
        $event->trigger();
    }

    /**
     * Options for the description editor
     * @return array
     */
    public static function editor_options() {
        global $PAGE;
        return [
            'maxfiles' => -1,
            'maxbytes' => 0,
            'context' => $PAGE->context,
            'noclean' => true,
        ];
    }

    /**
     * Observer for course_deleted event - deletes all associated entries
     *
     * @param \core\event\course_deleted $event
     */
    public static function course_deleted_observer(\core\event\course_deleted $event) {
        global $DB;
        $DB->delete_records('tool_devcourse', ['courseid' => $event->objectid]);
    }
}