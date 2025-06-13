<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Illuminate\Support\Collection;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleIbicRelation extends AbstractBatchCommand
{
    private Collection $byArticleCommands;

    public function __construct(Collection $commandsGroupedByArticle)
    {
        $this->byArticleCommands = $commandsGroupedByArticle;
    }

    public function __invoke(): void
    {
        foreach ($this->byArticleCommands as $articleId => $articleCommands) {
            $variant = ProductVariant::where('sku', $articleId)->first();

            if (!$variant) {
                $this->addLog(
                    CommandContract::LEVEL_WARNING,
                    "Product with code [{$articleId}] not found in line type [{$articleCommands->first()->getType()}].",
                );
                continue;
            }

            $product = $variant->product;

            $collectionGroup = CollectionGroup::where('handle', IbicCommand::HANDLE)->firstOrFail();

            $ibicCollection = $this->getIbicCollection($collectionGroup, $articleCommands);

            if ($ibicCollection->count() < $articleCommands->count()) {
                $this->createMissingCollections($collectionGroup, $articleCommands);

                $ibicCollection = $this->getIbicCollection($collectionGroup, $articleCommands);
            }

            (new CollectionGroupSync($product, $collectionGroup->id, $ibicCollection))->handle();
        }
    }

    private function getIbicCollection($collectionGroup, $articleCommands)
    {
        return LunarCollection::where('collection_group_id', $collectionGroup->id)
            ->where(function ($query) use ($articleCommands) {
                foreach ($articleCommands as $command) {
                    $query->orWhere('attribute_data->geslib-code->value', $command->code);
                }
            })->get();
    }

    private function createMissingCollections(CollectionGroup $collectionGroup, Collection $ibicCommands): void
    {
        foreach ($ibicCommands as $command) {
            $ibicCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->where('attribute_data->geslib-code->value', $command->code)->first();

            if ($ibicCollection) {
                continue;
            }

            LunarCollection::create([
                'collection_group_id' => $collectionGroup->id,
                'attribute_data' => [
                    'geslib-code' => new Text($command->code),
                    'name' => new Text($command->description),
                ],
            ]);
        }
    }
}
