# Moodle Custom Login Message (local_moodlecustomloginmessage)

This local plugin overrides default login feedback for suspended users.

## Purpose

When credentials are correct but the account is suspended, the plugin:
- kills active sessions for that user,
- stores a custom message in `$SESSION->loginerrormsg`,
- redirects back to `/login/index.php`.

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
- Uses `format_text($message, FORMAT_HTML)` before storing/rendering content.
- Invalidates sessions for suspended users.

## Testing steps

1. Create a test user and set it to suspended.
2. Attempt login with valid credentials.
3. Confirm redirect to `/login/index.php`.
4. Confirm `$SESSION->loginerrormsg` displays the configured message.
5. Confirm user sessions are removed from active sessions.

## Troubleshooting

- Message not translated: enable Multi-language content filter.
- Default message shown: ensure plugin setting is non-empty.
- Behavior unchanged: purge caches and confirm plugin installed as `local_moodlecustomloginmessage`.

## Screenshot descriptions

- Admin settings page showing multilingual HTML in the suspended account message editor.
- Login page after suspended-user attempt showing the custom message.
- Filter management page with Multi-language content enabled.
