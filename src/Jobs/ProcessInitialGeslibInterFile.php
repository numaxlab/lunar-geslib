<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use RuntimeException;
use Throwable;
use ZipArchive;

class ProcessInitialGeslibInterFile implements ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public function __construct(
        protected GeslibInterFile $geslibInterFile,
    ) {
        $this->onQueue('geslib-inter-files');
    }

    public function handle(): void
    {
        if (! $this->geslibInterFile->usesInitialStrategy()) {
            throw new RuntimeException(
                'ProcessInitialGeslibInterFile only accepts initial_load files.',
            );
        }

        $storage = Storage::disk(config('lunar.geslib.inter_files_disk'));

        $extractedFilePath = config('lunar.geslib.inter_files_path').'/'.str_replace(
            '.zip',
            '',
            $this->geslibInterFile->name,
        );

        if (! $storage->exists($extractedFilePath)) {
            $this->extractZipFile($storage);
        }

        $geslibFile = $this->parseGeslibContent($storage->get($extractedFilePath));
        $lines = $geslibFile->lines();
        $totalLines = count($lines);

        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_PROCESSING,
            'started_at' => Carbon::now(),
            'total_lines' => $totalLines,
        ]);

        $phases = GeslibInitialPhasePartitioner::partition($lines);

        // Build a chain: phase 1 → phase 2 → phase 3 → batch relations
        $chain = [];

        foreach ([1, 2, 3, 4] as $phase) {
            if (! empty($phases[$phase])) {
                $chain[] = new ProcessInitialGeslibPhaseChunk(
                    $this->geslibInterFile,
                    $phase,
                    $phases[$phase],
                );
            }
        }

        // Append batch-relation processor at the end of the chain
        $batchLineJob = new ProcessInitialGeslibBatchRelations($this->geslibInterFile);

        if (! empty($chain)) {
            Bus::chain([...$chain, $batchLineJob])->dispatch();
        } else {
            $batchLineJob->handle();
        }

        $storage->delete($extractedFilePath);
    }

    protected function parseGeslibContent(string $content): GeslibFile
    {
        return GeslibFile::parse($content);
    }

    protected function extractZipFile(Filesystem $storage): void
    {
        $zip = new ZipArchive;

        $zipFilePath = $storage->path(config('lunar.geslib.inter_files_path').'/'.$this->geslibInterFile->name);

        if ($zip->open($zipFilePath) === true) {
            $zip->extractTo($storage->path(config('lunar.geslib.inter_files_path')));
            $zip->close();

            return;
        }

        // The file has a .zip extension but contains plain-text INTER content.
        // Copy the raw content to the expected extracted path so the rest of the
        // pipeline can proceed unchanged.
        $rawContent = $storage->get(config('lunar.geslib.inter_files_path').'/'.$this->geslibInterFile->name);

        if ($rawContent === null) {
            throw new RuntimeException('Unable to read file: '.$zipFilePath);
        }

        $extractedFilePath = config('lunar.geslib.inter_files_path').'/'.str_replace(
            '.zip',
            '',
            $this->geslibInterFile->name,
        );

        $storage->put($extractedFilePath, $rawContent);
    }

    public function failed(Throwable $exception): void
    {
        $this->geslibInterFile->update([
            'status' => GeslibInterFile::STATUS_FAILED,
            'log' => array_merge($this->geslibInterFile->log ?? [], [
                [
                    'level' => CommandContract::LEVEL_ERROR,
                    'message' => $exception->getMessage(),
                ],
            ]),
            'finished_at' => Carbon::now(),
        ]);
    }
}
