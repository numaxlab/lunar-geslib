<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use NumaxLab\Geslib\Lines\AuthorType;

class Product extends \Lunar\Models\Product
{
    public function authors(): BelongsToMany
    {
        return $this->contributorsByType(AuthorType::AUTHOR);
    }

    public function contributorsByType(string $type): BelongsToMany
    {
        return $this->contributors()->wherePivot('author_type', $type)->orderBy('position');
    }

    public function contributors(): BelongsToMany
    {
        return $this->belongsToMany(
            Author::class,
            config('lunar.database.table_prefix') . 'geslib_author_product',
        )->withPivot(['author_type', 'position']);
    }

    public function translators(): BelongsToMany
    {
        return $this->contributorsByType(AuthorType::TRANSLATOR);
    }

    public function illustrators(): BelongsToMany
    {
        return $this->contributorsByType(AuthorType::ILLUSTRATOR);
    }

    public function coverIllustrators(): BelongsToMany
    {
        return $this->contributorsByType(AuthorType::COVER_ILLUSTRATOR);
    }

    public function backCoverIllustrators(): BelongsToMany
    {
        return $this->contributorsByType(AuthorType::BACK_COVER_ILLUSTRATOR);
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
