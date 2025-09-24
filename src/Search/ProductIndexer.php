<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductIndexer extends \Lunar\Search\ProductIndexer
{
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'thumbnail',
            'variants',
            'productType',
            'brand',
            'authors',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $data = array_merge([
            'id' => (string) $model->id,
        ], $this->mapSearchableAttributes($model));

        $data = array_merge($data, [
            'authors' => $model->authors->map(fn ($author) => $author->toSearchableArray())->toArray(),
            'isbns' => $model->variants->pluck('gtin')->toArray(),
            'brand' => $model->brand?->name,
            'status' => $model->status,
            'product_type' => $model->productType->name,
            'created_at' => (int) $model->created_at->timestamp,
        ]);

        $data['skus'] = $model->variants->pluck('sku')->toArray();
        $data['eans'] = $model->variants->pluck('ean')->toArray();

        if ($thumbnail = $model->thumbnail) {
            $data['thumbnail'] = $thumbnail->getUrl('small');
        }

        return $data;
    }
}
