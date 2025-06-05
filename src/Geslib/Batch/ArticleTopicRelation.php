<?php

namespace NumaxLab\Lunar\Geslib\Geslib\Batch;

use Illuminate\Support\Collection;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Geslib\TopicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleTopicRelation
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
                continue;
            }

            $product = $variant->product;

            $collectionGroup = CollectionGroup::where('handle', TopicCommand::HANDLE)->firstOrFail();

            $topicsCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->where(function ($query) use ($articleCommands) {
                    foreach ($articleCommands as $command) {
                        $query->orWhere('attribute_data->geslib-code->value', $command->topicId);
                    }
                })->get();

            if ($topicsCollection->isEmpty()) {
                continue;
            }

            (new CollectionGroupSync($product, $collectionGroup->id, $topicsCollection))->handle();
        }
    }
}
