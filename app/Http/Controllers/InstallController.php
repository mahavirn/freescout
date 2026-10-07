<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Web installer: requirements, permissions, .env and database setup, admin user creation.
 */
class InstallController extends Controller
{
    public function welcome()
    {
        return view('installer.welcome');
    }

    public function requirements()
    {
        $extensions = [];
        foreach (config('installer.requirements.php') as $extension) {
            $extensions[$extension] = extension_loaded($extension);
        }
        $min_php_version = config('installer.core.minPhpVersion');
        $php_supported = version_compare(PHP_VERSION, $min_php_version, '>=')
            && version_compare(PHP_VERSION, config('installer.core.maxPhpVersion'), '<=');

        return view('installer.requirements', [
            'extensions'      => $extensions,
            'min_php_version' => $min_php_version,
            'php_supported'   => $php_supported,
            'can_continue'    => $php_supported && !in_array(false, $extensions, true),
        ]);
    }

    public function permissions()
    {
        $permissions = [];
        foreach (array_keys(config('installer.permissions')) as $folder) {
            $path = base_path($folder);
            if (!file_exists($path)) {
                \File::makeDirectory($path, 0775, true, true);
            }
            $permissions[$folder] = \Helper::isFolderWritable($path);
        }

        return view('installer.permissions', [
            'permissions'  => $permissions,
            'can_continue' => !in_array(false, $permissions, true),
        ]);
    }

    public function environment()
    {
        return view('installer.environment');
    }

    public function saveEnvironment(Request $request)
    {
        if ($request->app_force_https == 'true') {
            $request->merge(['app_url' => preg_replace('/^http:/i', 'https:', $request->app_url)]);
        }

        // Entered values are shown in the form if there are errors
        // and admin data is used to create the admin user on the last step.
        foreach ($request->all() as $field => $value) {
            session(['_old_input.'.$field => $value]);
        }

        $validator = \Validator::make($request->all(), config('installer.environment.form.rules'));
        if ($validator->fails()) {
            return view('installer.environment', ['errors' => $validator->errors()]);
        }

        try {
            try {
                $this->testDbConnect($request);
            } catch (\Exception $e) {
                // Change utf8mb4 to utf8 if needed.
                if ($request->database_connection == 'mysql' && strstr($e->getMessage(), 'Unknown character set')) {
                    $this->testDbConnect($request, ['charset' => 'utf8', 'collation' => 'utf8_unicode_ci']);

                    $request->database_charset = 'utf8';
                    $request->database_collation = 'utf8_unicode_ci';
                } else {
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            $errors = $validator->errors();
            $errors->add('general', 'Could not establish database connection: '.$e->getMessage());
            $errors->add('database_hostname', 'Database Host: Please check entered value.');
            $errors->add('database_port', 'Database Port: Please check entered value.');
            $errors->add('database_name', 'Database Name: Please check entered value.');
            $errors->add('database_username', 'Database User Name: Please check entered value.');
            $errors->add('database_password', 'Database Password: Please check entered value.');

            return view('installer.environment', ['errors' => $errors]);
        }

        try {
            $this->saveEnvFile($request);
        } catch (\Exception $e) {
            $errors = $validator->errors();
            $errors->add('general', 'Could not save .env file: '.$e->getMessage());

            return view('installer.environment', ['errors' => $errors]);
        }

        return redirect()->route('installer.database');
    }

    public function database()
    {
        $output = new BufferedOutput();

        try {
            \Artisan::call('migrate', ['--force' => true], $output);
            $message = ['status' => 'success', 'message' => __('installer_messages.final.finished')];
        } catch (\Exception $e) {
            $message = ['status' => 'error', 'message' => $e->getMessage()];
        }
        $message['dbOutputLog'] = $output->fetch();

        return redirect()->route('installer.final')->with(['message' => $message]);
    }

    public function finish()
    {
        // Read before BufferedOutput, as after it session values are not available.
        $db_message = session('message') ?: [];
        $admin_data = [
            'email'      => old('admin_email'),
            'password'   => old('admin_password'),
            'role'       => User::ROLE_ADMIN,
            'first_name' => old('admin_first_name'),
            'last_name'  => old('admin_last_name'),
        ];

        $output = new BufferedOutput();
        try {
            \Artisan::call('freescout:clear-cache', [], $output);
            \Artisan::call('storage:link', [], $output);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        if (!User::where('role', User::ROLE_ADMIN)->exists()) {
            User::create($admin_data);
        }

        $installed_file = storage_path('.installed');
        $installed_message = __('installer_messages.installed.success_log_message').date('Y/m/d h:i:sa');
        file_put_contents($installed_file, $installed_message.PHP_EOL, FILE_APPEND | LOCK_EX);

        // Now clear cache and cache config.
        \Artisan::call('freescout:clear-cache');

        return view('installer.finished', [
            'finalMessages'      => $output->fetch(),
            'finalStatusMessage' => $installed_message,
            'dbMessage'          => $db_message,
        ]);
    }

    protected function testDbConnect($request, $params = [])
    {
        $driver = $request->database_connection ?? 'mysql';

        $params = array_merge(config('database.connections.'.$driver) ?: [], $params, [
            'driver'   => $driver,
            'host'     => $request->database_hostname,
            'port'     => $request->database_port,
            'database' => $request->database_name,
            'username' => $request->database_username,
            'password' => $request->database_password,
        ]);

        $connection = 'install'.md5(json_encode($params));
        \Config::set('database.connections.'.$connection, $params);
        \DB::connection($connection)->getPdo();
    }

    protected function saveEnvFile($request)
    {
        file_put_contents(base_path('.env'),
            '# Every time you are making changes in .env file, in order changes to take an effect you need to run:'."\n".
            '# php artisan freescout:clear-cache'."\n\n".
            '# Application URL'."\n".
            'APP_URL='.$request->app_url."\n\n".
            '# Improve security'."\n".
            'SESSION_SECURE_COOKIE='.(\Helper::isHttps($request->app_url) ? 'true' : '')."\n\n".
            '# Timezones: https://github.com/freescout-helpdesk/freescout/wiki/PHP-Timezones'."\n".
            '# Comment it to use default timezone from php.ini'."\n".
            'APP_TIMEZONE='.$request->app_timezone."\n\n".
            '# Default language'."\n".
            'APP_LOCALE='.$request->app_locale."\n\n".
            '# Database settings'."\n".
            'DB_CONNECTION='.$request->database_connection."\n".
            'DB_HOST='.$request->database_hostname."\n".
            'DB_PORT='.$request->database_port."\n".
            'DB_DATABASE='.$request->database_name."\n".
            'DB_USERNAME='.$request->database_username."\n".
            'DB_PASSWORD="'.str_replace('"', '\"\"', $request->database_password)."\"\n".
            (!empty($request->database_charset) ? 'DB_CHARSET='.$request->database_charset."\n" : '').
            (!empty($request->database_collation) ? 'DB_COLLATION='.$request->database_collation."\n" : '').
            "\n".
            '# Run the following console command to generate the key: php artisan key:generate'."\n".
            '# Otherwise application will show the following error: "Whoops, looks like something went wrong"'."\n".
            'APP_KEY='.config('app.key')."\n\n".
            '# Uncomment to see errors in your browser, don\'t forget to comment it back when debugging finished'."\n".
            '#APP_DEBUG=true'."\n"
        );

        // If config is cached here, it caches env data from memory.
        \Artisan::call('freescout:clear-cache', ['--doNotCacheConfig' => true]);
    }
}
