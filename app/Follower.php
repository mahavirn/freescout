<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use \App\Misc\SerializesDates;

    public $timestamps = false;
}
