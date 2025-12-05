<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Models;

use NumaxLab\Lunar\Geslib\Services\CegalAvailability;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends \Lunar\Models\ProductVariant
{
    #[\Override]
    public function getDescription(): string
    {
        return $this->product->recordTitle;
    }

    #[\Override]
    public function getThumbnail(): ?Media
    {
        return $this->product->getFirstMedia(config('lunar.media.collection'));
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->product->getFirstMediaUrl(config('lunar.media.collection'), 'small');
    }

    #[\Override]
    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        if ($this->purchasable == 'always') {
            return true;
        }

        $cegalAvailability = app(CegalAvailability::class);

        $firstTrustedStockProvider = $cegalAvailability->getAvailability($this);

        if ($firstTrustedStockProvider) {
            return true;
        }

        return $quantity <= $this->getTotalInventory();
    }
}
