<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Console\Commands\Search;

use Exception;
use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;

class EnsureIndexes extends Command
{
    protected $signature = 'lunar:meilisearch:ensure-indexes';

    protected $description = 'Ensure that all Meilisearch indexes exist for the models used with Scout.';

    protected MeilisearchEngine $client;

    public function __construct(EngineManager $engineManager)
    {
        parent::__construct();

        $this->client = $engineManager->createMeilisearchDriver();
    }

    public function handle(): int
    {
        $models = config('lunar.search.models', []);

        $this->info('Ensuring indexes for the following models: '.implode(', ', $models));

        foreach ($models as $modelClass) {
            $model = new $modelClass;
            $indexName = $model->searchableAs();

            try {
                $this->client->getIndex($indexName);
                $this->line("The index '{$indexName}' already exists.");
            } catch (Exception $e) {
                if ($e->getCode() === 404) {
                    $this->warn("The index '{$indexName}' does not exist. Creating...");
                    $this->client->createIndex($indexName, ['primaryKey' => $model->getScoutKeyName()]);
                    $this->info("Index '{$indexName}' created.");
                } else {
                    $this->error("Error verifying the index '{$indexName}': ".$e->getMessage());
                }
            }
        }

        $this->info('All indexes where verified.');

        return self::SUCCESS;
    }
}
