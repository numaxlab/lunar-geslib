<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Number;
use Lunar\Models\Product;
use NumaxLab\Geslib\Lines\Stock;

class StockCommand
{

    public function __invoke(Stock $stock): void
    {
        $product = Product::where('attribute_data->geslib-code->value', $stock->articleId())->first();


        if ($product) {
            $product->update([
                'attribute_data' => [
                    'quantity' => new Number($stock->quantity()),
                ],
            ]);
        }
    }
}
