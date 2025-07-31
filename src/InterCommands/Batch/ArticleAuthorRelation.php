<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Illuminate\Support\Collection;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Models\Author;

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

            $authorsSync = [];

            foreach ($articleCommands as $command) {
                $author = Author::where('geslib_code', $command->authorId)->first();

                if (!$author) {
                    continue;
                }

                $authorsSync[$author->id] = [
                    'position' => $command->position,
                    'author_type' => $command->authorType,
                ];
            }

            $product->authors()->sync($authorsSync);
        }
    }
}
