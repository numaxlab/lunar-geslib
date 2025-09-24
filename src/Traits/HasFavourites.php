<?php

namespace NumaxLab\Lunar\Geslib\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Product;

trait HasFavourites
{
    public function favourites(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Product::modelClass(),
                config('lunar.database.table_prefix').'geslib_product_user_favourites',
            )->withTimestamps()
            ->orderBy(config('lunar.database.table_prefix').'geslib_product_user_favourites.created_at', 'desc');
    }
}
