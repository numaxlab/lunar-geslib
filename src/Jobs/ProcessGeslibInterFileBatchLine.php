<?php

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

class ProcessGeslibInterFileBatchLine implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public $uniqueFor = 900; // 15 minutes

    public function __construct(
        protected GeslibInterFile $geslibInterFile,
        protected GeslibInterFileBatchLine $batchLine,
    ) {
        $this->onQueue('geslib-inter-files');
    }

    public function uniqueId(): string
    {
        return $this->geslibInterFile->id.'-batch-'.$this->batchLine->id;
    }

    public function handle(): void
    {
        $batchLines = GeslibInterFileBatchLine::whereHas('geslibInterFile', function ($query): void {
            $query->where('id', $this->geslibInterFile->id);
        })->orderBy('created_at')->take(5)->get();

        $log = is_array($this->geslibInterFile->log) ? $this->geslibInterFile->log : [];
        // Clear the log if it exceeds a certain size to prevent memory issues
        if (count($log) > 2000) {
            $log = [];
        }

        $log[] = [
            'level' => CommandContract::LEVEL_INFO,
            'message' => sprintf(
                'Processing batch line %s',
                $this->batchLine->id,
            ),
        ];

        $command = null;

        match ($this->batchLine->line_type) {
            ArticleAuthor::CODE => $command = new ArticleAuthorRelation(
                $this->batchLine->article_id,
                $this->batchLine->data,
            ),
            ArticleTopic::CODE => $command = new ArticleTopicRelation(
                $this->batchLine->article_id,
                $this->batchLine->data,
            ),
            Ibic::CODE => $command = new ArticleIbicRelation(
                $this->batchLine->article_id,
                $this->batchLine->data,
            ),
            default => $log[] = [
                'level' => CommandContract::LEVEL_WARNING,
                'message' => sprintf('Unexpected batch command for line type: %s', $this->batchLine->line_type),
            ],
        };

        if ($command !== null) {
            $command();
        }

        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_PROCESSING,
            'log' => $log,
        ]);

        $this->batchLine->delete();

        if ($batchLines->count() > 1) {
            $nextBatchLine = $batchLines->first(fn($line) => $line->id !== $this->batchLine->id);

            if ($nextBatchLine) {
                self::dispatch($this->geslibInterFile, $nextBatchLine);

                return;
            }
        }

        $this->geslibInterFile->update([
            'status' => $this->getStatusFromLog($this->geslibInterFile->log),
            'finished_at' => Carbon::now(),
        ]);
    }

    private function getStatusFromLog(array $log): string
    {
        if (array_any($log, fn($line) => $line['level'] === CommandContract::LEVEL_ERROR)) {
            return GeslibInterFile::STATUS_FAILED;
        }

        if (array_any($log, fn($line) => $line['level'] === CommandContract::LEVEL_WARNING)) {
            return GeslibInterFile::STATUS_WARNING;
        }

        return GeslibInterFile::STATUS_SUCCESS;
    }
}
