<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Services;

use Lunar\Models\ProductVariant;
use NumaxLab\Dilve\Client;

final readonly class DilveEnricher
{
    public function __construct(private Client $client) {}

    public function enrich(ProductVariant $productVariant): void
    {
        if (! config('lunar.geslib.dilve.enabled')) {
            return;
        }

        if (! $productVariant->gtin) {
            return;
        }

        $onixProduct = $this->client->getProductByIsbn($productVariant->gtin);

        if (! $onixProduct || ! $onixProduct->coverUrl) {
            return;
        }

        $currentPrimaryImage = $productVariant->product->getFirstMedia(
            config('lunar.media.collection'),
            ['primary' => true],
        );

        if ($currentPrimaryImage) {
            $currentPrimaryImage->delete();
        }

        $productVariant->product
            ->addMediaFromUrl($onixProduct->coverUrl)
            ->usingName(__('Portada'))
            ->usingFileName(
                $productVariant->product->defaultUrl->slug.'-portada.'.pathinfo(
                    (string) $onixProduct->coverUrl,
                    PATHINFO_EXTENSION,
                ),
            )
            ->withCustomProperties([
                'primary' => true,
            ])
            ->toMediaCollection(config('lunar.media.collection'));
    }
}
