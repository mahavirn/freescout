<?php

namespace App\Misc\Modules;

use Nwidart\Modules\Laravel\Module as BaseModule;

/**
 * FreeScout module (Modules/{Name}/module.json).
 */
class Module extends BaseModule
{
    /**
     * Access module.json values as properties: $module->version.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    public function getAlias()
    {
        return $this->get('alias');
    }

    public function active()
    {
        return $this->isEnabled();
    }

    public function isOfficial()
    {
        return \App\Module::isOfficial($this->get('authorUrl'));
    }

    public function getLicense()
    {
        return \App\Module::getLicense($this->getAlias());
    }

    /**
     * Module with an error can be deactivated via "modules.register_error" filter
     * instead of breaking the whole app.
     */
    public function register(): void
    {
        try {
            parent::register();
        } catch (\Throwable $e) {
            $e = \Eventy::filter('modules.register_error', $e, $this);
            if ($e) {
                throw $e;
            }
        }
    }
}
