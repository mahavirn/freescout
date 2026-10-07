<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use \App\Misc\SerializesDates;
    use \App\Misc\QueuedJobPayload;

    const UPDATED_AT = null;

    protected $casts = [
        'created_at'   => 'datetime',
        'available_at' => 'datetime',
        'reserved_at'  => 'datetime',
    ];
}
