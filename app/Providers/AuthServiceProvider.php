<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\User'         => 'App\Policies\UserPolicy',
        'App\Mailbox'      => 'App\Policies\MailboxPolicy',
        'App\Folder'       => 'App\Policies\FolderPolicy',
        'App\Conversation' => 'App\Policies\ConversationPolicy',
        'App\Thread'       => 'App\Policies\ThreadPolicy',
        'App\Customer'     => 'App\Policies\CustomerPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Same as AuthManager::createSessionDriver() but with FreeScout session guard.
        \Auth::extend('session', function ($app, $name, $config) {
            $guard = new \App\Misc\SessionGuard(
                $name,
                \Auth::createUserProvider($config['provider'] ?? null),
                $app['session.store'],
                rehashOnLogin: $app['config']->get('hashing.rehash_on_login', true),
                timeboxDuration: $app['config']->get('auth.timebox_duration', 200000),
                hashKey: $app['config']->get('app.key'),
            );
            $guard->setCookieJar($app['cookie']);
            $guard->setDispatcher($app['events']);
            $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            if (isset($config['remember'])) {
                $guard->setRememberDuration($config['remember']);
            }

            return $guard;
        });
    }
}
