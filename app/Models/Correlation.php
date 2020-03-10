<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correlation extends Model
{

    protected $table = 'securities_correlation';

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
        'compared_security_id',
        'correlation'
    ];

    /**
     * A Correlation has a security
     * 
     * @var array
     */
    public function security()
    {
        return $this->belongsTo('App\Models\Security');
    }

    /**
     * A Correlation has a compared security
     * 
     * @var array
     */
    public function compared_security()
    {
        return $this->belongsTo('App\Models\Security', 'compared_security_id');
    }

}