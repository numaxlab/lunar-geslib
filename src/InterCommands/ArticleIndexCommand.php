<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\ArticleIndex;

class ArticleIndexCommand extends AbstractCommand
{
    public function __construct(private readonly ArticleIndex $articleIndex) {}

    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->articleIndex->articleId())->first();

        if (! $variant) {
            return;
        }

        $product = $variant->product;

        $product->update([
            'attribute_data' => array_merge($product->attribute_data->toArray(), [
                'index' => new TranslatedText(collect([
                    'es' => new Text($this->articleIndex->value()),
                ])),
            ]),
        ]);
    }
}
