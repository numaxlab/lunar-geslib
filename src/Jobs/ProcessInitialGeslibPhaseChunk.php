<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Geslib\Lines\ArticleBatchHeader;
use NumaxLab\Geslib\Lines\ArticleBatchLine;
use NumaxLab\Geslib\Lines\ArticleGpsr;
use NumaxLab\Geslib\Lines\ArticleIndex;
use NumaxLab\Geslib\Lines\ArticleIndexTranslation;
use NumaxLab\Geslib\Lines\ArticleResource;
use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Geslib\Lines\ArticleTranslation;
use NumaxLab\Geslib\Lines\Author;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Geslib\Lines\BindingType;
use NumaxLab\Geslib\Lines\BookshopReference;
use NumaxLab\Geslib\Lines\BookshopReferenceTranslation;
use NumaxLab\Geslib\Lines\CenterStock;
use NumaxLab\Geslib\Lines\Classification;
use NumaxLab\Geslib\Lines\Coedition;
use NumaxLab\Geslib\Lines\Collection;
use NumaxLab\Geslib\Lines\Country;
use NumaxLab\Geslib\Lines\EBook;
use NumaxLab\Geslib\Lines\Editorial;
use NumaxLab\Geslib\Lines\EditorialReference;
use NumaxLab\Geslib\Lines\EditorialReferenceTranslation;
use NumaxLab\Geslib\Lines\Ibic;
use NumaxLab\Geslib\Lines\Language;
use NumaxLab\Geslib\Lines\PressPublication;
use NumaxLab\Geslib\Lines\Province;
use NumaxLab\Geslib\Lines\RecordLabel;
use NumaxLab\Geslib\Lines\StationeryCategory;
use NumaxLab\Geslib\Lines\Status;
use NumaxLab\Geslib\Lines\Stock;
use NumaxLab\Geslib\Lines\Topic;
use NumaxLab\Geslib\Lines\Type;
use NumaxLab\Geslib\Lines\Warning;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleAuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleIndexCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleTopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorBiographyCommand;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\InterCommands\BookshopReferenceCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\InterCommands\EditorialCommand;
use NumaxLab\Lunar\Geslib\InterCommands\EditorialReferenceCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\PressPublicationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\RecordLabelCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StockByCenterCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StockCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;
use RuntimeException;
use Throwable;

/**
 * Processes a chunk of Geslib lines for a single phase of the initial load strategy.
 *
 * Phases are defined by GeslibInitialPhasePartitioner:
 *   1 — Base entities (Editorial, Author, Collection, etc.)
 *   2 — Articles and article indexes
 *   3 — Stock and dependent updates
 *   4 — Batch relations (handled via GeslibInterFileBatchLine, not this job)
 */
class ProcessInitialGeslibPhaseChunk implements ShouldQueue
{
    use Queueable;

    public $tries = 2;

    private const array VALID_PHASES = [1, 2, 3, 4];

    /**
     * @param  object[]  $lines  Parsed Geslib line objects for this chunk
     */
    public function __construct(
        protected GeslibInterFile $geslibInterFile,
        protected int $phase,
        protected array $lines,
    ) {
        $this->onQueue('geslib-inter-files');
    }

    public function handle(): void
    {
        if (! in_array($this->phase, self::VALID_PHASES, strict: true)) {
            throw new RuntimeException(
                "Invalid phase {$this->phase} for ProcessInitialGeslibPhaseChunk. Valid phases: ".
                implode(', ', self::VALID_PHASES),
            );
        }

        $log = is_array($this->geslibInterFile->log) ? $this->geslibInterFile->log : [];

        if (count($log) > 2000) {
            $log = [];
        }

        $batchCommands = collect();

        foreach ($this->lines as $line) {
            $command = null;

            match ($line->getCode()) {
                Editorial::CODE => $command = new EditorialCommand($line),
                RecordLabel::CODE => $command = new RecordLabelCommand($line),
                StationeryCategory::CODE => null,
                PressPublication::CODE => $command = new PressPublicationCommand($line),
                Collection::CODE => $command = new CollectionCommand($line),
                Topic::CODE => $command = new TopicCommand($line),
                Article::CODE => $command = new ArticleCommand($line),
                EBook::CODE => $command = new ArticleCommand($line, true),
                ArticleTopic::CODE => $command = new ArticleTopicCommand($line),
                Ibic::CODE => $command = new IbicCommand($line),
                BookshopReference::CODE => $command = new BookshopReferenceCommand($line),
                EditorialReference::CODE => $command = new EditorialReferenceCommand($line),
                ArticleIndex::CODE => $command = new ArticleIndexCommand($line),
                BookshopReferenceTranslation::CODE => null,
                EditorialReferenceTranslation::CODE => null,
                ArticleIndexTranslation::CODE => null,
                ArticleAuthor::CODE => $command = new ArticleAuthorCommand($line),
                BindingType::CODE => $command = new BindingTypeCommand($line),
                Language::CODE => $command = new LanguageCommand($line),
                Stock::CODE => $command = new StockCommand($line),
                CenterStock::CODE => $command = new StockByCenterCommand($line),
                Status::CODE => $command = new StatusCommand($line),
                Author::CODE => $command = new AuthorCommand($line),
                Country::CODE => null,
                ArticleBatchHeader::CODE => null,
                ArticleBatchLine::CODE => null,
                ArticleGpsr::CODE => null,
                Type::CODE => $command = new TypeCommand($line),
                Classification::CODE => $command = new ClassificationCommand($line),
                ArticleTranslation::CODE => null,
                Province::CODE => null,
                AuthorBiography::CODE => $command = new AuthorBiographyCommand($line),
                Coedition::CODE => null,
                ArticleResource::CODE => null,
                Warning::CODE => null,
                default => $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unknown line code: %s', $line->getCode()),
                ],
            };

            if ($command !== null) {
                $command();

                if ($command->isBatch) {
                    $batchCommands->push($command);
                }

                if ($command->getLog() !== []) {
                    $log[] = $command->getLog();
                }
            }
        }

        // Accumulate batch-relation commands into GeslibInterFileBatchLine
        // (phase 4 — processed by ProcessGeslibInterFileBatchLine at the end)
        foreach ($batchCommands->groupBy('type') as $lineType => $lineTypeCommands) {
            foreach ($lineTypeCommands->groupBy('articleId') as $articleId => $articleCommands) {
                $fileBatchLine = $this->geslibInterFile->batchLines()->firstOrCreate([
                    'article_id' => $articleId,
                    'line_type' => $lineType,
                ]);

                if ($fileBatchLine->data === null) {
                    $fileBatchLine->data = [];
                }

                $fileBatchLine->update([
                    'data' => array_merge(
                        $fileBatchLine->data,
                        $articleCommands->map(function ($command) {
                            $data = json_decode(json_encode($command), true);
                            unset($data['type'], $data['articleId']);

                            return $data;
                        })->toArray(),
                    ),
                ]);
            }
        }

        $log[] = [
            'level' => CommandContract::LEVEL_INFO,
            'message' => sprintf(
                'Phase %d chunk processed: %d lines',
                $this->phase,
                count($this->lines),
            ),
        ];

        $this->geslibInterFile->update([
            'log' => $log,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME)->forceRelease();

        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_FAILED,
            'log' => array_merge($this->geslibInterFile->log ?? [], [
                [
                    'level' => CommandContract::LEVEL_ERROR,
                    'message' => $exception->getMessage(),
                ],
            ]),
            'finished_at' => Carbon::now(),
        ]);
    }
}
