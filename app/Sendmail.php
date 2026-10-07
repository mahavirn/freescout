<?php
/**
 * Outgoing emails.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sendmail extends Model
{
    use \App\Misc\SerializesDates;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Customer.
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * User.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
