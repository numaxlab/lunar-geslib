<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Stock;

class StockCommand extends AbstractCommand
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
