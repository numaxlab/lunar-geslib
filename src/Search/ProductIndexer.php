<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use NumaxLab\Lunar\Geslib\Handle;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;

class ProductIndexer extends \Lunar\Search\ProductIndexer
{
    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'product_type',
            'taxonomies',
            'languages',
            'price',
            'geslib_status',
            'status',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'thumbnail',
            'variants',
            'variant.prices',
            'variant.prices.priceable',
            'variant.prices.priceable.taxClass',
            'variant.prices.priceable.taxClass.taxRateAmounts',
            'variant.prices.currency',
            'productType',
            'brand',
            'authors',
            'collections',
            'collections.group',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $data = array_merge([
            'id' => (string) $model->id,
        ], $this->mapSearchableAttributes($model));

        $collectionsByGroup = $model->collections->groupBy('group.handle');

        $taxonomies = collect();
        $relatedTaxonomies = $collectionsByGroup->get(Handle::COLLECTION_GROUP_TAXONOMIES);

        if ($relatedTaxonomies) {
            $relatedTaxonomies->each(function ($item) use (&$taxonomies) {
                $taxonomies->push($item);
                $taxonomies = $taxonomies->merge($item->ancestors);
            });
        }

        $languages = $collectionsByGroup->get(LanguageCommand::HANDLE);
        $geslibStatus = $collectionsByGroup->get(StatusCommand::HANDLE);

        $pricing = $model->variant->pricing()->get()->matched;

        $data = array_merge($data, [
            'authors' => $model->authors->map(fn($author) => $author->toSearchableArray())->toArray(),
            'taxonomies' => $taxonomies->map(fn($taxon) => $taxon->toSearchableArray())->toArray(),
            'languages' => $languages?->map(fn($language) => $language->toSearchableArray())->toArray(),
            'isbns' => $model->variants->pluck('gtin')->toArray(),
            'price' => $pricing->priceIncTax()->unitDecimal(),
            'geslib_status' => $geslibStatus?->first()->toSearchableArray(),
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
