<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Illuminate\Support\Collection;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleTopicRelation extends AbstractBatchCommand
{
    private Collection $byArticleCommands;

    public function __construct(Collection $commandsGroupedByArticle)
    {
        $this->byArticleCommands = $commandsGroupedByArticle;
    }

    public function linesCount(): int
    {
        return $this->byArticleCommands->count();
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

            $collectionGroup = CollectionGroup::where('handle', TopicCommand::HANDLE)->firstOrFail();

            $topicsCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->where(function ($query) use ($articleCommands) {
                    foreach ($articleCommands as $command) {
                        $query->orWhere('attribute_data->geslib-code->value', $command->topicId);
                    }
                })->get();

            if ($topicsCollection->isEmpty()) {
                $this->addLog(
                    CommandContract::LEVEL_WARNING,
                    "Topics with code [{$articleCommands->pluck('topicId')->implode(', ')}] not found in line type [{$articleCommands->first()->getType()}].",
                );
                continue;
            }

            if ($topicsCollection->count() < $articleCommands->count()) {
                $this->addLog(
                    CommandContract::LEVEL_WARNING,
                    "Some topics with code [{$articleCommands->pluck('topicId')->implode(', ')}] not found in line type [{$articleCommands->first()->getType()}].",
                );
            }

            (new CollectionGroupSync($product, $collectionGroup->id, $topicsCollection))->handle();
        }
    }
}
