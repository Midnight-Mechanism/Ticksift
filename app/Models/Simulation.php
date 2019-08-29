<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
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
        'user_id',
        'name',
        'description',
        'saved',
    ];

    /**
     * A simulation belongs to a user.
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
