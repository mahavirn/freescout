<?php

namespace App\Misc\Modules;

use Illuminate\Container\Container;
use Nwidart\Modules\Laravel\LaravelFileRepository;

/**
 * Modules repository (\Module facade). Modules are found by alias in FreeScout.
 */
class Repository extends LaravelFileRepository
{
    protected $active = [];

    /**
     * Scanned modules are stored in a static variable, but they are bound to the app instance.
     * A new app instance (for example, created by "config:cache") must scan modules again.
     */
    public function __construct(Container $app, ?string $path = null)
    {
        parent::__construct($app, $path);

        $this->resetModules();
    }

    protected function createModule(Container $app, string $name, string $path): Module
    {
        return new Module($app, $name, $path);
    }

    public function findByAlias($alias)
    {
        $alias = strtolower($alias);
        foreach ($this->all() as $module) {
            if (strtolower($module->getAlias()) === $alias) {
                return $module;
            }
        }

        return null;
    }

    public function isActive($alias)
    {
        if (!isset($this->active[$alias])) {
            $module = $this->findByAlias($alias);
            $this->active[$alias] = $module && $module->isEnabled();
        }

        return $this->active[$alias];
    }

    public function getActive()
    {
        return $this->allEnabled();
    }

    public function getModulePathByAlias($alias)
    {
        $module = $this->findByAlias($alias);

        return $module ? $module->getPath().'/' : '';
    }

    public function getPublicPath($alias)
    {
        return '/modules/'.$alias;
    }

    public function getOption($alias, $option_name, $default = false)
    {
        // If not passed, get default value from module config.
        if (func_num_args() == 2) {
            $options = config(strtolower($alias).'.options');
            if (isset($options[$option_name]['default'])) {
                $default = $options[$option_name]['default'];
            }
        }

        return \Option::get($alias.'.'.$option_name, $default);
    }

    public function setOption($alias, $option_name, $option_value)
    {
        return \Option::set(strtolower($alias).'.'.$option_name, $option_value);
    }

    /**
     * Rescan modules and reload their statuses.
     */
    public function clearCache()
    {
        $this->resetModules();
        $this->active = [];
        \App\Module::clearModulesCache();
    }
}
