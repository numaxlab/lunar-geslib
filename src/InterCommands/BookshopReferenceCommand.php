<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\BookshopReference;

class BookshopReferenceCommand extends AbstractCommand
{
    public function __invoke(BookshopReference $bookshopReference)
    {
        $variant = ProductVariant::where('sku', $bookshopReference->articleId())->first();

        if (!$variant) {
            return;
        }

        $product = $variant->product;

        $product->update([
            'attribute_data' => array_merge($product->attribute_data->toArray(), [
                'bookshop-reference' => new Text($bookshopReference->value()),
            ]),
        ]);
    }
}
