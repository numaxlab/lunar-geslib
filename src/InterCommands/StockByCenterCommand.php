<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\ListField;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\CenterStock;

class StockByCenterCommand extends AbstractCommand
{
    public function __construct(private readonly CenterStock $centerStock) {}

    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->centerStock->articleId())->first();

        if (! $variant) {
            return;
        }

        $data = $variant->translateAttribute('stock-by-center');

        $data = $data ? (array) $data : [];

        $data[$this->centerStock->centerId()] = $this->centerStock->quantity();

        $variant->update([
            'attribute_data' => [
                'stock-by-center' => new ListField($data),
            ],
        ]);
    }
}
