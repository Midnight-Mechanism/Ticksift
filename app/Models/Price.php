<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use \App\Models\SourceTable;

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
        'close_adj',
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

    /**
     * A function to retrieve the results for a given source table
     */
    public static function sourceTableFilter($source_table_name) {
        $source_table = SourceTable::where('name', $source_table_name)->first();
        return Price::whereHas('security', function(Builder $query) use ($source_table) {
            $query->where('source_table_id', $source_table->id);
        });
    }
}
