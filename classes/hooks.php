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
 * Hook handlers for local_moodlecustomloginmessage.
 *
 * @package    local_moodlecustomloginmessage
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlecustomloginmessage;

use core_auth\hook\user_authenticated;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook class.
 */
class hooks {
    /**
     * Prevent suspended users from logging in after auth succeeds.
     *
     * @param user_authenticated $hook
     * @return void
     */
    public static function after_user_authenticated(user_authenticated $hook): void {
        global $DB, $SESSION;

        $user = $hook->user;
        if (empty($user) || empty($user->id) || empty($user->suspended)) {
            return;
        }

        // Kill existing sessions for the suspended user.
        $DB->delete_records('sessions', ['userid' => $user->id]);

        $message = get_config('local_moodlecustomloginmessage', 'suspensionmessage');
        if (empty($message)) {
            $message = get_string('accountsuspended', 'local_moodlecustomloginmessage');
        }

        // Ensure multilang HTML content is processed safely by Moodle formatting APIs.
        $SESSION->loginerrormsg = format_text($message, FORMAT_HTML);

        redirect(new \moodle_url('/login/index.php'));
    }
}
