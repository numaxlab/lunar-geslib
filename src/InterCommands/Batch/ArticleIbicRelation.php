<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Illuminate\Support\Collection;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;
use NumaxLab\Lunar\Geslib\Support\ImportRegistry;

class ArticleIbicRelation extends AbstractBatchCommand
{
    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->articleId)->first();

        if (! $variant) {
            $this->addLog(
                CommandContract::LEVEL_WARNING,
                sprintf('Product with code [%s] not found.', $this->articleId),
            );

            return;
        }

        $product = $variant->product;

        $collectionGroup = ImportRegistry::collectionGroup(IbicCommand::HANDLE);

        $ibicCollection = $this->getIbicCollection($collectionGroup);

        if ($ibicCollection->count() < count($this->data)) {
            $this->createMissingCollections($collectionGroup, $ibicCollection);

            $ibicCollection = $this->getIbicCollection($collectionGroup);
        }

        new CollectionGroupSync($product, $collectionGroup->id, $ibicCollection)->handle();

        $product->searchable();
    }

    private function getIbicCollection(CollectionGroup $collectionGroup): Collection
    {
        return LunarCollection::where('collection_group_id', $collectionGroup->id)
            ->where(function ($query): void {
                foreach ($this->data as $item) {
                    $query->orWhere('geslib_code', $item['code']);
                }
            })->get();
    }

    private function createMissingCollections(CollectionGroup $collectionGroup, Collection $existing): void
    {
        $existingCodes = $existing->pluck('geslib_code')->toArray();

        foreach ($this->data as $item) {
            if (in_array($item['code'], $existingCodes)) {
                continue;
            }

            LunarCollection::create([
                'collection_group_id' => $collectionGroup->id,
                'geslib_code' => $item['code'],
                'attribute_data' => [
                    'name' => new Text($item['description']),
                ],
            ]);
        }
    }
}
