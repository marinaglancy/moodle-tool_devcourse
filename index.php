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
 * Main file
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/admin/tool/devcourse/index.php', ['id' => $courseid]);
$PAGE->set_url($url);

require_login($courseid);
$context = context_course::instance($courseid);
require_capability('tool/devcourse:view', $context);

$PAGE->set_title(get_string('helloworld', 'tool_devcourse'));
$PAGE->set_heading(get_string('pluginname', 'tool_devcourse'));

// Deleting an entry if specified.
if ($deleteid = optional_param('delete', null, PARAM_INT)) {
    require_sesskey();
    $record = $DB->get_record('tool_devcourse',
        ['id' => $deleteid, 'courseid' => $courseid], '*', MUST_EXIST);
    require_capability('tool/devcourse:edit', $PAGE->context);
    $DB->delete_records('tool_devcourse', ['id' => $deleteid]);
    // TODO code to delete entry should be in a separate function.
    redirect(new moodle_url('/admin/tool/devcourse/index.php', ['id' => $courseid]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('helloworld', 'tool_devcourse'));
echo html_writer::div(get_string('youareviewing', 'tool_devcourse', $courseid));
$course = $DB->get_record_sql("SELECT shortname, fullname FROM {course} WHERE id = ?", [$courseid]);
echo html_writer::div(format_string($course->fullname, true, ['context' => $context]));

// Display table.
$table = new tool_devcourse_table('tool_devcourse', $courseid);
$table->out(0, false);

// Link to add new entry.
if (has_capability('tool/devcourse:edit', $context)) {
    echo html_writer::div(html_writer::link(new moodle_url('/admin/tool/devcourse/edit.php', ['courseid' => $courseid]),
        get_string('newentry', 'tool_devcourse')));
}

echo $OUTPUT->footer();