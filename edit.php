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
 * Editing or creating entries
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$id = optional_param('id', 0, PARAM_INT);
if ($id) {
    // We are going to edit an entry.
    $entry = $DB->get_record('tool_devcourse', ['id' => $id], '*', MUST_EXIST);
    $courseid = $entry->courseid;
    $urlparams = ['id' => $id];
    $title = get_string('newentry', 'tool_devcourse');
} else {
    // We are going to add an entry. Parameter courseid is required.
    $courseid = required_param('courseid', PARAM_INT);
    $entry = (object)['courseid' => $courseid];
    $urlparams = ['courseid' => $courseid];
    $title = get_string('editentry', 'tool_devcourse');
}

$url = new moodle_url('/admin/tool/devcourse/edit.php', $urlparams);
$PAGE->set_url($url);

require_login($courseid);
$context = context_course::instance($courseid);
require_capability('tool/devcourse:edit', $context);

$PAGE->set_title($title);
$PAGE->set_heading(get_string('pluginname', 'tool_devcourse'));

$form = new tool_devcourse_form();
$form->set_data($entry);

$returnurl = new moodle_url('/admin/tool/devcourse/index.php', ['id' => $courseid]);
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    if ($data->id) {
        // Edit entry. Never modify courseid.
        $DB->update_record('tool_devcourse', [
            'id' => $data->id,
            'name' => $data->name,
            'completed' => $data->completed,
            'timemodified' => time()
        ]);
        // TODO there should be a function in another file that updates an entry.
    } else {
        // Add entry.
        $DB->insert_record('tool_devcourse', [
            'courseid' => $data->courseid,
            'name' => $data->name,
            'completed' => $data->completed,
            'priority' => 0,
            'timecreated' => time(),
            'timemodified' => time()
        ]);
        // TODO there should be a function in another file that creates an entry.
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$form->display();

echo $OUTPUT->footer();