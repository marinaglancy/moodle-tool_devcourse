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
 * Callbacks for plugin tool_devcourse
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds this plugin to the course administration menu
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the tool
 * @param context $context The context of the course
 * @return void|null return null if we don't want to display the node.
 */
function tool_devcourse_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('tool/devcourse:view', $context)) {
        $navigation->add(
            get_string('pluginname', 'tool_devcourse'),
            new moodle_url('/admin/tool/devcourse/index.php', ['id' => $course->id]),
            navigation_node::TYPE_SETTING,
            get_string('pluginname', 'tool_devcourse'),
            'devcourse',
            new pix_icon('icon', '', 'tool_devcourse'));
    }
}

/**
 * Serve the embedded files.
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param context $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function tool_devcourse_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    if ($filearea !== 'entry') {
        return false;
    }

    require_login($course);
    require_capability('tool/devcourse:view', $context);

    $itemid = array_shift($args);

    $entry = tool_devcourse_api::retrieve($itemid);

    $filename = array_pop($args);

    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'tool_devcourse', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
