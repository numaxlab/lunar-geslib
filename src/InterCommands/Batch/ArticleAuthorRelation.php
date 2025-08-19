<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Models\Author;

class ArticleAuthorRelation extends AbstractBatchCommand
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

        $authorsSync = [];

        foreach ($this->data as $item) {
            $author = Author::where('geslib_code', $item['authorId'])->first();

            if (! $author) {
                continue;
            }

            $authorsSync[$author->id] = [
                'position' => $item['position'],
                'author_type' => $item['authorType'],
            ];
        }

        $product->authors()->sync($authorsSync);
    }
}
