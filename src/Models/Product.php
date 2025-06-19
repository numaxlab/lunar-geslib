<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;

class Product extends \Lunar\Models\Product
{
    public function authors(): BelongsToMany
    {
        return $this
            ->collections()
            ->whereHas('group', function ($query) {
                $query->where('handle', AuthorCommand::HANDLE);
            });
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
