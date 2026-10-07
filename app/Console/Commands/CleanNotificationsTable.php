<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanNotificationsTable extends Command
{
    const PERIOD = '-6 months';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:clean-notifications-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old read records from notifications table.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \DB::table('notifications')->where('created_at', '<', \Carbon\Carbon::now()->modify(self::PERIOD))
            ->whereNotNull('read_at')
            ->delete();

        $this->info('['.date('Y-m-d H:i:s').'] Deleted old read notifications for: '.self::PERIOD);
    }
}
