<?php
/**
 * Application with FreeScout specific behaviour
 * (ported from overrides/laravel/framework/src/Illuminate/Foundation/ProviderRepository.php).
 */

namespace App\Misc;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application as BaseApplication;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Collection;

class Application extends BaseApplication
{
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        $this->clearCacheIfFrameworkChanged();
    }

    /**
     * Cached config, packages and services from another Laravel version
     * (for example, left after updating from Laravel 5.5) break the app:
     * new packages are not registered and old config format is used.
     * Clear them once when the framework version changes.
     */
    protected function clearCacheIfFrameworkChanged()
    {
        $marker = $this->bootstrapPath('cache/framework_version');

        if (@file_get_contents($marker) === static::VERSION) {
            return;
        }

        foreach (glob($this->bootstrapPath('cache/*.php')) ?: [] as $file) {
            @unlink($file);
        }

        @file_put_contents($marker, static::VERSION);
    }

    /**
     * Skip service providers which do not exist.
     *
     * Config and packages are cached (bootstrap/cache). If a service provider
     * is removed during the app update, cached files still list it and
     * users receive "Class '...' not found" error until the cache is cleared.
     */
    public function registerConfiguredProviders()
    {
        $providers = (new Collection($this->make('config')->get('app.providers')))
            ->partition(fn ($provider) => str_starts_with($provider, 'Illuminate\\'));

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        $providers = $providers->collapse()->filter(function ($provider) {
            if (class_exists($provider)) {
                return true;
            }
            // Just log the error. After cache will be cleared, problem will go away.
            error_log('[FreeScout] Service provider not found, cache needs to be cleared: '.$provider);

            return false;
        });

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($providers->values()->toArray());

        $this->fireAppCallbacks($this->registeredCallbacks);
    }
}
