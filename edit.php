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
    $entry = tool_devcourse_api::retrieve($id);
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
if (!empty($entry->id)) {
    file_prepare_standard_editor($entry, 'description',
        tool_devcourse_api::editor_options($courseid),
        $PAGE->context, 'tool_devcourse', 'entry', $entry->id);
}
$form->set_data($entry);

$returnurl = new moodle_url('/admin/tool/devcourse/index.php', ['id' => $courseid]);
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    if ($data->id) {
        // Update entry.
        tool_devcourse_api::update($data);
    } else {
        // Add entry.
        tool_devcourse_api::insert($data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$form->display();

echo $OUTPUT->footer();