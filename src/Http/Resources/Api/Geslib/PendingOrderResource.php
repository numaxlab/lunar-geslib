<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingOrderResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'n_pedido' => $this->resource->reference,
            'cliente' => $this->resource->customer?->meta?->offsetExists('geslib_id') ?
                $this->resource->customer->meta->offsetGet('geslib_id') : null,
        ];
    }
}
