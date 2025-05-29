<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\ArticleIndex;

class ArticleIndexCommand extends AbstractCommand
{
    public function __invoke(ArticleIndex $articleIndex)
    {
        $variant = ProductVariant::where('sku', $articleIndex->articleId())->first();

        if (!$variant) {
            return;
        }

        $product = $variant->product;

        $product->update([
            'attribute_data' => array_merge($product->attribute_data->toArray(), [
                'index' => new Text($articleIndex->value()),
            ]),
        ]);
    }
}
