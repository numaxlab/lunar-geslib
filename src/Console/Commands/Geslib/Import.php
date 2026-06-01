<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Console\Commands\Geslib;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

class Import extends Command
{
    protected $signature = 'lunar:geslib:import';

    protected $description = 'Process Geslib INTER pending files';

    public function handle(): int
    {
        $this->components->info('Getting all Geslib INTER files...');

        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $files = collect($storage->files(config('lunar.geslib.inter_files_path')))
            ->filter(fn ($file): bool => strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip')
            ->sortBy(fn ($file) => File::lastModified($storage->path($file)))
            ->values()
            ->all();

        $lastInterFile = GeslibInterFile::orderBy('received_at', 'desc')->first();

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

            $lock = Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME, 60 * 60 * 12); // 12 hours

            if (! $lock->get()) {
                $this->components->info(
                    'The Geslib INTER files import worker is busy. Waiting until next execution...',
                );

                return self::SUCCESS;
            }

            $interFile = new GeslibInterFile([
                'name' => $filename,
                'received_at' => $fileLastModified,
                'status' => GeslibInterFile::STATUS_PENDING,
                'file_type' => $this->detectFileType($storage->get($file)),
            ]);

            $interFile->save();

            if ($interFile->usesInitialStrategy()) {
                ProcessInitialGeslibInterFile::dispatch($interFile);
            } else {
                ProcessGeslibInterFile::dispatch($interFile);
            }

            $this->components->info(sprintf('Dispatching %s...', $filename));

            break;
        }

        return self::SUCCESS;
    }

    protected function detectFileType(string $rawContent): string
    {
        // Real ZIP archives start with the PK magic bytes; plain-text pseudo-zips do not.
        $content = str_starts_with($rawContent, "PK\x03\x04")
            ? $this->readFirstFileFromZip($rawContent)
            : $rawContent;

        $firstLine = strtok($content, "\n");

        return str_starts_with((string) $firstLine, 'I|')
            ? GeslibInterFile::FILE_TYPE_INITIAL_LOAD
            : GeslibInterFile::FILE_TYPE_NORMAL_UPDATE;
    }

    /**
     * Write raw ZIP bytes to a temp file, open it with ZipArchive, and return
     * the beginning of the first contained file — enough to inspect the header line.
     */
    protected function readFirstFileFromZip(string $rawContent): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'geslib_');

        try {
            file_put_contents($tmp, $rawContent);

            $zip = new \ZipArchive();

            if ($zip->open($tmp) !== true || $zip->numFiles === 0) {
                return '';
            }

            // Read just the first 256 bytes — enough to check the header line.
            $content = (string) $zip->getFromIndex(0, 256);
            $zip->close();

            return $content;
        } finally {
            @unlink($tmp);
        }
    }
}
