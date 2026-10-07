<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    use \App\Misc\SerializesDates;
    use \App\Misc\QueuedJobPayload;

    protected $casts = [
        'failed_at' => 'datetime',
    ];

    public static function retry($job_id)
    {
        \Artisan::call('queue:retry', ['id' => $job_id]);
    }
}
