<?php

namespace App\Console\Commands;

use Nwidart\Modules\Commands\Database\MigrateCommand;

/**
 * Runs migrations from the module folder, as not all modules register
 * their migrations via loadMigrationsFrom(). Real path is passed,
 * so it works when Modules folder is a symlink leading outside of the app.
 */
class ModuleMigrate extends MigrateCommand
{
    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $path = $module->getExtraPath(config('modules.paths.generator.migration.path'));
        if ($this->option('subpath')) {
            $path .= '/'.$this->option('subpath');
        }

        $this->call('migrate', [
            '--path'     => $path,
            '--realpath' => true,
            '--database' => $this->option('database'),
            '--pretend'  => $this->option('pretend'),
            '--force'    => $this->option('force'),
        ]);

        if ($this->option('seed')) {
            $this->call('module:seed', ['module' => $module->getName(), '--force' => $this->option('force')]);
        }
    }
}
