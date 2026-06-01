<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Geslib\Lines\Province;
use NumaxLab\Geslib\Lines\StationeryCategory;
use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFileBatchLine;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;

/**
 * Creates fake line objects with a fixed code.
 *
 * These are plain objects — NOT LineInterface implementors — because
 * ProcessGeslibInterFile only calls getCode() on them at runtime.
 * They are injected via Reflection directly into GeslibFile's private $lines
 * property, bypassing addLine()'s LineInterface type check.
 *
 * @return array<object>
 */
function fakeLines(int $count, string $code): array
{
    return array_map(function () use ($code) {
        return new class($code)
        {
            public function __construct(private string $code) {}

            public function getCode(): string
            {
                return $this->code;
            }
        };
    }, range(1, $count));
}

/**
 * Builds a GeslibFile with the given fake lines injected via Reflection,
 * then returns a ProcessGeslibInterFile stub whose parseGeslibContent()
 * returns that file. No alias mocks — no process-level contamination.
 *
 * The stub also overrides dispatchNextChunk() to dispatch the concrete
 * ProcessGeslibInterFile class (not the anonymous stub), so that Bus::fake()
 * records the dispatch with the correct class name and Bus::assertDispatched
 * works correctly.
 */
function makeJobStub(
    GeslibInterFile $interFile,
    array $lines,
    int $startLine = 0,
    int $chunkSize = 10,
): ProcessGeslibInterFile {
    $geslibFile = new GeslibFile;

    // Inject fake lines directly into the private $lines property, bypassing
    // addLine()'s LineInterface type check. This is intentional: the job only
    // calls getCode() on each line at runtime, which our fakes implement.
    $ref = new ReflectionProperty(GeslibFile::class, 'lines');
    $ref->setValue($geslibFile, $lines);

    return new class($interFile, $startLine, $chunkSize, $geslibFile) extends ProcessGeslibInterFile
    {
        public function __construct(
            GeslibInterFile $geslibInterFile,
            int $startLine,
            int $chunkSize,
            private GeslibFile $stubbedFile,
        ) {
            parent::__construct($geslibInterFile, $startLine, $chunkSize);
        }

        protected function parseGeslibContent(string $content): GeslibFile
        {
            return $this->stubbedFile;
        }

        protected function dispatchNextChunk(int $endLine): void
        {
            // Dispatch the concrete parent class so Bus::fake records
            // ProcessGeslibInterFile::class (not this anonymous stub).
            ProcessGeslibInterFile::dispatch($this->geslibInterFile, $endLine, $this->chunkSize);
        }
    };
}

beforeEach(function (): void {
    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib-inter');

    Storage::fake('local');
    Cache::flush();
});

it('generates a unique id including file id, start and chunk', function (): void {
    $file = GeslibInterFile::factory()->create();

    $job = new ProcessGeslibInterFile($file, startLine: 10, chunkSize: 500);

    expect($job->uniqueId())->toBe($file->id.'-10-500');
});

it('processes a chunk and dispatches next job when not finished', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, fakeLines(5, StationeryCategory::CODE), startLine: 0, chunkSize: 3);
    $job->handle();

    $file->refresh();

    expect($file->status)
        ->toBe(GeslibInterFile::STATUS_PROCESSING)
        ->and($file->total_lines)->toBe(5)
        ->and($file->processed_lines)->toBe(3)
        ->and($file->started_at)->not()->toBeNull()
        ->and($file->finished_at)->toBeNull();

    Bus::assertDispatched(ProcessGeslibInterFile::class, function (ProcessGeslibInterFile $next) use ($file) {
        return $next->uniqueId() === $file->id.'-3-3';
    });
});

it('does not update status or started_at when file is already processing', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create([
        'status' => GeslibInterFile::STATUS_PROCESSING,
        'started_at' => now()->subHour(),
        'total_lines' => 5,
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $originalStartedAt = $file->started_at;

    $job = makeJobStub($file, fakeLines(5, StationeryCategory::CODE), startLine: 0, chunkSize: 3);
    $job->handle();

    $file->refresh();

    expect($file->started_at->timestamp)->toBe($originalStartedAt->timestamp);
});

it('extracts zip file when extracted file does not exist', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create(['name' => 'test.zip']);

    $zipPath = config('lunar.geslib.inter_files_path').'/'.$file->name;

    $zip = new ZipArchive;
    $fullZipPath = Storage::disk('local')->path($zipPath);
    Storage::disk('local')->makeDirectory(config('lunar.geslib.inter_files_path'));

    if ($zip->open($fullZipPath, ZipArchive::CREATE) === true) {
        $zip->addFromString('test', 'dummy content');
        $zip->close();
    }

    // Use stub with empty lines so the extracted content is not sent through the
    // real parser. This test only verifies the zip was extracted (directory exists).
    $job = makeJobStub($file, [], startLine: 0, chunkSize: 10);
    $job->handle();

    expect(Storage::disk('local')->exists(config('lunar.geslib.inter_files_path')))->toBeTrue();
});

it('throws exception when zip file cannot be read at all', function (): void {
    // File does not exist on disk at all — storage->get() returns null
    $file = GeslibInterFile::factory()->create(['name' => 'nonexistent.zip']);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Unable to read file');
});

it('extracts plain-text INTER content from a .zip file that is not a real archive', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create(['name' => 'INTER682.zip']);

    $zipPath = config('lunar.geslib.inter_files_path').'/INTER682.zip';
    // Plain-text content — not a real ZIP (mirrors the real client file format)
    Storage::disk('local')->put($zipPath, "F|some_update\nGP4|A|SKU-1\n");

    $job = makeJobStub($file, [], startLine: 0, chunkSize: 10);
    $job->handle();

    // Job must complete without throwing — file is deleted on success, so we
    // verify the DB status rather than storage existence.
    $file->refresh();
    expect($file->status)->not->toBe(GeslibInterFile::STATUS_FAILED);
});

it('finalizes when file finished and there are no batch lines', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, fakeLines(2, Province::CODE), startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->status)
        ->toBe(ProcessGeslibInterFileBatchLine::getStatusFromLog($file->log))
        ->and($file->finished_at)->not()->toBeNull()
        ->and(Storage::disk('local')->exists($extracted))->toBeFalse();

    Bus::assertNotDispatched(ProcessGeslibInterFileBatchLine::class);
});

it('dispatches ProcessGeslibInterFileBatchLine when batch lines exist after finishing', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();
    GeslibInterFileBatchLine::factory()->create([
        'geslib_inter_file_id' => $file->id,
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, fakeLines(2, Province::CODE), startLine: 0, chunkSize: 10);
    $job->handle();

    Bus::assertDispatched(ProcessGeslibInterFileBatchLine::class);
});

it('updates processed_lines correctly for each chunk', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job1 = makeJobStub($file, fakeLines(10, StationeryCategory::CODE), startLine: 0, chunkSize: 3);
    $job1->handle();

    $file->refresh();
    expect($file->processed_lines)->toBe(3);

    $job2 = makeJobStub($file, fakeLines(10, StationeryCategory::CODE), startLine: 3, chunkSize: 3);
    $job2->handle();

    $file->refresh();
    expect($file->processed_lines)->toBe(6);
});

it('clears log when it exceeds 2000 entries', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create([
        'log' => array_fill(0, 2001, ['level' => CommandContract::LEVEL_INFO, 'message' => 'test']),
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, fakeLines(2, Province::CODE), startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect(count($file->log))->toBeLessThan(2001);
});

it('handles empty file correctly', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, [], startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->total_lines)
        ->toBe(0)
        ->and($file->processed_lines)->toBe(0)
        ->and($file->finished_at)->not()->toBeNull();
});

it('handles chunk that exceeds total lines correctly', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = makeJobStub($file, fakeLines(5, StationeryCategory::CODE), startLine: 3, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->processed_lines)->toBe(5);
});

it('releases cache lock when file is finished', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $lock = Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME);
    $lock->get();

    $job = makeJobStub($file, fakeLines(2, Province::CODE), startLine: 0, chunkSize: 10);
    $job->handle();

    expect($lock->get())->toBeTrue();
    $lock->release();
});

it('appends error to log and marks as failed on failed()', function (): void {
    $file = GeslibInterFile::factory()->create();

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);

    $job->failed(new RuntimeException('Boom'));

    $file->refresh();

    expect($file->status)
        ->toBe(GeslibInterFile::STATUS_FAILED)
        ->and($file->finished_at)->not()->toBeNull()
        ->and(collect($file->log)->last())
        ->toMatchArray([
            'level' => CommandContract::LEVEL_ERROR,
            'message' => 'Boom',
        ]);
});

it('releases cache lock on failed()', function (): void {
    $file = GeslibInterFile::factory()->create();

    $lock = Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME);
    $lock->get();

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);
    $job->failed(new RuntimeException('Boom'));

    expect($lock->get())->toBeTrue();
    $lock->release();
});

it('preserves existing log entries when appending error on failed()', function (): void {
    $file = GeslibInterFile::factory()->create([
        'log' => [
            ['level' => CommandContract::LEVEL_INFO, 'message' => 'Previous log'],
        ],
    ]);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);
    $job->failed(new RuntimeException('Boom'));

    $file->refresh();

    expect(count($file->log))
        ->toBe(2)
        ->and($file->log[0]['message'])->toBe('Previous log')
        ->and($file->log[1]['message'])->toBe('Boom');
});

it('handles null log gracefully on failed()', function (): void {
    $file = GeslibInterFile::factory()->create(['log' => null]);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);
    $job->failed(new RuntimeException('Boom'));

    $file->refresh();

    expect($file->log)
        ->toBeArray()
        ->and(count($file->log))->toBe(1)
        ->and($file->log[0]['message'])->toBe('Boom');
});

it('throws when handling an initial_load file to prevent accidental mixing with normal update flow', function (): void {
    $file = GeslibInterFile::factory()->create([
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'initial_load');
});
