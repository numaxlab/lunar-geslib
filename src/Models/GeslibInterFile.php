<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Lunar\Base\BaseModel;

class GeslibInterFile extends BaseModel
{
    protected $fillable = [
        'name',
        'received_at',
        'processing',
        'processed',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];
}
