<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Stock;

class StockCommand extends AbstractCommand
{
    public function __construct(private readonly Stock $stock) {}

    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->stock->articleId())->first();

        if (! $variant) {
            return;
        }

        $variant->update([
            'stock' => $this->stock->quantity(),
        ]);
    }
}
