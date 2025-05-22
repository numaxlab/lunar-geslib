<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Lunar\Base\BaseModel;

class GeslibInterFile extends BaseModel
{
    protected $fillable = [
        'name',
        'received_at',
        'started_at',
        'finished_at',
        'log',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
