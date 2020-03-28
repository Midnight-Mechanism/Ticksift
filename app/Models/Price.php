<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'security_id',
        'date',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'dividends',
        'close_unadj',
        'source_last_updated',
    ];

    /**
     * A price has a security.
     */
    public function security()
    {
        return $this->belongsTo('App\Models\Security');
    }

}
