<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\HasUrls;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\Traits\Searchable;
use Lunar\Models\Product;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

class Author extends BaseModel implements Contracts\Author, SpatieHasMedia
{
    use HasAttributes;
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasTranslations;
    use HasUrls;
    use LogsActivity;
    use Searchable;

    protected $table = 'geslib_authors';

    protected $guarded = [];

    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::modelClass(),
            config('lunar.database.table_prefix').'geslib_author_product',
        )->withPivot(['author_type', 'position']);
    }
}
