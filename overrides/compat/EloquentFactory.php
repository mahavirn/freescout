<?php
/**
 * Laravel 5.5 model factory, removed in Laravel 8.
 *
 * Module service providers generated before Laravel 13 call
 * app(\Illuminate\Database\Eloquent\Factory::class)->load($path) in non-production environments.
 * Legacy factory definitions are not loaded: modules should use class based factories
 * (Illuminate\Database\Eloquent\Factories\Factory) and remove registerFactories().
 */

namespace Illuminate\Database\Eloquent;

class Factory
{
    public function load($path)
    {
        @trigger_error('Legacy model factories are not supported, remove registerFactories() from the module service provider: '.$path, E_USER_DEPRECATED);

        return $this;
    }
}
