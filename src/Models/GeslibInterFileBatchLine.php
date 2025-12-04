<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use NumaxLab\Lunar\Geslib\Database\Factories\GeslibInterFileBatchLineFactory;

class GeslibInterFileBatchLine extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'geslib_inter_file_id',
        'line_type',
        'article_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function newFactory()
    {
        return GeslibInterFileBatchLineFactory::new();
    }

    public function geslibInterFile(): BelongsTo
    {
        return $this->belongsTo(GeslibInterFile::class);
    }
}
