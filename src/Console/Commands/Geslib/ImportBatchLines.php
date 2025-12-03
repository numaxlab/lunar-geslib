<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Console\Commands\Geslib;

use Illuminate\Console\Command;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFileBatchLine;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;

class ImportBatchLines extends Command
{
    protected $signature = 'lunar:geslib:import:batch-lines';

    protected $description = 'Process Geslib INTER batch lines';

    public function handle(): int
    {
        $interFileId = $this->ask('Geslib INTER file ID');

        $geslibInterFile = GeslibInterFile::find($interFileId);

        $batchLine = GeslibInterFileBatchLine::whereHas(
            'geslibInterFile',
            function ($query) use ($geslibInterFile): void {
                $query->where('id', $geslibInterFile->id);
            },
        )->orderBy('created_at')->first();

        if ($batchLine) {
            ProcessGeslibInterFileBatchLine::dispatch($geslibInterFile, $batchLine);
        }

        return self::SUCCESS;
    }
}
