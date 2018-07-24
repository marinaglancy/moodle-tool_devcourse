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
 * Add confirmation
 *
 * @module     tool_devcourse/confirm
 * @package    tool_devcourse
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/notification'], function($, str, notification) {

    /**
     * Displays the delete confirmation and on approval redirects to href
     * @param {String} href
     */
    var confirmDelete = function(href) {
        str.get_strings([
            {key: 'delete'},
            {key: 'confirmdeleteentry', component: 'tool_devcourse'},
            {key: 'yes'},
            {key: 'no'}
        ]).done(function(s) {
                notification.confirm(s[0], s[1], s[2], s[3], function() {
                    window.location.href = href;
                });
            }
        ).fail(notification.exception);
    };

    /**
     * Registers the handler for click event
     * @param {String} selector
     */
    var registerClickHandler = function(selector) {
        $(selector).on('click', function(e) {
            e.preventDefault();
            var href = $(e.currentTarget).attr('href');
            confirmDelete(href);
        });
    };

    return /** @alias module:tool_devcourse/confirm */ {
        /**
         * Initialise the confirmation for selector
         *
         * @method init
         * @param {String} selector
         */
        init: function(selector) {
            registerClickHandler(selector);
        }
    };
});