<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Models;

use NumaxLab\Lunar\Geslib\Services\CegalAvailabilityService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends \Lunar\Models\ProductVariant
{
    public function getDescription(): string
    {
        return $this->product->recordTitle;
    }

    public function getThumbnail(): ?Media
    {
        return $this->product->getFirstMedia(config('lunar.media.collection'));
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->product->getFirstMediaUrl(config('lunar.media.collection'), 'small');
    }

    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        if ($this->purchasable == 'always') {
            return true;
        }

        $cegalAvailabilityService = app(CegalAvailabilityService::class);

        $cegalAvailability = $cegalAvailabilityService->getAvailability($this);

        if ($cegalAvailability) {
            return true;
        }

        return $quantity <= $this->getTotalInventory();
    }
}
