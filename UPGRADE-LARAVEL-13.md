# Upgrading to Laravel 13

This version of FreeScout runs on Laravel 13 and requires **PHP 8.3 or newer**.

## Installation and updates

- The `vendor/` directory is not included in the repository. Install dependencies with:

  ```
  composer install --no-dev --optimize-autoloader
  ```

- The built-in updater is disabled by default (`APP_DISABLE_UPDATING=true`), as it installs official FreeScout releases (Laravel 5.5).

## Updating an existing installation

1. Back up the database, `.env` and `storage/`.
2. Replace the application files and run `composer install --no-dev --optimize-autoloader`.
3. Run:

   ```
   php artisan migrate --force
   php artisan freescout:clear-cache
   ```

Notes:

- Cached files in `bootstrap/cache` left from the previous version are removed automatically on the first run.
- `.env` files written by previous versions keep working. New values are written in standard [phpdotenv](https://github.com/vlucas/phpdotenv) syntax.
- Users are logged out once, as the session cookie format has changed.
- Logging uses `config/logging.php` (`APP_LOG`, `APP_LOG_LEVEL`). Daily log files are named `storage/logs/laravel-YYYY-MM-DD.log`.
- SMTP EHLO uses the `APP_URL` host. Set `MAIL_EHLO_DOMAIN` in `.env` to use another host name.

## Notes for module developers

Modules keep the same structure (`module.json`, `start.php`, service providers, Eventy hooks). Code written for Laravel 5.5 may need these changes:

| Laravel 5.5 | Laravel 13 |
|---|---|
| `array_get()`, `str_slug()` and other `array_*` / `str_*` helpers | `Arr::get()`, `Str::slug()`. The old helpers still work but are deprecated. |
| `str_contains($haystack, [...])` | `Str::contains()`. PHP's native `str_contains()` accepts only a string needle. |
| `Event::fire()` | `Event::dispatch()` |
| `Input::get()` | `request()->input()` |
| `Mail::failures()` | Still available: returns recipients rejected by the SMTP server. |
| Swift Mailer: `withSwiftMessage()`, `Swift_Message` | Symfony Mailer: `withSymfonyMessage()`, `Symfony\Component\Mime\Email` |
| Cache TTL in minutes: `Cache::put($key, $value, 5)` | TTL in seconds: `Cache::put($key, $value, now()->addMinutes(5))` |
| `protected $dates = [...]` | `protected $casts = ['field' => 'datetime']` |
| Blade `{{ $var or 'default' }}` | `{{ $var ?? 'default' }}` |
| Carbon `diffInSeconds()` returned an absolute integer | Carbon 3 returns a signed float: use `diffInSeconds($date, true)` |
| `$factory->define()` model factories | Class based factories (`Illuminate\Database\Eloquent\Factories\Factory`) |
| `$table->index([DB::raw('column(191)')])` | `DB::statement()` with the index SQL |

Modules are loaded by [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules) 13. Module statuses are still stored in the `modules` DB table and the `\Module` facade keeps FreeScout methods: `findByAlias()`, `isActive()`, `getActive()`, `getPublicPath()`, `getModulePath()`, `getModulePathByAlias()`, `getOption()`, `setOption()`. Changes:

- `php artisan module:make` creates the same module structure as before. Other `module:make-*` commands come from laravel-modules 13.
- `php artisan module:migrate` accepts several module names: `php artisan module:migrate "Saved Replies" Tags`.
- Module objects no longer have `getRequires()`, `enabled()`, `disabled()` and `notActive()`. Use `get('requires')`, `isEnabled()` and `isDisabled()`.

Eventy hooks with changed arguments:

- `email.reply_to_customer.swiftmessage` receives `Symfony\Component\Mime\Email` (attachments are added after this hook runs).
- `session_guard.validate_credentials`, `url_generator.app_url`, `auth_middleware.handle` and `modules.register_error` work as before. `modules.register_error` now also receives PHP errors (`\Throwable`).
