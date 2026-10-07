<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AfterAppUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:after-app-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run commands after application has been updated';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('freescout:clear-cache');
        $this->call('migrate', ['--force' => true]);
        $this->call('queue:restart');

        \Eventy::action('command.after_app_update');
    }
}
