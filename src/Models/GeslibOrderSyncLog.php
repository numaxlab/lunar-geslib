<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Lunar\Base\BaseModel;

class GeslibOrderSyncLog extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'geslib_endpoint_called',
        'status',
        'message',
        'payload_to_geslib',
        'payload_from_geslib',
    ];
}
