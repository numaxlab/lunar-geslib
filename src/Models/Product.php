<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends \Lunar\Models\Product
{
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            Author::class,
            config('lunar.database.table_prefix') . 'geslib_author_product',
        )->withPivot(['author_type', 'position']);
    }

    protected function recordFullTitle(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                $fullTitle = $this->translateAttribute('name');

                if ($this->translateAttribute('subtitle')) {
                    $fullTitle .= ' - ' . $this->translateAttribute('subtitle');
                }

                return $fullTitle;
            },
        );
    }
}
