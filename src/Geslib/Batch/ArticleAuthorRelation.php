<?php

namespace NumaxLab\Lunar\Geslib\Geslib\Batch;

use Illuminate\Support\Collection;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\AuthorType;
use NumaxLab\Lunar\Geslib\Geslib\AuthorCommand;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleAuthorRelation
{
    private Collection $byArticleCommands;

    public function __construct(Collection $commandsGroupedByArticle)
    {
        $this->byArticleCommands = $commandsGroupedByArticle;
    }

    public function __invoke()
    {
        foreach ($this->byArticleCommands as $articleId => $articleCommands) {
            $variant = ProductVariant::where('sku', $articleId)->first();

            if (!$variant) {
                continue;
            }

            $product = $variant->product;

            $collectionGroup = CollectionGroup::where('handle', AuthorCommand::HANDLE)->firstOrFail();
            $byAuthorTypeCommands = $articleCommands->groupBy('authorType');
            $productAttributeData = [];

            foreach ($byAuthorTypeCommands as $authorType => $authorTypeCommands) {
                $authorTypeCommands = $authorTypeCommands->sortBy('position');

                $authorsCollection = LunarCollection::where('collection_group_id', $collectionGroup->id)
                    ->where(function ($query) use ($authorTypeCommands) {
                        foreach ($authorTypeCommands as $command) {
                            $query->orWhere('attribute_data->geslib-code->value', $command->authorId);
                        }
                    })->get();

                if ($authorsCollection->isEmpty()) {
                    continue;
                }

                $authorsString = $authorsCollection
                    ->map(fn($author) => $author->attribute_data->get('name')->getValue())
                    ->implode('; ');

                if ($authorType === AuthorType::AUTHOR) {
                    (new CollectionGroupSync($product, $collectionGroup->id, $authorsCollection))->handle();
                    continue;
                }

                if ($authorType === AuthorType::TRANSLATOR) {
                    $productAttributeData['translator'] = new Text($authorsString);
                    continue;
                }

                if ($authorType === AuthorType::ILLUSTRATOR) {
                    $productAttributeData['illustrator'] = new Text($authorsString);
                    continue;
                }

                if ($authorType === AuthorType::COVER_ILLUSTRATOR) {
                    $productAttributeData['cover-illustrator'] = new Text($authorsString);
                    continue;
                }

                if ($authorType === AuthorType::BACK_COVER_ILLUSTRATOR) {
                    $productAttributeData['back-cover-illustrator'] = new Text($authorsString);
                }
            }

            if (count($productAttributeData) > 0) {
                $product->update([
                    'attribute_data' => array_merge($product->attribute_data->toArray(), $productAttributeData),
                ]);
            }
        }
    }
}
