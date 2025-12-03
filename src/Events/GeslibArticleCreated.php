<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Models\ProductVariant;

class GeslibArticleCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ProductVariant $productVariant) {}
}
