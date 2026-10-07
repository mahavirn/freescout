<?php

namespace App\Providers;

use App\Misc\Modules\Repository;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Nwidart\Modules\Providers\ConsoleServiceProvider;

class ModulesServiceProvider extends LaravelModulesServiceProvider
{
    public function boot()
    {
        parent::boot();

        // Modules are registered on boot (not on register), so that app providers
        // are registered already (for example "modules.register_error" filter).
        $modules = $this->app['modules'];
        $modules->register();
        $this->app->booted(fn () => $modules->boot());
    }

    protected function registerServices()
    {
        parent::registerServices();

        $this->app->singleton(RepositoryInterface::class, function ($app) {
            return new Repository($app, $app['config']->get('modules.paths.modules'));
        });
    }

    /**
     * ContractsServiceProvider is not registered, as it binds the default modules repository.
     */
    protected function registerProviders()
    {
        $this->app->register(ConsoleServiceProvider::class);
    }

    /**
     * Modules are registered in boot().
     */
    protected function registerModules()
    {
    }
}
