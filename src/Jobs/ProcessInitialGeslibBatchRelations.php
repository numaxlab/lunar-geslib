<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;

class ProcessInitialGeslibBatchRelations implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly GeslibInterFile $file) {}

    public function handle(): void
    {
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
}
