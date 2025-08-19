<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleIbicRelation extends AbstractBatchCommand
{
    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->articleId)->first();

        if (! $variant) {
            $this->addLog(
                CommandContract::LEVEL_WARNING,
                "Product with code [{$this->articleId}] not found.",
            );

            return;
        }

        $product = $variant->product;

        $collectionGroup = CollectionGroup::where('handle', IbicCommand::HANDLE)->firstOrFail();

        $ibicCollection = $this->getIbicCollection($collectionGroup);

        if ($ibicCollection->count() < count($this->data)) {
            $this->createMissingCollections($collectionGroup);

            $ibicCollection = $this->getIbicCollection($collectionGroup);
        }

        (new CollectionGroupSync($product, $collectionGroup->id, $ibicCollection))->handle();
    }

    private function getIbicCollection($collectionGroup)
    {
        return LunarCollection::where('collection_group_id', $collectionGroup->id)
            ->where(function ($query) {
                foreach ($this->data as $item) {
                    $query->orWhere('geslib_code', $item['code']);
                }
            })->get();
    }

    private function createMissingCollections(CollectionGroup $collectionGroup): void
    {
        foreach ($this->data as $item) {
            $ibicCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->where('geslib_code', $item['code'])->first();

            if ($ibicCollection) {
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
