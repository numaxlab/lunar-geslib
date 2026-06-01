<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;
use Throwable;

class ProcessInitialGeslibBatchRelations implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly GeslibInterFile $file) {}

    public function handle(): void
    {
        Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME)->forceRelease();

        $batchLine = GeslibInterFileBatchLine::whereHas(
            'geslibInterFile',
            fn ($q) => $q->where('id', $this->file->id),
        )->orderBy('created_at')->first();

        if ($batchLine) {
            ProcessGeslibInterFileBatchLine::dispatch($this->file, $batchLine);
        } else {
            $this->file->update([
                'status' => ProcessGeslibInterFileBatchLine::getStatusFromLog(
                    $this->file->log ?? [],
                ),
                'finished_at' => Carbon::now(),
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME)->forceRelease();

        $this->file->update([
            'status' => GeslibInterFile::STATUS_FAILED,
            'log' => array_merge($this->file->log ?? [], [
                [
                    'level' => CommandContract::LEVEL_ERROR,
                    'message' => $exception->getMessage(),
                ],
            ]),
            'finished_at' => Carbon::now(),
        ]);
    }
}
