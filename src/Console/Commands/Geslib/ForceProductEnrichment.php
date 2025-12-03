<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Console\Commands\Geslib;

use Illuminate\Console\Command;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class ForceProductEnrichment extends Command
{
    protected $signature = 'lunar:geslib:force-product-enrichment';

    protected $description = 'Force the product enrichment process';

    public function handle(): int
    {
        $this->withProgressBar(ProductVariant::all(), function (ProductVariant $variant): void {
            try {
                GeslibArticleUpdated::dispatch($variant);
            } catch (FileCannotBeAdded $fileCannotBeAdded) {
                $this->newLine();
                $this->error($fileCannotBeAdded->getMessage());
            }
        });

        $this->info('Done!');

        return self::SUCCESS;
    }
}
