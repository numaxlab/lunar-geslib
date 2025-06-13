<?php

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
use NumaxLab\Geslib\Lines\Country;
use NumaxLab\Geslib\Lines\EBook;
use NumaxLab\Geslib\Lines\EbookInfo;
use NumaxLab\Geslib\Lines\Editorial;
use NumaxLab\Geslib\Lines\EditorialReference;
use NumaxLab\Geslib\Lines\EditorialReferenceTranslation;
use NumaxLab\Geslib\Lines\Ibic;
use NumaxLab\Geslib\Lines\Language;
use NumaxLab\Geslib\Lines\Preposition;
use NumaxLab\Geslib\Lines\PressPublication;
use NumaxLab\Geslib\Lines\Province;
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
use Throwable;

class ProcessGeslibInterFile implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public $tries = 2;

    public $uniqueFor = 3600;

    public function __construct(
        public GeslibInterFile $geslibInterFile,
    ) {
        $this->onQueue('geslib-inter-files');
    }

    public function uniqueId(): string
    {
        return $this->geslibInterFile->id;
    }

    public function handle(): void
    {
        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_PROCESSING,
            'started_at' => Carbon::now(),
        ]);

        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $geslibFile = GeslibFile::parse(
            $storage->get(config('lunar.geslib.inter_files_path') . '/' . $this->geslibInterFile->name),
        );

        $log = [];

        $batchCommands = collect();

        foreach ($geslibFile->lines() as $line) {
            $command = null;

            match ($line->getCode()) {
                Editorial::CODE => $command = new EditorialCommand(),
                RecordLabel::CODE => $command = new RecordLabelCommand(),
                '1P' => null,
                PressPublication::CODE => $command = new PressPublicationCommand(),
                Collection::CODE => $command = new CollectionCommand(),
                Topic::CODE => $command = new TopicCommand(),
                Article::CODE => $command = new ArticleCommand(),
                EBook::CODE => $command = new ArticleCommand(true),
                EbookInfo::CODE => null,
                ArticleTopic::CODE => $command = new ArticleTopicCommand(),
                Ibic::CODE => $command = new IbicCommand(),
                BookshopReference::CODE => $command = new BookshopReferenceCommand(),
                EditorialReference::CODE => $command = new EditorialReferenceCommand(),
                ArticleIndex::CODE => $command = new ArticleIndexCommand(),
                BookshopReferenceTranslation::CODE => null,
                EditorialReferenceTranslation::CODE => null,
                ArticleIndexTranslation::CODE => null,
                ArticleAuthor::CODE => $command = new ArticleAuthorCommand(),
                BindingType::CODE => $command = new BindingTypeCommand(),
                Language::CODE => $command = new LanguageCommand(),
                Preposition::CODE => null,
                Stock::CODE => $command = new StockCommand(),
                'B2' => null,
                Status::CODE => $command = new StatusCommand(),
                'CLI' => null,
                Author::CODE => $command = new AuthorCommand(),
                'IPC' => null,
                'P' => null,
                'PROCEN' => null,
                'PC' => null,
                'VTA' => null,
                Country::CODE => null,
                'CLOTE' => null, // TODO: Implement CLOTE command
                'LLOTE' => null, // TODO: Implement LLOTE command
                Type::CODE => $command = new TypeCommand(),
                Classification::CODE => $command = new ClassificationCommand(),
                'ATRA' => null,
                'CA' => null,
                'CLOTCLI' => null, // TODO: Implement CLOTCLI command
                'LLOTCLI' => null, // TODO: Implement LLOTCLI command
                'PROFES' => null,
                Province::CODE => null,
                'CAGRDTV' => null,
                'LAGRDTV' => null,
                'CLIDTO' => null,
                'CDG' => null,
                'LDG' => null,
                AuthorBiography::CODE => $command = new AuthorBiographyCommand(),
                'EMBALA' => null,
                'PACK' => null,
                'TRACKS' => null,
                'ATRIBU' => null,
                'ARTATR' => null,
                'ARTREC' => null,
                'CDP' => null,
                'LDP' => null,
                default => $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unknown line code: %s', $line->getCode()),
                ],
            };

            if ($command !== null) {
                $command($line);

                if ($command->isBatch()) {
                    $batchCommands->push($command);
                }

                $log = array_merge($log, $command->getLog());
            }
        }

        foreach ($batchCommands->groupBy('type') as $lineType => $commands) {
            $batchCommand = null;

            match ((string)$lineType) {
                ArticleAuthor::CODE => $batchCommand = new ArticleAuthorRelation($commands->groupBy('articleId')),
                ArticleTopic::CODE => $batchCommand = new ArticleTopicRelation($commands->groupBy('articleId')),
                Ibic::CODE => $batchCommand = new ArticleIbicRelation($commands->groupBy('articleId')),
                default => $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unexpected batch command for line type: %s', $lineType),
                ],
            };

            $batchCommand();

            $log = array_merge($log, $batchCommand->getLog());
        }

        $this->geslibInterFile->update([
            'status' => $this->getStatusFromLog($log),
            'finished_at' => Carbon::now(),
            'log' => $log,
        ]);
    }

    private function getStatusFromLog(array $log): string
    {
        foreach ($log as $line) {
            if ($line['level'] === CommandContract::LEVEL_ERROR) {
                return GeslibInterFile::STATUS_FAILED;
            }
            if ($line['level'] === CommandContract::LEVEL_WARNING) {
                return GeslibInterFile::STATUS_WARNING;
            }
        }

        return GeslibInterFile::STATUS_SUCCESS;
    }

    public function failed(Throwable $exception): void
    {
        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_FAILED,
            'log' => [
                'error' => $exception->getMessage(),
            ],
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
