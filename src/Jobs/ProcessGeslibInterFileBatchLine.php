<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Geslib\Lines\Ibic;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleAuthorRelation;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleIbicRelation;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleTopicRelation;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;
use Throwable;

class ProcessGeslibInterFileBatchLine implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public $uniqueFor = 900; // 15 minutes

    public function __construct(
        protected GeslibInterFile          $geslibInterFile,
        protected GeslibInterFileBatchLine $batchLine,
    )
    {
        $this->onQueue('geslib-inter-files');
    }

    public function uniqueId(): string
    {
        return $this->geslibInterFile->id . '-batch-' . $this->batchLine->id;
    }

    public function handle(): void
    {
        $batchLines = GeslibInterFileBatchLine::whereHas('geslibInterFile', function ($query): void {
            $query->where('id', $this->geslibInterFile->id);
        })->orderBy('created_at')->take(50)->get();

        $log = is_array($this->geslibInterFile->log) ? $this->geslibInterFile->log : [];
        // Clear the log if it exceeds a certain size to prevent memory issues
        if (count($log) > 2000) {
            $log = [];
        }

        foreach ($batchLines as $batchLine) {
            $log[] = [
                'level' => CommandContract::LEVEL_INFO,
                'message' => sprintf('Processing batch line %s', $batchLine->id),
            ];

            $command = match ($batchLine->line_type) {
                ArticleAuthor::CODE => new ArticleAuthorRelation($batchLine->article_id, $batchLine->data),
                ArticleTopic::CODE => new ArticleTopicRelation($batchLine->article_id, $batchLine->data),
                Ibic::CODE => new ArticleIbicRelation($batchLine->article_id, $batchLine->data),
                default => null,
            };

            if ($command !== null) {
                $command();

                if ($command->getLog() !== []) {
                    $log[] = $command->getLog();
                }
            } else {
                $log[] = [
                    'level' => CommandContract::LEVEL_WARNING,
                    'message' => sprintf('Unexpected batch command for line type: %s', $batchLine->line_type),
                ];
            }

            $batchLine->delete();
        }

        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_PROCESSING,
            'log' => $log,
        ]);

        $nextBatchLine = GeslibInterFileBatchLine::whereHas('geslibInterFile', function ($query): void {
            $query->where('id', $this->geslibInterFile->id);
        })->orderBy('created_at')->first();

        if ($nextBatchLine) {
            self::dispatch($this->geslibInterFile, $nextBatchLine);

            return;
        }

        $this->geslibInterFile->update([
            'status' => self::getStatusFromLog($this->geslibInterFile->log),
            'finished_at' => Carbon::now(),
        ]);
    }

    public static function getStatusFromLog(array $log): string
    {
        if (array_any($log, fn($line): bool => $line['level'] === CommandContract::LEVEL_ERROR)) {
            return GeslibInterFile::STATUS_FAILED;
        }

        if (array_any($log, fn($line): bool => $line['level'] === CommandContract::LEVEL_WARNING)) {
            return GeslibInterFile::STATUS_WARNING;
        }

        return GeslibInterFile::STATUS_SUCCESS;
    }

    public function failed(Throwable $exception): void
    {
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
    }
}
