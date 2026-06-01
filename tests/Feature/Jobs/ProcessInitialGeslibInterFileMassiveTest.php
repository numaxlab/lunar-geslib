<?php

declare(strict_types=1);

/**
 * Manual/opt-in runner for INTER682 massive file validation.
 *
 * NOT part of the normal test suite. Run explicitly:
 *   GESLIB_INTER682_PATH=/path/to/INTER682 \
 *     ./vendor/bin/pest tests/Feature/Jobs/ProcessInitialGeslibInterFileMassiveTest.php --compact
 *
 * This test is skipped automatically unless GESLIB_INTER682_PATH is set.
 * It is never run in CI and never requires the file to be in fixtures.
 */

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Lunar\Models\Brand;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language as LunarLanguage;
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibBatchRelations;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibInterFile;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibPhaseChunk;
use NumaxLab\Lunar\Geslib\Models\Author;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Support\ImportRegistry;

function requireInter682Path(): string
{
    $path = env('GESLIB_INTER682_PATH');

    if (empty($path) || ! file_exists($path)) {
        test()->markTestSkipped('GESLIB_INTER682_PATH not set or file not found. Set env var to run this test.');
    }

    return $path;
}

it('detects INTER682 as an initial_load file', function (): void {
    $path = requireInter682Path();
    $content = file_get_contents($path);

    $parsed = GeslibFile::parse($content);

    expect($parsed->isInitialFile())->toBeTrue();
});

it('creates a GeslibInterFile record with initial_load type for INTER682', function (): void {
    $path = requireInter682Path();
    $content = file_get_contents($path);

    $firstLine = strtok($content, "\n");
    $fileType = str_starts_with((string) $firstLine, 'I|')
        ? GeslibInterFile::FILE_TYPE_INITIAL_LOAD
        : GeslibInterFile::FILE_TYPE_NORMAL_UPDATE;

    expect($fileType)->toBe(GeslibInterFile::FILE_TYPE_INITIAL_LOAD);

    $interFile = GeslibInterFile::factory()->create([
        'name' => 'INTER682',
        'file_type' => $fileType,
    ]);

    expect($interFile->usesInitialStrategy())->toBeTrue();
});

it('ProcessInitialGeslibInterFile records processing signals and builds a phase chain for INTER682', function (): void {
    $path = requireInter682Path();

    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib-inter');

    Storage::fake('local');
    Bus::fake();

    $interFile = GeslibInterFile::factory()->create([
        'name' => 'INTER682',
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    // Pre-place the extracted content so the job skips the ZIP extraction step
    $extractedPath = config('lunar.geslib.inter_files_path').'/INTER682';
    Storage::disk('local')->put($extractedPath, file_get_contents($path));

    $job = new ProcessInitialGeslibInterFile($interFile);
    $job->handle();

    $interFile->refresh();

    // Signal 1: coordinator must record that processing started
    expect($interFile->started_at)->not->toBeNull('started_at must be set by the coordinator');

    // Signal 2: total_lines must reflect the real parsed line count (INTER682 has thousands)
    expect($interFile->total_lines)->toBeGreaterThan(1000, 'INTER682 must have > 1000 parsed lines');

    // Signal 3: status must be "processing" — the chain was dispatched but not yet executed (Bus::fake)
    expect($interFile->status)->toBe(
        GeslibInterFile::STATUS_PROCESSING,
        'status must be "processing" after coordinator dispatches the phase chain',
    );

    // Signal 4: the phase chain must have been built with at least one PhaseChunk job.
    // The chain structure is: [phase1Chunk, ...phaseNChunks, batchRelations].
    // Bus::fake() registers only the first job as dispatched; remaining jobs are in its `chained` property.
    Bus::assertDispatched(
        ProcessInitialGeslibPhaseChunk::class,
        fn (ProcessInitialGeslibPhaseChunk $job): bool => collect($job->chained)->contains(
            fn (string $serialized): bool => str_contains($serialized, ProcessInitialGeslibBatchRelations::class),
        ),
    );
});

it('completes the full initial_load flow for INTER682 with real entities created and finished_at set', function (): void {
    $path = requireInter682Path();

    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib-inter');

    Storage::fake('local');
    ImportRegistry::flush();

    // Seed Lunar Language (required by UrlGenerator on entity creation)
    LunarLanguage::factory()->create();

    // Seed default Currency (required by PriceObserver when articles are created)
    Currency::factory()->create(['default' => true]);

    // Seed CollectionGroups required by InterCommands (production pre-condition)
    foreach ([
        BindingTypeCommand::HANDLE,
        ClassificationCommand::HANDLE,
        CollectionCommand::HANDLE,
        IbicCommand::HANDLE,
        LanguageCommand::HANDLE,
        StatusCommand::HANDLE,
        TopicCommand::HANDLE,
        TypeCommand::HANDLE,
    ] as $handle) {
        CollectionGroup::factory(['handle' => $handle])->create();
    }

    // Use the first 15 000 lines of INTER682 — representative slice with all 4 phases:
    // phase 1: AUT (authors, from ~line 12 893), 8 (languages), 7 (binding types),
    //          TIPART (types), E (statuses), 1L (editorials), CLASIF (classifications)
    // phase 2: GP4 (articles)
    // phase 4: LA (article-author batch), BIC (ibic batch)
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    $content = implode("\n", array_slice($lines, 0, 15000));

    $interFile = GeslibInterFile::factory()->create([
        'name' => 'INTER682',
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    Storage::disk('local')->put(
        config('lunar.geslib.inter_files_path').'/INTER682',
        $content,
    );

    // No Bus::fake() — queue connection is sync, full chain executes inline
    $job = new ProcessInitialGeslibInterFile($interFile);
    $job->handle();

    $interFile->refresh();

    // Full chain executed synchronously — finished_at must be set
    expect($interFile->finished_at)->not->toBeNull(
        'finished_at must be set after the full chain completes synchronously',
    );

    // Status must be success or warning — never processing (chain not done) or failed (exception)
    expect($interFile->status)->toBeIn(
        [GeslibInterFile::STATUS_SUCCESS, GeslibInterFile::STATUS_WARNING],
        'status must be success or warning after full chain — never processing or failed',
    );

    // Real entities created from the INTER682 slice (phase 1 commands ran)
    expect(Author::count())->toBeGreaterThan(0, 'at least one Author must be created');
    expect(Brand::count())->toBeGreaterThan(0, 'at least one Brand (editorial) must be created');
});
