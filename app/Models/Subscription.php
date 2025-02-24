<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'email',
        'target_price',
        'percent_change',
        'time_interval',
        'is_percent_change_notified',
        'is_time_interval_notified',
        'percent_change_notified_on',
        'target_price_notified_on',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'target_price' => 'decimal:8',
        'is_percent_change_notified' => 'boolean',
        'is_time_interval_notified' => 'boolean',
        'percent_change_notified_on' => 'datetime',
        'target_price_notified_on' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
