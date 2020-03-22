<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
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
     * A portfolio contains securities.
     *
     * @var array
     */
    public function securities()
    {
        return $this->belongsToMany('App\Models\Security')->orderBy('ticker');
    }

    /**
     * A portfolio can belong to users.
     *
     * @var array
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
