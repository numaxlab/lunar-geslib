<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use NumaxLab\Lunar\Geslib\Database\Factories\GeslibInterFileFactory;

class GeslibInterFile extends BaseModel
{
    use HasFactory;

    public const string FILE_TYPE_INITIAL_LOAD = 'initial_load';

    public const string FILE_TYPE_NORMAL_UPDATE = 'normal_update';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_FAILED = 'failed';

    public const STATUS_WARNING = 'warning';

    public const STATUS_SUCCESS = 'success';

    protected $fillable = [
        'name',
        'status',
        'file_type',
        'strategy_metadata',
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
        'strategy_metadata' => 'array',
    ];

    public function usesInitialStrategy(): bool
    {
        return $this->file_type === self::FILE_TYPE_INITIAL_LOAD;
    }

    protected static function newFactory()
    {
        return GeslibInterFileFactory::new();
    }

    public function batchLines(): HasMany
    {
        return $this->hasMany(GeslibInterFileBatchLine::class);
    }

    public function getProgressAttribute(): string
    {
        return $this->processed_lines.' / '.$this->total_lines;
    }
}
