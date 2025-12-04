<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can be created', function () {
    $geslibInterFile = GeslibInterFile::factory()->create();

    $this->assertDatabaseHas(GeslibInterFile::class, [
        'id' => $geslibInterFile->id,
        'name' => $geslibInterFile->name,
    ]);
});

it('casts attributes', function () {
    $geslibInterFile = GeslibInterFile::factory([
        'started_at' => now(),
        'finished_at' => now(),
        'log' => ['level' => 'error', 'message' => 'Something went wrong!'],
    ])->create();

    $geslibInterFile->refresh();

    $this->assertInstanceOf(Carbon::class, $geslibInterFile->received_at);
    $this->assertInstanceOf(Carbon::class, $geslibInterFile->started_at);
    $this->assertInstanceOf(Carbon::class, $geslibInterFile->finished_at);
    $this->assertIsArray($geslibInterFile->log);
});

it('has a batchLines relationship', function () {
    $geslibInterFile = GeslibInterFile::factory()->create();
    $batchLine = GeslibInterFileBatchLine::factory()->create([
        'geslib_inter_file_id' => $geslibInterFile->id,
    ]);

    $geslibInterFile->refresh();

    $this->assertCount(1, $geslibInterFile->batchLines);
    $this->assertEquals($batchLine->id, $geslibInterFile->batchLines->first()->id);
});
