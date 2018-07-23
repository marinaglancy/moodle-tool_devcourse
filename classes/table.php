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
 * Class tool_devcourse_table
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * Class tool_devcourse_table for displaying tool_devcourse table
 *
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_devcourse_table extends table_sql {

    /** @var context_course */
    protected $context;

    /**
     * Sets up the table_log parameters.
     *
     * @param string $uniqueid unique id of form.
     * @param int $courseid
     */
    public function __construct($uniqueid, $courseid) {
        global $PAGE;

        parent::__construct($uniqueid);

        $columns = array('name', 'completed', 'priority', 'timecreated', 'timemodified');
        $headers = array(
            get_string('name', 'tool_devcourse'),
            get_string('completed', 'tool_devcourse'),
            get_string('priority', 'tool_devcourse'),
            get_string('timecreated', 'tool_devcourse'),
            get_string('timemodified', 'tool_devcourse'),
        );
        $this->context = context_course::instance($courseid);
        if (has_capability('tool/devcourse:edit', $this->context)) {
            $columns[] = 'edit';
            $headers[] = '';
        }
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->pageable(true);
        $this->collapsible(false);
        $this->sortable(false);
        $this->is_downloadable(false);

        $this->define_baseurl($PAGE->url);

        $this->set_sql('id, name, completed, priority, timecreated, timemodified',
            '{tool_devcourse}', 'courseid = ?', [$courseid]);
    }

    /**
     * Displays column completed
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_completed($row) {
        return $row->completed ? get_string('yes') : get_string('no');
    }

    /**
     * Displays column priority
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_priority($row) {
        return $row->priority ? get_string('yes') : get_string('no');
    }

    /**
     * Displays column name
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_name($row) {
        return format_string($row->name, true,
            ['context' => $this->context]);
    }

    /**
     * Displays column timecreated
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_timecreated($row) {
        return userdate($row->timecreated, get_string('strftimedatetime'));
    }

    /**
     * Displays column timemodified
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_timemodified($row) {
        return userdate($row->timemodified, get_string('strftimedatetime'));
    }

    protected function col_edit($row) {
        $url = new moodle_url('/admin/tool/devcourse/edit.php', ['id' => $row->id]);
        $deleteurl = new moodle_url('/admin/tool/devcourse/index.php',
            ['delete' => $row->id, 'id' => $this->context->instanceid,
                'sesskey' => sesskey()]);
        return html_writer::link($url, get_string('edit')) . '<br>' .
            html_writer::link($deleteurl, get_string('delete'));
    }
}