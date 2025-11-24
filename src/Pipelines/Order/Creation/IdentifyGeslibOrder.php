<?php

namespace NumaxLab\Lunar\Geslib\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Contracts\Order as OrderContract;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;

class IdentifyGeslibOrder
{
    public function handle(OrderContract $order, Closure $next): mixed
    {
        $isGeslibOrder = false;

        foreach ($order->lines as $line) {
            if ($line->purchasable_type === 'product_variant') {
                if ($line->purchasable->product->product_type_id === ArticleCommand::PRODUCT_TYPE_ID) {
                    $isGeslibOrder = true;
                    break;
                }
            }
        }

        if ($isGeslibOrder) {
            $order->updateQuietly([
                'is_geslib' => true,
            ]);
        }

        return $next($order);
    }
}
