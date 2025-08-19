<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Lunar\Models\ProductVariant;
use NumaxLab\Dilve\Client;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleCreated;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;

class EnrichProductFromDilveSubscriber implements ShouldQueue
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            GeslibArticleCreated::class,
            self::handleCreatedProduct(...),
        );

        $events->listen(
            GeslibArticleUpdated::class,
            self::handleUpdatedProduct(...),
        );
    }

    public function handleCreatedProduct(GeslibArticleCreated $event): void
    {
        $this->handle($event->productVariant);
    }

    private function handle(ProductVariant $productVariant): void
    {
        if (! $productVariant->gtin) {
            return;
        }

        $client = new Client(
            config('lunar.geslib.dilve.username'),
            config('lunar.geslib.dilve.password'),
        );

        $onixProduct = $client->getProductByIsbn($productVariant->gtin);

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
            ->usingName('Portada')
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

    public function handleUpdatedProduct(GeslibArticleUpdated $event): void
    {
        $this->handle($event->productVariant);
    }
}
