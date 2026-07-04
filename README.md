# Moodle Custom Login Message (local_moodlecustomloginmessage)

This local plugin overrides default login feedback for suspended users.

## Purpose

Moodle rejects suspended accounts in `authenticate_user_login()` before the
password is even checked, so the custom message is shown for any login
attempt against a suspended account, regardless of the password entered.
The plugin observes the `\core\event\user_login_failed` event and, when the
failure reason is `AUTH_LOGIN_SUSPENDED`:
- kills active sessions for that user,
- adds the custom message via `\core\notification::error()`,
- redirects back to `/login/index.php`.

The message is shown through Moodle's session notification system rather
than the login form's own error field, because the login form template
HTML-escapes its error text (it's only ever meant to hold plain text), while
notifications are rendered as raw HTML - required for multilang markup like
`<span lang="ar" class="multilang">...</span>` to actually render instead of
showing as literal tags.

## Compatibility

- Moodle 4.x (tested metadata for 4.3+)
- Plugin type: local
- No Moodle core file changes

## Installation

1. Place the plugin in `local/moodlecustomloginmessage`.
2. Visit Site administration → Notifications.
3. Complete upgrade.

## Configure multilingual message

1. Go to Site administration → Plugins → Local plugins → Moodle Custom Login Message.
2. Set **Suspended account message** using multilingual HTML, for example:

```html
<span lang="en" class="multilang">Your account has been suspended.</span>
<span lang="ar" class="multilang">تم إيقاف حسابك.</span>
```

3. Enable filter:
   Site administration → Plugins → Filters → Manage filters → Multi-language content.

## Security notes

- Uses Moodle APIs only.
- Includes `defined('MOODLE_INTERNAL') || die();` checks.
- Uses `format_text($message, FORMAT_HTML)` before passing content to
  `\core\notification::error()` for rendering.
- Invalidates sessions for suspended users.

## Testing steps

1. Create a test user and set it to suspended.
2. Attempt login with either the correct or an incorrect password.
3. Confirm redirect to `/login/index.php`.
4. Confirm the configured message displays as a rendered (not escaped) HTML
   notification banner, instead of the generic "Invalid login" message.
5. Confirm user sessions are removed from active sessions.

## Troubleshooting

- Message not translated: enable Multi-language content filter.
- Default message shown: ensure plugin setting is non-empty.
- Message shows with literal HTML tags instead of being rendered: make sure
  the observer uses `\core\notification::error()` rather than
  `$SESSION->loginerrormsg` (the login form template escapes that field).
- Generic "Invalid login" message still shown instead of the custom one:
  purge caches (Site administration → Development → Purge caches) so Moodle
  picks up the event observer registered in `db/events.php`, and confirm the
  plugin installed as `local_moodlecustomloginmessage`.

## Screenshot descriptions

- Admin settings page showing multilingual HTML in the suspended account message editor.
- Login page after suspended-user attempt showing the custom message.
- Filter management page with Multi-language content enabled.
