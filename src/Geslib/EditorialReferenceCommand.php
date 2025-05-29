<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\EditorialReference;

class EditorialReferenceCommand extends AbstractCommand
{
    public function __invoke(EditorialReference $editorialReference)
    {
        $variant = ProductVariant::where('sku', $editorialReference->articleId())->first();

        if (!$variant) {
            return;
        }

        $product = $variant->product;

        $product->update([
            'attribute_data' => array_merge($product->attribute_data->toArray(), [
                'editorial-reference' => new Text($editorialReference->value()),
            ]),
        ]);
    }
}
