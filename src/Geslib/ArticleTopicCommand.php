<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\ArticleTopic;

class ArticleTopicCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->isBatch = true;
    }

    public function __invoke(ArticleTopic $articleTopic): void
    {
        $variant = ProductVariant::where('sku', $articleTopic->articleId())->first();

        $group = CollectionGroup::where('handle', TopicCommand::HANDLE)->firstOrFail();
        $collection = Collection::where('attribute_data->geslib-code->value', $articleTopic->topicId())
            ->where('collection_group_id', $group->id)
            ->first();

        if (!$variant || !$collection) {
            return;
        }

        $currentCollections = $variant->product->collections();
    }
}
