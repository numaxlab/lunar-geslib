<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleCreated;
use NumaxLab\Lunar\Geslib\Services\DilveEnricher;

final readonly class HandleGeslibArticleCreated implements ShouldQueue
{
    public function __construct(private DilveEnricher $dilveEnricher) {}

    public function handle(GeslibArticleCreated $event): void
    {
        $this->dilveEnricher->enrich($event->productVariant);
    }
}
