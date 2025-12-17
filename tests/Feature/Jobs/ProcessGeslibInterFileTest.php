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

beforeEach(function () {
    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib-inter');

    Storage::fake('local');
    Cache::flush();
});

afterEach(fn () => Mockery::close());

it('generates a unique id including file id, start and chunk', function () {
    $file = GeslibInterFile::factory()->create();

    $job = new ProcessGeslibInterFile($file, startLine: 10, chunkSize: 500);

    expect($job->uniqueId())->toBe($file->id.'-10-500');
});

it('processes a chunk and dispatches next job when not finished', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(5, StationeryCategory::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);

    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 3);
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

it('does not update status or started_at when file is already processing', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(5, StationeryCategory::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create([
        'status' => GeslibInterFile::STATUS_PROCESSING,
        'started_at' => now()->subHour(),
        'total_lines' => 5,
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $originalStartedAt = $file->started_at;

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 3);
    $job->handle();

    $file->refresh();

    expect($file->started_at->timestamp)->toBe($originalStartedAt->timestamp);
});

it('extracts zip file when extracted file does not exist', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(2, Province::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create(['name' => 'test.zip']);

    $zipPath = config('lunar.geslib.inter_files_path').'/'.$file->name;
    $extractedPath = config('lunar.geslib.inter_files_path');

    $zip = new ZipArchive;
    $fullZipPath = Storage::disk('local')->path($zipPath);
    Storage::disk('local')->makeDirectory(config('lunar.geslib.inter_files_path'));

    if ($zip->open($fullZipPath, ZipArchive::CREATE) === true) {
        $zip->addFromString('test', 'dummy content');
        $zip->close();
    }

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    expect(Storage::disk('local')->exists($extractedPath))->toBeTrue();
});

it('throws exception when zip file cannot be opened', function () {
    $file = GeslibInterFile::factory()->create(['name' => 'nonexistent.zip']);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Unable to open zip file');
});

it('finalizes when file finished and there are no batch lines', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(2, Province::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->status)
        ->toBe(ProcessGeslibInterFileBatchLine::getStatusFromLog($file->log))
        ->and($file->finished_at)->not()->toBeNull()
        ->and(Storage::disk('local')->exists($extracted))->toBeFalse();

    Bus::assertNotDispatched(ProcessGeslibInterFileBatchLine::class);
});

it('dispatches ProcessGeslibInterFileBatchLine when batch lines exist after finishing', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(2, Province::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();
    GeslibInterFileBatchLine::factory()->create([
        'geslib_inter_file_id' => $file->id,
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    Bus::assertDispatched(ProcessGeslibInterFileBatchLine::class);
});

it('updates processed_lines correctly for each chunk', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(10, StationeryCategory::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    // Process first chunk
    $job1 = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 3);
    $job1->handle();

    $file->refresh();
    expect($file->processed_lines)->toBe(3);

    // Process second chunk
    $job2 = new ProcessGeslibInterFile($file, startLine: 3, chunkSize: 3);
    $job2->handle();

    $file->refresh();
    expect($file->processed_lines)->toBe(6);
});

it('clears log when it exceeds 2000 entries', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(2, Province::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create([
        'log' => array_fill(0, 2001, ['level' => CommandContract::LEVEL_INFO, 'message' => 'test']),
    ]);

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect(count($file->log))->toBeLessThan(2001);
});

it('handles empty file correctly', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return [];
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->total_lines)
        ->toBe(0)
        ->and($file->processed_lines)->toBe(0)
        ->and($file->finished_at)->not()->toBeNull();
});

it('handles chunk that exceeds total lines correctly', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(5, StationeryCategory::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $job = new ProcessGeslibInterFile($file, startLine: 3, chunkSize: 10);
    $job->handle();

    $file->refresh();

    expect($file->processed_lines)->toBe(5);
});

it('releases cache lock when file is finished', function () {
    Bus::fake();

    $fakeParsed = new class
    {
        public function lines(): array
        {
            return fakeLines(2, Province::CODE);
        }
    };

    Mockery::mock('alias:'.GeslibFile::class)
        ->shouldReceive('parse')
        ->andReturn($fakeParsed);

    $file = GeslibInterFile::factory()->create();

    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'dummy');

    $lock = Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME);
    $lock->get();

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 10);
    $job->handle();

    expect($lock->get())->toBeTrue();
    $lock->release();
});

it('appends error to log and marks as failed on failed()', function () {
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

it('releases cache lock on failed()', function () {
    $file = GeslibInterFile::factory()->create();

    $lock = Cache::lock(ProcessGeslibInterFile::CACHE_LOCK_NAME);
    $lock->get();

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);
    $job->failed(new RuntimeException('Boom'));

    expect($lock->get())->toBeTrue();
    $lock->release();
});

it('preserves existing log entries when appending error on failed()', function () {
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

it('handles null log gracefully on failed()', function () {
    $file = GeslibInterFile::factory()->create(['log' => null]);

    $job = new ProcessGeslibInterFile($file, startLine: 0, chunkSize: 1);
    $job->failed(new RuntimeException('Boom'));

    $file->refresh();

    expect($file->log)
        ->toBeArray()
        ->and(count($file->log))->toBe(1)
        ->and($file->log[0]['message'])->toBe('Boom');
});
