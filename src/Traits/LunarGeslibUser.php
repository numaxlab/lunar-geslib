<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Product;

trait LunarGeslibUser
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

    public function getNameAttribute(): string
    {
        return $this->latestCustomer()?->first_name ?? '';
    }

    public function getLastNameAttribute(): string
    {
        return $this->latestCustomer()?->last_name ?? '';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->last_name ?? '';
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
