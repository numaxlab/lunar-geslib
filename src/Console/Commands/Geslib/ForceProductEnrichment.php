<?php

namespace NumaxLab\Lunar\Geslib\Console\Commands\Geslib;

use Illuminate\Console\Command;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;

class ForceProductEnrichment extends Command
{
    protected $signature = 'lunar:geslib:force-product-enrichment';

    protected $description = 'Force the product enrichment process';

    public function handle(): int
    {
        $this->withProgressBar(ProductVariant::all(), function (ProductVariant $variant) {
            GeslibArticleUpdated::dispatch($variant);
        });

        $this->info('Done!');

        return self::SUCCESS;
    }
}