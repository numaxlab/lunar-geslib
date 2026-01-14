<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

function putWithMTime(string $disk, string $path, string $contents, int $mtime): void
{
    Storage::disk($disk)->put($path, $contents);

    // Ensure mtime matches what the command will read via File::lastModified($storage->path(...))
    $absPath = Storage::disk($disk)->path($path);
    @mkdir(dirname($absPath), 0777, true);
    touch($absPath, $mtime);
}

beforeEach(function (): void {
    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib/inter');

    Storage::fake('local');
    Bus::fake();
});

test('it exits successfully when there are no files', function (): void {
    Cache::partialMock()->shouldReceive('lock')->never();

    $exit = Artisan::call('lunar:geslib:import');

    expect($exit)->toBe(Import::SUCCESS);
    expect(GeslibInterFile::count())->toBe(0);
    Bus::assertNothingDispatched();
});

test('it creates a GeslibInterFile and dispatches the processor for the oldest zip', function (): void {
    $disk = config('lunar.geslib.inter_files_disk');
    $base = config('lunar.geslib.inter_files_path');

    $t1 = Carbon::now()->subMinutes(10)->timestamp;
    $t2 = Carbon::now()->subMinutes(5)->timestamp;

    putWithMTime($disk, "$base/INTER001.zip", 'zip-1', $t1);
    putWithMTime($disk, "$base/INTER002.zip", 'zip-2', $t2);

    Storage::disk($disk)->put("$base/readme.txt", 'ignore');

    $this
        ->artisan('lunar:geslib:import')
        ->expectsOutputToContain('Getting all Geslib INTER files...')
        ->expectsOutputToContain('Dispatching INTER001.zip...')
        ->assertExitCode(Import::SUCCESS);

    $file = GeslibInterFile::first();

    expect($file)->not
        ->toBeNull()
        ->and($file->name)->toBe('INTER001.zip')
        ->and($file->status)->toBe(GeslibInterFile::STATUS_PENDING)
        ->and($file->received_at->timestamp)->toBe($t1);

    Bus::assertDispatched(ProcessGeslibInterFile::class, 1);
});

test('it skips files older than the last imported file timestamp', function (): void {
    $disk = config('lunar.geslib.inter_files_disk');
    $base = config('lunar.geslib.inter_files_path');

    $older = Carbon::now()->subHour()->timestamp;
    putWithMTime($disk, "$base/old.zip", 'zip-1', $older);

    GeslibInterFile::factory()->create([
        'name' => 'previous.zip',
        'status' => GeslibInterFile::STATUS_SUCCESS,
        'received_at' => Carbon::createFromTimestamp($older + 60),
    ]);

    $this
        ->artisan('lunar:geslib:import')
        ->assertExitCode(Import::SUCCESS);

    expect(GeslibInterFile::count())->toBe(1);
    Bus::assertNothingDispatched();
});

test('it skips files with same timestamp and name as the last processed', function (): void {
    $disk = config('lunar.geslib.inter_files_disk');
    $base = config('lunar.geslib.inter_files_path');

    $ts = Carbon::now()->subMinutes(15)->timestamp;
    putWithMTime($disk, "$base/same.zip", 'zip', $ts);

    GeslibInterFile::factory()->create([
        'name' => 'same.zip',
        'status' => GeslibInterFile::STATUS_SUCCESS,
        'received_at' => Carbon::createFromTimestamp($ts),
    ]);

    Cache::partialMock()->shouldReceive('lock')->never();

    $this->artisan('lunar:geslib:import')->assertExitCode(Import::SUCCESS);

    // No new records, no dispatches
    expect(GeslibInterFile::count())->toBe(1);
    Bus::assertNothingDispatched();
});

test('it exits early when the worker is busy (lock not acquired)', function (): void {
    $disk = config('lunar.geslib.inter_files_disk');
    $base = config('lunar.geslib.inter_files_path');

    $ts = Carbon::now()->timestamp;
    putWithMTime($disk, "$base/file.zip", 'zip', $ts);

    // Simulate busy lock
    Cache::partialMock()->shouldReceive('lock')->once()->andReturn(new class
    {
        public function get(): bool
        {
            return false;
        }
    });

    $this
        ->artisan('lunar:geslib:import')
        ->expectsOutputToContain('The Geslib INTER files import worker is busy')
        ->assertExitCode(Import::SUCCESS);

    // No records created and no job dispatched
    expect(GeslibInterFile::count())->toBe(0);
    Bus::assertNothingDispatched();
});
