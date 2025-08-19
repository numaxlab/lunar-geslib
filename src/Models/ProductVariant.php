<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends \Lunar\Models\ProductVariant
{
    public function getDescription(): string
    {
        return $this->product->recordFullTitle;
    }

    public function getThumbnail(): ?Media
    {
        return $this->product->getFirstMedia(config('lunar.media.collection'));
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->product->getFirstMediaUrl(config('lunar.media.collection'), 'small');
    }
}
