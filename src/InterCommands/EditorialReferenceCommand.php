<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\EditorialReference;

class EditorialReferenceCommand extends AbstractCommand
{
    public function __construct(private readonly EditorialReference $editorialReference) {}

    public function __invoke()
    {
        $variant = ProductVariant::where('sku', $this->editorialReference->articleId())->first();

        if (! $variant) {
            return;
        }

        $product = $variant->product;

        $product->update([
            'attribute_data' => array_merge($product->attribute_data->toArray(), [
                'editorial-reference' => new Text($this->editorialReference->value()),
            ]),
        ]);
    }
}
