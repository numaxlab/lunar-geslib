<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Services;

use Illuminate\Support\Facades\Cache;
use NumaxLab\Cegal\Client;
use NumaxLab\Cegal\Dto\BookAvailability;
use NumaxLab\Cegal\Exceptions\CegalApiException;
use NumaxLab\Lunar\Geslib\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Models\TrustedStockProvider;

class CegalAvailability
{
    public function __construct(protected Client $client) {}

    public function getAvailability(ProductVariant $variant): ?TrustedStockProvider
    {
        if (! config('lunar.geslib.cegal.enabled')) {
            return null;
        }

        if (! $variant->gtin) {
            return null;
        }

        $cacheKey = 'cegal_availability_'.$variant->sku;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($variant) {
            $trustedStockProviders = TrustedStockProvider::all();

            if ($trustedStockProviders->isEmpty()) {
                return null;
            }

            $trustedStockProviders = $trustedStockProviders->keyBy('sinli_id');

            try {
                $cegalAvailabilityCollection = $this->client->getAvailability($variant->gtin);
            } catch (CegalApiException) {
                return null;
            }

            /** @var BookAvailability $cegalAvailability */
            foreach ($cegalAvailabilityCollection as $cegalAvailability) {
                if ($trustedStockProviders->has($cegalAvailability->sinliId)) {
                    return $trustedStockProviders->get($cegalAvailability->sinliId);
                }
            }

            return null;
        });
    }
}
