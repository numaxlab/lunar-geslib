<?php

namespace NumaxLab\Lunar\Geslib\Search;

use Illuminate\Database\Eloquent\Model;
use Lunar\Search\ScoutIndexer;

class AuthorIndexer extends ScoutIndexer
{
    public function toSearchableArray(Model $model): array
    {
        $data = array_merge([
            'id' => (string) $model->id,
            'name' => $model->name,
        ], $this->mapSearchableAttributes($model));

        return $data;
    }
}
