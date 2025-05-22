<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Stock;

class StockCommand
{

    public function __invoke(Stock $stock): void
    {
        $variant = ProductVariant::where('sku', $stock->articleId())->first();

        if (!$variant) {
            return;
        }

        $variant->update([
            'stock' => $stock->quantity(),
        ]);
    }
}
