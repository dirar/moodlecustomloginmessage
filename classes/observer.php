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
 * Event observer for local_moodlecustomloginmessage.
 *
 * @package    local_moodlecustomloginmessage
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlecustomloginmessage;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class.
 */
class observer {
    /**
     * Show a custom message when a suspended user's login attempt is rejected.
     *
     * Moodle rejects suspended accounts in authenticate_user_login() before the
     * password is even checked, so this must react to the login failure event
     * rather than any "successful authentication" hook.
     *
     * @param \core\event\user_login_failed $event
     * @return void
     */
    public static function user_login_failed(\core\event\user_login_failed $event): void {
        global $DB, $SESSION;

        $reason = $event->other['reason'] ?? null;
        error_log('[moodlecustomloginmessage] observer fired, reason=' . var_export($reason, true));

        if ((int) $reason !== AUTH_LOGIN_SUSPENDED) {
            return;
        }

        try {
            if (!empty($event->userid)) {
                // Kill any existing sessions for the suspended user.
                $DB->delete_records('sessions', ['userid' => $event->userid]);
            }

            $message = get_config('local_moodlecustomloginmessage', 'suspensionmessage');
            if (empty($message)) {
                $message = get_string('accountsuspended', 'local_moodlecustomloginmessage');
            }

            // Ensure multilang HTML content is processed safely by Moodle formatting APIs.
            $SESSION->loginerrormsg = format_text($message, FORMAT_HTML);

            error_log('[moodlecustomloginmessage] about to redirect with custom message set');
        } catch (\Throwable $e) {
            error_log('[moodlecustomloginmessage] exception before redirect: ' . $e->getMessage());
            throw $e;
        }

        redirect(new \moodle_url('/login/index.php'));
    }
}
