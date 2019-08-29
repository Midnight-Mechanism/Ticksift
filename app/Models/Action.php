<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
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
        'name',
    ];

    /**
     * An action has securities.
     *
     * @var array
     */
    public function securities()
    {
        return $this->belongsToMany('App\Models\Security')->withPivot('date', 'value');
    }
}
