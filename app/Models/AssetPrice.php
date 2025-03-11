<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetPrice extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'asset_prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'symbol',
        'current_price',
        'bid',
        'ask',
        'mts',
        'time_interval',
        'percent_difference'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'current_price' => 'decimal:8',
        'bid' => 'decimal:8',
        'ask' => 'decimal:8',
        'mts' => 'timestamp'
    ];
}
