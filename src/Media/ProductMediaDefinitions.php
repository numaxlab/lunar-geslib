<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Media;

use Lunar\Base\MediaDefinitionsInterface;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductMediaDefinitions implements MediaDefinitionsInterface
{
    public function registerMediaCollections(HasMedia $model): void
    {
        $fallbackUrl = config('lunar.media.fallback.url');
        $fallbackPath = config('lunar.media.fallback.path');

        // Reset to avoid duplication
        $model->mediaCollections = [];

        $collection = $model->addMediaCollection(
            config('lunar.media.collection'),
        );

        if ($fallbackUrl) {
            $collection = $collection->useFallbackUrl($fallbackUrl);
        }

        if ($fallbackPath) {
            $collection = $collection->useFallbackPath($fallbackPath);
        }

        $this->registerCollectionConversions($collection, $model);
    }

    protected function registerCollectionConversions(MediaCollection $collection, HasMedia $model): void
    {
        $conversions = [
            'zoom' => [
                'width' => 1000,
                'fit' => Fit::Contain,
            ],
            'large' => [
                'width' => 800,
                'fit' => Fit::Contain,
            ],
            'medium' => [
                'width' => 500,
                'fit' => Fit::Contain,
            ],
            'open-graph' => [
                'width' => 1200,
                'height' => 630,
                'fit' => Fit::Fill,
            ],
        ];

        $collection->registerMediaConversions(function (Media $media) use ($model, $conversions): void {
            foreach ($conversions as $key => $conversion) {
                if ($conversion['fit']->value === Fit::Fill->value) {
                    $model
                        ->addMediaConversion($key)
                        ->fit(
                            fit: $conversion['fit'],
                            desiredWidth: $conversion['width'],
                            desiredHeight: $conversion['height'],
                        )
                        ->border(0, BorderType::Overlay, color: '#FFF')
                        ->background('#FFF')
                        ->keepOriginalImageFormat();
                } else {
                    $model
                        ->addMediaConversion($key)
                        ->fit(
                            fit: $conversion['fit'],
                            desiredWidth: $conversion['width'],
                        )
                        ->keepOriginalImageFormat();
                }
            }
        });
    }

    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void
    {
        // Add a conversion for the admin panel to use
        $model
            ->addMediaConversion('small')
            ->fit(
                fit: Fit::Contain,
                desiredWidth: 300,
            )
            ->sharpen(10)
            ->keepOriginalImageFormat();
    }

    public function getMediaCollectionTitles(): array
    {
        return [
            config('lunar.media.collection') => __('lunar::base.standard-media-definitions.collection-titles.images'),
        ];
    }

    public function getMediaCollectionDescriptions(): array
    {
        return [
            config('lunar.media.collection') => '',
        ];
    }
}
