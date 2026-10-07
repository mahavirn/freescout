<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$guards
     * @return mixed
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        \Eventy::action('auth_middleware.handle', $request, $guards, $next);

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Redirect guests to the login page (default in Laravel 5.5).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return $request->expectsJson() ? null : route('login');
    }
}
