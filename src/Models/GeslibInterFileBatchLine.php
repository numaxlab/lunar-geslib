<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;

class GeslibInterFileBatchLine extends BaseModel
{
    protected $fillable = [
        'geslib_inter_file_id',
        'line_type',
        'article_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function geslibInterFile(): BelongsTo
    {
        return $this->belongsTo(GeslibInterFile::class);
    }
}
