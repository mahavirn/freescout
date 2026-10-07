<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendMonitor extends Command
{
    const CHECK_PERIOD = 12 * 3600; // 12 hours

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:send-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if queue:work is processing emails queue and show an alert in the web interface if needed';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Works with any queue driver.
        $oldest_job_time = \Queue::creationTimeOfOldestPendingJob('emails');
        $pending_jobs = $oldest_job_time && $oldest_job_time < time() - self::CHECK_PERIOD;

        if ($pending_jobs) {
            \Option::set('send_emails_problem', '1');
            $this->error('['.date('Y-m-d H:i:s').'] There are problems with emails queue processing');
        } else {
            \Option::remove('send_emails_problem');
            $this->info('['.date('Y-m-d H:i:s').'] Emails queue processing is working');
        }
    }
}
