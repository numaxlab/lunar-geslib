<?php

namespace NumaxLab\Lunar\Geslib\Console\Commands\Geslib;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

class Import extends Command
{
    protected $signature = 'lunar:geslib:import';

    protected $description = 'Process Geslib INTER pending files';

    public function handle(): void
    {
        $this->components->info('Getting all Geslib INTER files...');

        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $files = collect($storage->files(config('lunar.geslib.inter_files_path')))
            ->filter(fn($file) => strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip')
            ->sortBy(fn($file) => File::lastModified($storage->path($file)))
            ->values()
            ->all();

        $lastInterFile = GeslibInterFile::orderBy('received_at', 'desc')->first();

        $count = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            $fileLastModified = Carbon::createFromTimestamp(
                File::lastModified($storage->path($file)),
                config('app.timezone'),
            );

            if ($lastInterFile && $lastInterFile->received_at->greaterThan($fileLastModified)) {
                continue;
            }

            if (
                $lastInterFile &&
                $lastInterFile->received_at->equalTo($fileLastModified) &&
                $lastInterFile->name === $filename
            ) {
                continue;
            }

            $interFile = new GeslibInterFile([
                'name' => $filename,
                'received_at' => $fileLastModified,
                'status' => GeslibInterFile::STATUS_PENDING,
            ]);

            $interFile->save();

            ProcessGeslibInterFile::dispatch($interFile);

            $this->components->info(sprintf('Dispatching %s...', $filename));

            $count++;
        }

        $this->components->info(sprintf('%s Geslib INTER files have been dispatched for processing.', $count));
    }
}
