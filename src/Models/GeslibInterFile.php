<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;

class GeslibInterFile extends BaseModel
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_FAILED = 'failed';

    public const STATUS_WARNING = 'warning';

    public const STATUS_SUCCESS = 'success';

    protected $fillable = [
        'name',
        'status',
        'received_at',
        'started_at',
        'finished_at',
        'total_lines',
        'processed_lines',
        'log',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'log' => 'array',
    ];

    public function batchLines(): HasMany
    {
        return $this->hasMany(GeslibInterFileBatchLine::class);
    }

    public function getProgressAttribute(): string
    {
        return $this->processed_lines.' / '.$this->total_lines;
    }
}
