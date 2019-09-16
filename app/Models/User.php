<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasRoleAndPermission;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'password',
        'token',
        'activated',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * A user has simulations.
     *
     * @var array
     */
    public function simulations()
    {
        return $this->hasMany('App\Models\Simulation')->orderBy('name');
    }

    /**
     * A user has portfolios.
     *
     * @var array
     */
    public function portfolios()
    {
        return $this->belongsToMany('App\Models\Portfolio')->orderBy('name');
    }

}
