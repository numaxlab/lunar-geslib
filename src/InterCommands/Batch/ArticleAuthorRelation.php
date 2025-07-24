<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Illuminate\Support\Collection;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\AuthorType;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleAuthorRelation extends AbstractBatchCommand
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

                if ($authorType === AuthorType::AUTHOR) {
                    (new CollectionGroupSync($product, $collectionGroup->id, $authorsCollection))->handle();
                    continue;
                }

                if ($authorsCollection->isEmpty()) {
                    continue;
                }

                $authorsString = $authorsCollection
                    ->map(fn($author) => $author->attribute_data->get('name')->getValue())
                    ->implode('; ');

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
