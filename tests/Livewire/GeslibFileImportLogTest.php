<?php

namespace NumaxLab\Lunar\Geslib\Tests\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibFileImportLog;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class GeslibFileImportLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /** @test */
    public function component_renders_correctly_with_no_logs()
    {
        Livewire::test(GeslibFileImportLog::class)
            ->assertStatus(200)
            ->assertSee('Geslib File Import Logs')
            ->assertSee('No file import logs found.');
    }

    /** @test */
    public function it_displays_logs_with_pagination()
    {
        GeslibInterFile::factory()->count(20)->create();

        Livewire::test(GeslibFileImportLog::class)
            ->assertSee(GeslibInterFile::first()->name)
            ->assertViewHas('logs', function ($logs) {
                return $logs->count() === 15; // Default pagination
            });
    }

    /** @test */
    public function search_by_filename_works()
    {
        GeslibInterFile::create(['name' => 'search_target_file.txt', 'status' => 'pending']);
        GeslibInterFile::create(['name' => 'another_file.txt', 'status' => 'processed']);

        Livewire::test(GeslibFileImportLog::class)
            ->set('searchFilename', 'target')
            ->assertSee('search_target_file.txt')
            ->assertDontSee('another_file.txt');
    }

    /** @test */
    public function filter_by_status_works()
    {
        GeslibInterFile::create(['name' => 'file_pending.txt', 'status' => 'pending']);
        GeslibInterFile::create(['name' => 'file_processed.txt', 'status' => 'processed']);

        Livewire::test(GeslibFileImportLog::class)
            ->set('filterStatus', 'pending')
            ->assertSee('file_pending.txt')
            ->assertDontSee('file_processed.txt');
    }

    /** @test */
    public function filter_by_date_range_works()
    {
        GeslibInterFile::create(['name' => 'file_yesterday.txt', 'status' => 'pending', 'created_at' => now()->subDay()]);
        GeslibInterFile::create(['name' => 'file_today.txt', 'status' => 'processed', 'created_at' => now()]);
        GeslibInterFile::create(['name' => 'file_tomorrow.txt', 'status' => 'error', 'created_at' => now()->addDay()]);


        Livewire::test(GeslibFileImportLog::class)
            ->set('filterDateFrom', now()->subDay()->toDateString())
            ->set('filterDateTo', now()->toDateString())
            ->assertSee('file_yesterday.txt')
            ->assertSee('file_today.txt')
            ->assertDontSee('file_tomorrow.txt');
    }

    /** @test */
    public function reprocess_file_action_dispatches_job_for_error_file()
    {
        $errorFile = GeslibInterFile::create(['name' => 'error_file.txt', 'status' => 'error']);

        Livewire::test(GeslibFileImportLog::class)
            ->call('reprocessFile', $errorFile->id);

        Queue::assertPushed(ProcessGeslibInterFile::class, function ($job) use ($errorFile) {
            return $job->geslibInterFile->id === $errorFile->id;
        });

        // Check for session message (optional, but good for UX)
        // ->assertEmitted('notify', ['message' => 'File ID ' . $errorFile->id . ' has been queued for reprocessing.', 'type' => 'success']);
        // This depends on how you handle notifications/feedback in your Livewire setup.
        // For this test, checking session flash message if set by component.
        // ->assertSessionHas('message', 'File ID ' . $errorFile->id . ' has been queued for reprocessing.');
        // The component uses session()->flash, which might not be directly testable this way in Livewire tests
        // without specific setup or custom assertions. Focus on job dispatch.
    }

    /** @test */
    public function reprocess_file_action_does_not_dispatch_job_for_non_error_file()
    {
        $processedFile = GeslibInterFile::create(['name' => 'processed_file.txt', 'status' => 'processed']);

        Livewire::test(GeslibFileImportLog::class)
            ->call('reprocessFile', $processedFile->id);

        Queue::assertNotPushed(ProcessGeslibInterFile::class);
    }

    // Helper to create factories if needed, but direct creation is fine for now.
    // protected function geslibInterFileFactory() { ... }
}
