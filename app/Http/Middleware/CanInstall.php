<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Web installer is available only until the app is installed.
 */
class CanInstall
{
    public function handle($request, Closure $next)
    {
        if ($this->isInstalled()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }

    protected function isInstalled()
    {
        if (file_exists(storage_path('.installed'))) {
            return true;
        }

        // If config is cached env() always returns empty, so config() is used.
        if (!config('app.url') || !config('app.key') || !config('database.default')
            || !config('database.connections.mysql.host') || !config('database.connections.mysql.port')
            || !config('database.connections.mysql.database') || !config('database.connections.mysql.username')
            || !config('database.connections.mysql.password')
        ) {
            return false;
        }

        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            return false;
        }

        // Allow to access the last installation pages.
        return !\Helper::isRoute(['installer.database', 'installer.final']);
    }
}
