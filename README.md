# Moodle Custom Login Message (local_moodlecustomloginmessage)

This local plugin overrides default login feedback for suspended users.

## Purpose

Moodle rejects suspended accounts in `authenticate_user_login()` before the
password is even checked, so the custom message is shown for any login
attempt against a suspended account, regardless of the password entered.
The plugin observes the `\core\event\user_login_failed` event and, when the
failure reason is `AUTH_LOGIN_SUSPENDED`:
- kills active sessions for that user,
- stores the custom message in `$SESSION->loginerrormsg`, prefixed with a
  private marker (`observer::HTML_MARKER`),
- redirects back to `/login/index.php`.

`$SESSION->loginerrormsg` feeds the login form's error field, which is what
keeps the message positioned right above the username field - but both core
and the active theme's `loginform.mustache` render that field as escaped
plain text (`{{error}}`), since it's normally only ever a plain-text string
(e.g. "Invalid login, please try again"). Rendering multilang HTML there
requires unescaping *only our own message*, without weakening the escaping
for any other login message - some of which embed unsanitised user input
(e.g. a submitted username) and must stay escaped to avoid XSS.

To do that safely, `classes/hook_callbacks.php` hooks
`\core\hook\output\before_standard_top_of_body_html_generation` (registered
in `db/hooks.php`) to inject a small script, active only when
`$PAGE->pagelayout === 'login'`. It looks at the rendered error elements and,
only if their text starts with our marker, replaces their `innerHTML` with
the (unescaped) remainder - re-parsing our own HTML content in place, while
leaving any other message (which won't have the marker) untouched.

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
- Uses `format_text($message, FORMAT_HTML)` (which sanitises the HTML) before
  storing content in `$SESSION->loginerrormsg`.
- The client-side unescape only ever touches text carrying our own private
  marker, so other login messages (including ones built from raw user input,
  like a submitted username) remain safely HTML-escaped.
- Invalidates sessions for suspended users.

## Testing steps

1. Create a test user and set it to suspended.
2. Attempt login with either the correct or an incorrect password.
3. Confirm redirect to `/login/index.php`.
4. Confirm the configured message displays as rendered HTML (not escaped
   tags), positioned right above the username field, instead of the generic
   "Invalid login" message.
5. Confirm user sessions are removed from active sessions.

## Troubleshooting

- Message not translated: enable Multi-language content filter.
- Default message shown: ensure plugin setting is non-empty.
- Message shows with literal HTML tags instead of being rendered: purge
  caches so Moodle picks up `db/hooks.php` (hook registrations are cached
  separately from event observers), and confirm the browser actually loaded
  the injected script (check dev tools console/network for errors).
- Message appears in the wrong position (e.g. floating at the top of the
  page): confirm the observer sets `$SESSION->loginerrormsg` rather than
  using `\core\notification`, which renders as a floating toast instead of
  inline in the form.
- Generic "Invalid login" message still shown instead of the custom one:
  purge caches (Site administration → Development → Purge caches) so Moodle
  picks up the event observer registered in `db/events.php`, and confirm the
  plugin installed as `local_moodlecustomloginmessage`.

## Screenshot descriptions

- Admin settings page showing multilingual HTML in the suspended account message editor.
- Login page after suspended-user attempt showing the custom message.
- Filter management page with Multi-language content enabled.
