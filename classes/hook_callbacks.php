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
 * Hook callbacks for local_moodlecustomloginmessage.
 *
 * @package    local_moodlecustomloginmessage
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlecustomloginmessage;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook callbacks class.
 */
class hook_callbacks {
    /**
     * On the login page only, inject a small script that renders our marked
     * suspension message as HTML while leaving every other login error/info
     * message (which may contain unsanitised user input) escaped as-is.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     * @return void
     */
    public static function before_standard_top_of_body_html(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        if ($PAGE->pagelayout !== 'login') {
            return;
        }

        $marker = json_encode(observer::HTML_MARKER);
        $js = <<<HTML
<script>
(function() {
    var marker = {$marker};
    var unescapeIfMarked = function(el) {
        if (!el) {
            return;
        }
        var text = el.textContent || '';
        if (text.indexOf(marker) === 0) {
            el.innerHTML = text.slice(marker.length);
        }
    };
    document.addEventListener('DOMContentLoaded', function() {
        unescapeIfMarked(document.getElementById('loginerrormessage'));
        document.querySelectorAll('.alert.alert-danger').forEach(unescapeIfMarked);
    });
})();
</script>
HTML;

        $hook->add_html($js);
    }
}
