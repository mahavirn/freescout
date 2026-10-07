<?php

use App\Misc\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

$app = Application::configure(basePath: dirname(__DIR__))
    // Listeners are registered explicitly in App\Providers\EventServiceProvider.
    ->withEvents(discover: false)
    ->withRouting(using: function () {
        Route::pattern('id', '[0-9]+');

        $subdirectory = \Helper::getSubdirectory();

        foreach (['web', 'open'] as $group) {
            Route::prefix($subdirectory ?: '')
                ->middleware($group)
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/'.$group.'.php'));
        }
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\ResponseHeaders::class,
            \App\Http\Middleware\TerminateHandler::class,
        ]);

        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\TokenAuth::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HttpsRedirect::class,
            \App\Http\Middleware\CheckBrowser::class,
            \App\Http\Middleware\Localize::class,
            \App\Http\Middleware\LogoutIfDeleted::class,
            \App\Http\Middleware\FrameGuard::class,
            \App\Http\Middleware\CustomHandle::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);

        $middleware->group('open', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HttpsRedirect::class,
            \App\Http\Middleware\FrameGuard::class,
            \App\Http\Middleware\CustomHandle::class,
        ]);

        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            // Used by modules routes.
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'guest'    => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'roles'    => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withSchedule(new \App\Console\Scheduler())
    ->withExceptions()
    ->create();

$app->bind(
    Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    App\Bootstrap\HandleExceptions::class
);

$app->bind(
    Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    App\Bootstrap\LoadEnvironmentVariables::class
);

return $app;
