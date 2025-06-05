<?php

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
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
use NumaxLab\Lunar\Geslib\Geslib\ArticleAuthorCommand;
use NumaxLab\Lunar\Geslib\Geslib\ArticleCommand;
use NumaxLab\Lunar\Geslib\Geslib\ArticleIndexCommand;
use NumaxLab\Lunar\Geslib\Geslib\ArticleTopicCommand;
use NumaxLab\Lunar\Geslib\Geslib\AuthorCommand;
use NumaxLab\Lunar\Geslib\Geslib\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\Geslib\BookshopReferenceCommand;
use NumaxLab\Lunar\Geslib\Geslib\ClassificationCommand;
use NumaxLab\Lunar\Geslib\Geslib\CollectionCommand;
use NumaxLab\Lunar\Geslib\Geslib\EditorialCommand;
use NumaxLab\Lunar\Geslib\Geslib\EditorialReferenceCommand;
use NumaxLab\Lunar\Geslib\Geslib\LanguageCommand;
use NumaxLab\Lunar\Geslib\Geslib\PressPublicationCommand;
use NumaxLab\Lunar\Geslib\Geslib\RecordLabelCommand;
use NumaxLab\Lunar\Geslib\Geslib\StockCommand;
use NumaxLab\Lunar\Geslib\Geslib\TopicCommand;
use NumaxLab\Lunar\Geslib\Geslib\TypeCommand;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Notifications\GeslibFileImportFailed; // Added
use RuntimeException;
use Illuminate\Support\Facades\Notification as NotificationFacade; // Added
use Illuminate\Support\Facades\Cache; // Added
use Throwable; // Added

class ProcessGeslibInterFile implements ShouldQueue
{
    use Queueable;

    // Default number of times the job may be attempted.
    public $tries = 3;

    // Default timeout for the job.
    public $timeout = 120; // 2 minutes

    public function __construct(
        public GeslibInterFile $geslibInterFile,
    ) {}

    public function handle(): void
    {
        $this->geslibInterFile->update([
            'started_at' => Carbon::now(),
        ]);

        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $geslibFile = GeslibFile::parse(
            $storage->get(config('lunar.geslib.inter_files_path') . '/' . $this->geslibInterFile->name),
        );

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
                Ibic::CODE => null,
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
                Status::CODE => null,
                'CLI' => null,
                Author::CODE => $command = new AuthorCommand(),
                'IPC' => null,
                'P' => null,
                'PROCEN' => null,
                'PC' => null,
                'VTA' => null,
                Country::CODE => null,
                'CLOTE' => null,
                'LLOTE' => null,
                Type::CODE => $command = new TypeCommand(),
                Classification::CODE => $command = new ClassificationCommand(),
                'ATRA' => null,
                'CA' => null,
                'CLOTCLI' => null,
                'LLOTCLI' => null,
                'PROFES' => null,
                Province::CODE => null,
                'CAGRDTV' => null,
                'LAGRDTV' => null,
                'CLIDTO' => null,
                'CDG' => null,
                'LDG' => null,
                AuthorBiography::CODE => null,
                'EMBALA' => null,
                'PACK' => null,
                'TRACKS' => null,
                'ATRIBU' => null,
                'ARTATR' => null,
                'ARTREC' => null,
                'CDP' => null,
                'LDP' => null,
                default => throw new RuntimeException(sprintf('Unknown line type: %s', $line->getCode())),
            };

            if ($command !== null) {
                if (!$command->isBatch()) {
                    $command($line);
                } else {
                    $batchCommands->push($command);
                }
            }
        }

        $this->geslibInterFile->update([
            'finished_at' => Carbon::now(),
        ]);

        // If successful, update status and clear any previous error notes
        $this->geslibInterFile->update([
            'status' => 'processed', // Assuming 'processed' is the success status
            'notes' => null,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // Update model status to 'error' and store the error message
        $this->geslibInterFile->update([
            'status' => 'error',
            'notes' => $exception->getMessage(),
            'finished_at' => Carbon::now(), // Also mark as finished if it failed
        ]);

        // Check if notifications are enabled and mail_to is configured
        if (!config('lunar.geslib.notifications.enabled') || !config('lunar.geslib.notifications.mail_to')) {
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
        Cache::put($cacheKey, true, now()->addMinutes($throttlePeriodMinutes));
    }
}
