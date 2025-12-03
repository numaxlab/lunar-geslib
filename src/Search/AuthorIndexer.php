<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Search;

use Illuminate\Database\Eloquent\Model;
use Lunar\Search\ScoutIndexer;

class AuthorIndexer extends ScoutIndexer
{
    #[\Override]
    public function toSearchableArray(Model $model): array
    {
        return array_merge([
            'id' => (string) $model->id,
            'name' => $model->name,
        ], $this->mapSearchableAttributes($model));
    }
}
