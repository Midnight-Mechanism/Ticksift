<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Security extends Model
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
        'source_table_id',
        'source_id',
        'ticker',
        'name',
        'exchange_id',
        'is_delisted',
        'category_id',
        'sic_industry_id',
        'industry_id',
        'scale_marketcap',
        'scale_revenue',
        'currency_id',
        'location',
        'source_first_added',
        'source_last_updated',
        'first_quarter',
        'last_quarter',
        'sec_filing_url',
        'company_url',
    ];

    /**
     * A security has a currency.
     */
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    /**
     * A security has prices.
     */
    public function prices()
    {
        return $this->hasMany('App\Models\Price')->orderBy('date');
    }

    /**
     * A security has actions.
     *
     * @var array
     */
    public function actions()
    {
        return $this->belongsToMany('App\Models\Action')->withPivot('date', 'value');
    }

    /**
     * A security belongs to portfolios.
     *
     * @var array
     */
    public function portfolios()
    {
        return $this->belongsToMany('App\Models\Portfolio');
    }

    /**
     * A security has many correlations.
     * 
     * @var array
     */
    public function correlations()
    {
        return $this->hasMany('App\Model\Correlation', 'security_id', 'id');
    }

}
