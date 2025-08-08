<?php

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Geslib\Lines\ArticleIndex;
use NumaxLab\Geslib\Lines\ArticleIndexTranslation;
use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Geslib\Lines\Author;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Geslib\Lines\BindingType;
use NumaxLab\Geslib\Lines\BookshopReference;
use NumaxLab\Geslib\Lines\BookshopReferenceTranslation;
use NumaxLab\Geslib\Lines\Classification;
use NumaxLab\Geslib\Lines\Collection;
use NumaxLab\Geslib\Lines\EBook;
use NumaxLab\Geslib\Lines\Editorial;
use NumaxLab\Geslib\Lines\EditorialReference;
use NumaxLab\Geslib\Lines\EditorialReferenceTranslation;
use NumaxLab\Geslib\Lines\Ibic;
use NumaxLab\Geslib\Lines\Language;
use NumaxLab\Geslib\Lines\PressPublication;
use NumaxLab\Geslib\Lines\RecordLabel;
use NumaxLab\Geslib\Lines\Status;
use NumaxLab\Geslib\Lines\Stock;
use NumaxLab\Geslib\Lines\Topic;
use NumaxLab\Geslib\Lines\Type;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleAuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleIndexCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleTopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorBiographyCommand;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleAuthorRelation;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleIbicRelation;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleTopicRelation;
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
use NumaxLab\Lunar\Geslib\InterCommands\StockCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;
use RuntimeException;
use Throwable;
use ZipArchive;

class ProcessGeslibInterFile implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public const CACHE_LOCK_NAME = 'geslib-inter-files';

    public $tries = 2;

    public $uniqueFor = 900; // 15 minutes

    public function __construct(
        protected GeslibInterFile $geslibInterFile,
        protected int $startLine = 0,
        protected int $chunkSize = 1000,
    ) {
        $this->onQueue('geslib-inter-files');
    }

    public function uniqueId(): string
    {
        return $this->geslibInterFile->id . '-' . $this->startLine . '-' . $this->chunkSize;
    }

    public function handle(): void
    {
        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $extractedFilePath = config('lunar.geslib.inter_files_path') . '/' . str_replace(
                '.zip',
                '',
                $this->geslibInterFile->name,
            );

        if (!$storage->exists($extractedFilePath)) {
            $this->extractZipFile($storage);
        }

        $geslibFile = GeslibFile::parse($storage->get($extractedFilePath));
        $totalLines = count($geslibFile->lines());

        if ($this->geslibInterFile->status === GeslibInterFile::STATUS_PENDING) {
            $this->geslibInterFile->update([
                'status' => GeslibInterFile::STATUS_PROCESSING,
                'started_at' => Carbon::now(),
                'total_lines' => $totalLines,
            ]);
        }

        $fileFinished = false;
        $endLine = $this->startLine + $this->chunkSize;
        if ($endLine > $totalLines) {
            $fileFinished = true;
            $endLine = $totalLines;
        }

        $log = is_array($this->geslibInterFile->log) ? $this->geslibInterFile->log : [];
        // Clear the log if it exceeds a certain size to prevent memory issues
        if (count($log) > 2000) {
            $log = [];
        }

        $batchCommands = collect();

        for ($i = $this->startLine; $i < $endLine; $i++) {
            $line = $geslibFile->lines()[$i];
            $command = null;

            match ($line->getCode()) {
                Editorial::CODE => $command = new EditorialCommand($line),
                RecordLabel::CODE => $command = new RecordLabelCommand($line),
                // '1P' => null,
                PressPublication::CODE => $command = new PressPublicationCommand($line),
                Collection::CODE => $command = new CollectionCommand($line),
                Topic::CODE => $command = new TopicCommand($line),
                Article::CODE => $command = new ArticleCommand($line),
                EBook::CODE => $command = new ArticleCommand($line, true),
                // EbookInfo::CODE => null,
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
                // Preposition::CODE => null,
                Stock::CODE => $command = new StockCommand($line),
                // 'B2' => null,
                Status::CODE => $command = new StatusCommand($line),
                // 'CLI' => null,
                Author::CODE => $command = new AuthorCommand($line),
                // 'IPC' => null,
                // 'P' => null,
                // 'PROCEN' => null,
                // 'PC' => null,
                // 'VTA' => null,
                // Country::CODE => null,
                // 'CLOTE' => null,
                // 'LLOTE' => null,
                Type::CODE => $command = new TypeCommand($line),
                Classification::CODE => $command = new ClassificationCommand($line),
                // 'ATRA' => null,
                // 'CA' => null,
                // 'CLOTCLI' => null,
                // 'LLOTCLI' => null,
                // 'PROFES' => null,
                // Province::CODE => null,
                // 'CAGRDTV' => null,
                // 'LAGRDTV' => null,
                // 'CLIDTO' => null,
                // 'CDG' => null,
                // 'LDG' => null,
                AuthorBiography::CODE => $command = new AuthorBiographyCommand($line),
                // 'EMBALA' => null,
                // 'PACK' => null,
                // 'TRACKS' => null,
                // 'ATRIBU' => null,
                // 'ARTATR' => null,
                // 'ARTREC' => null,
                // 'CDP' => null,
                // 'LDP' => null,
                default => $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unknown line code: %s', $line->getCode()),
                ],
            };

            if ($command !== null) {
                $command();

                if ($command->isBatch()) {
                    $batchCommands->push($command);
                }

                if (count($command->getLog()) > 0) {
                    $log[] = $command->getLog();
                }
            }
        }

        $log[] = [
            'level' => CommandContract::LEVEL_INFO,
            'message' => sprintf(
                'Processed lines %s to %s',
                $this->startLine + 1,
                $endLine,
            ),
        ];

        $this->geslibInterFile->update([
            GeslibInterFile::STATUS_PROCESSING,
            'processed_lines' => $endLine,
            'log' => $log,
            'finished_at' => null,
        ]);

        foreach ($batchCommands->groupBy('type') as $lineType => $lineTypCommands) {
            foreach ($lineTypCommands->groupBy('articleId') as $articleId => $articleCommands) {
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

        if (!$fileFinished) {
            self::dispatch($this->geslibInterFile, $endLine, $this->chunkSize);
            return;
        }

        unset($geslibFile, $line, $command, $batchCommands);

        $log = $this->processBatchLines($log);

        Cache::lock(self::CACHE_LOCK_NAME)->forceRelease();

        $this->geslibInterFile->update([
            'status' => $this->getStatusFromLog($this->geslibInterFile->log),
            'finished_at' => Carbon::now(),
            'log' => $log,
        ]);

        $this->geslibInterFile->batchLines()->delete();

        $storage->delete($extractedFilePath);
    }

    protected function extractZipFile(Filesystem $storage): void
    {
        $zip = new ZipArchive();

        $zipFilePath = $storage->path(config('lunar.geslib.inter_files_path') . '/' . $this->geslibInterFile->name);

        if ($zip->open($zipFilePath) === true) {
            $zip->extractTo($storage->path(config('lunar.geslib.inter_files_path')));

            $zip->close();
        } else {
            throw new RuntimeException('Unable to open zip file: ' . $zipFilePath);
        }
    }

    protected function processBatchLines(array $log): array
    {
        $batchLines = GeslibInterFileBatchLine::whereHas('geslibInterFile', function ($query) {
            $query->where('id', $this->geslibInterFile->id);
        })->get();

        $log[] = [
            'level' => CommandContract::LEVEL_INFO,
            'message' => sprintf(
                'Processing %s batch lines',
                $batchLines->count(),
            ),
        ];

        foreach ($batchLines as $batchLine) {
            $command = null;

            match ($batchLine->line_type) {
                ArticleAuthor::CODE => $command = new ArticleAuthorRelation($batchLine->article_id, $batchLine->data),
                ArticleTopic::CODE => $command = new ArticleTopicRelation($batchLine->article_id, $batchLine->data),
                Ibic::CODE => $command = new ArticleIbicRelation($batchLine->article_id, $batchLine->data),
                default => $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unexpected batch command for line type: %s', $batchLine->line_type),
                ],
            };

            $command();
        }

        return $log;
    }

    private function getStatusFromLog(array $log): string
    {
        foreach ($log as $line) {
            if ($line['level'] === CommandContract::LEVEL_ERROR) {
                return GeslibInterFile::STATUS_FAILED;
            }
        }

        foreach ($log as $line) {
            if ($line['level'] === CommandContract::LEVEL_WARNING) {
                return GeslibInterFile::STATUS_WARNING;
            }
        }

        return GeslibInterFile::STATUS_SUCCESS;
    }

    public function failed(Throwable $exception): void
    {
        Cache::lock(self::CACHE_LOCK_NAME)->forceRelease();

        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_FAILED,
            'log' => array_merge($this->geslibInterFile->log, [
                [
                    'level' => CommandContract::LEVEL_ERROR,
                    'message' => $exception->getMessage(),
                ],
            ]),
            'finished_at' => Carbon::now(),
        ]);

        // Check if notifications are enabled and mail_to is configured
        /*if (!config('lunar.geslib.notifications.enabled') || !config('lunar.geslib.notifications.mail_to')) {
            return;
        }

        $mailTo = config('lunar.geslib.notifications.mail_to');
        $throttlePeriodMinutes = config('lunar.geslib.notifications.throttle_period_minutes', 60);
        $cacheKey = 'geslib_notification_import_failed_' . $this->geslibInterFile->id;

        // Throttle notifications
        if (Cache::has($cacheKey)) {
            return; // Already notified recently
        }

        // Send notification
        NotificationFacade::route('mail', $mailTo)
            ->notify(new GeslibFileImportFailed($this->geslibInterFile, $exception->getMessage()));

        // Cache that notification has been sent
        Cache::put($cacheKey, true, now()->addMinutes($throttlePeriodMinutes));*/
    }
}
