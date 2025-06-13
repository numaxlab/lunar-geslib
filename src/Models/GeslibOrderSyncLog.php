<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Lunar\Base\BaseModel;
use Lunar\Models\Order;

class GeslibOrderSyncLog extends BaseModel
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SYNCED = 'synced';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'status',
        'log',
    ];

    public function order(): void
    {
        $this->belongsTo(Order::modelClass());
    }
}
