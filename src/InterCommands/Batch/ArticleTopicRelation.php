<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleTopicRelation extends AbstractBatchCommand
{
    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->articleId)->first();

        if (!$variant) {
            $this->addLog(
                CommandContract::LEVEL_WARNING,
                "Product with code [{$this->articleId}] not found.",
            );
            return;
        }

        $product = $variant->product;

        $collectionGroup = CollectionGroup::where('handle', TopicCommand::HANDLE)->firstOrFail();

        $topicsCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
            ->where(function ($query) {
                foreach ($this->data as $item) {
                    $query->orWhere('geslib_code', $item['topicId']);
                }
            })->get();

        if ($topicsCollection->isEmpty()) {
            $this->addLog(
                CommandContract::LEVEL_WARNING,
                sprintf('Topics with code [%s] not found.', collect($this->data)->pluck('topicId')->implode(', ')),
            );

            return;
        }

        if ($topicsCollection->count() < count($this->data)) {
            $this->addLog(
                CommandContract::LEVEL_WARNING,
                sprintf(
                    'Some topics in the ones with code [%s] not found.',
                    collect($this->data)->pluck('topicId')->implode(', '),
                ),
            );
        }

        (new CollectionGroupSync($product, $collectionGroup->id, $topicsCollection))->handle();
    }
}
