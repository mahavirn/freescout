<?php

namespace App\Misc\Modules;

use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

/**
 * Modules statuses are stored in the "modules" DB table by module alias.
 */
class DbActivator implements ActivatorInterface
{
    public function enable(Module $module): void
    {
        $this->setActive($module, true);
    }

    public function disable(Module $module): void
    {
        $this->setActive($module, false);
    }

    public function hasStatus(Module|string $module, bool $status): bool
    {
        if (is_string($module)) {
            $module = app('modules')->find($module);
        }

        return $module && (bool)\App\Module::isActive($module->get('alias')) === $status;
    }

    public function setActive(Module $module, bool $active): void
    {
        \App\Module::setActive($module->get('alias'), $active);
    }

    public function setActiveByName(string $name, bool $active): void
    {
        $this->setActive(app('modules')->findOrFail($name), $active);
    }

    public function delete(Module $module): void
    {
        $this->setActive($module, false);
    }

    public function reset(): void
    {
        \App\Module::query()->update(['active' => false]);
        \App\Module::clearModulesCache();
    }
}
