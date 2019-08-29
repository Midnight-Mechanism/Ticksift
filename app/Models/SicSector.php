<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SicSector extends Model
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
        'code',
        'name',
    ];
}
