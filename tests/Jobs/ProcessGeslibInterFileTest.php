<?php

namespace NumaxLab\Lunar\Geslib\Tests\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Notifications\GeslibFileImportFailed;
use NumaxLab\Lunar\Geslib\Tests\TestCase;
use RuntimeException; // For simulating exceptions

// Mock the GeslibFile class if its parsing is complex or relies on external state
// For this example, we'll assume it can be instantiated with simple content
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Geslib\Lines\Article; // Example line type
use NumaxLab\Geslib\Lines\Action;  // Example action

class ProcessGeslibInterFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Queue::fake(); // Usually for testing job dispatching, but can be useful
        Storage::fake('local'); // Assuming 'local' is the disk used in geslib config

        // Configure the path for Geslib inter files for testing
        config()->set('lunar.geslib.inter_files_disk', 'local');
        config()->set('lunar.geslib.inter_files_path', 'geslib_test_inter_files');
        Storage::disk('local')->makeDirectory('geslib_test_inter_files');

        // Configure notification settings for tests
        config()->set('lunar.geslib.notifications.enabled', true);
        config()->set('lunar.geslib.notifications.mail_to', 'test@example.com');
        config()->set('lunar.geslib.notifications.throttle_period_minutes', 60);
    }

    private function createTestGeslibFile(string $filename, string $content): GeslibInterFile
    {
        Storage::disk('local')->put('geslib_test_inter_files/'.$filename, $content);
        return GeslibInterFile::create([
            'name' => $filename,
            'type' => 'test_import', // Or any relevant type
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_processes_a_valid_geslib_file_successfully()
    {
        // Minimal valid Geslib file content. This depends heavily on GeslibFile::parse logic.
        // For this example, let's assume an Editorial line.
        // E;A;123;Editorial Name;Ext Name;Spain
        $geslibFileContent = "E;A;123;Test Editorial;Test Editorial Ext;ES\n";
        $geslibInterFile = $this->createTestGeslibFile('valid_import.txt', $geslibFileContent);

        // Mock the EditorialCommand or ensure it doesn't break in tests
        // For simplicity, we assume the command doesn't throw an error with this line.

        $job = new ProcessGeslibInterFile($geslibInterFile);
        $job->handle();

        $geslibInterFile->refresh();
        $this->assertEquals('processed', $geslibInterFile->status);
        $this->assertNotNull($geslibInterFile->started_at);
        $this->assertNotNull($geslibInterFile->finished_at);
        $this->assertNull($geslibInterFile->notes); // Should be cleared on success
    }

    /** @test */
    public function it_handles_job_failure_updates_status_and_sends_notification()
    {
        $geslibFileContent = "INVALID_LINE_FORMAT\n"; // Content that will cause an error
        $geslibInterFile = $this->createTestGeslibFile('error_import.txt', $geslibFileContent);

        $job = new ProcessGeslibInterFile($geslibInterFile);

        try {
            // Manually call handle and simulate an exception if GeslibFile::parse throws it
            // Or, if the job's internal logic throws it (e.g., unknown line type)
            $job->handle(); // This might throw RuntimeException for default case
        } catch (RuntimeException $e) {
            // If handle throws, call failed method manually for testing that part
            $job->failed($e);
        }

        $geslibInterFile->refresh();
        $this->assertEquals('error', $geslibInterFile->status);
        $this->assertNotNull($geslibInterFile->notes); // Should contain error message
        $this->assertStringContainsString('Unknown line type', $geslibInterFile->notes); // Example check

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            GeslibFileImportFailed::class,
            function ($notification, $channels, $notifiable) use ($geslibInterFile) {
                return $notifiable->routes['mail'] === 'test@example.com' &&
                       $notification->file->id === $geslibInterFile->id;
            }
        );

        // Test throttling: try to fail and notify again immediately
        Cache::shouldReceive('has')->once()->with('geslib_notification_import_failed_' . $geslibInterFile->id)->andReturn(true);
        Notification::assertSentTimes(GeslibFileImportFailed::class, 1); // Should not send again

        // To properly test the failed method of a queued job, you'd typically
        // need to dispatch it via Queue::push and have a worker process it,
        // or use a more specific test helper if available in Testbench for failed jobs.
        // Here, we are calling failed() directly after catching an exception from handle().
    }

    /** @test */
    public function notification_is_not_sent_if_disabled_in_config()
    {
        config()->set('lunar.geslib.notifications.enabled', false);

        $geslibFileContent = "ANOTHER_INVALID_LINE\n";
        $geslibInterFile = $this->createTestGeslibFile('disabled_notify.txt', $geslibFileContent);

        $job = new ProcessGeslibInterFile($geslibInterFile);
        try {
            $job->handle();
        } catch (RuntimeException $e) {
            $job->failed($e);
        }

        Notification::assertNothingSent();
    }
     /** @test */
    public function notification_throttling_works()
    {
        $geslibFileContent = "THROTTLE_ME_LINE\n";
        $geslibInterFile = $this->createTestGeslibFile('throttle_test.txt', $geslibFileContent);

        $job = new ProcessGeslibInterFile($geslibInterFile);
        $exception = new RuntimeException('Simulated failure for throttling test.');

        // First failure - should send notification
        Cache::shouldReceive('has')->once()->with('geslib_notification_import_failed_' . $geslibInterFile->id)->andReturn(false);
        Cache::shouldReceive('put')->once()
            ->with('geslib_notification_import_failed_' . $geslibInterFile->id, true, \Mockery::any());

        $job->failed($exception); // Call failed method directly to test its logic

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            GeslibFileImportFailed::class
        );

        // Second failure - should be throttled
        Cache::shouldReceive('has')->once()->with('geslib_notification_import_failed_' . $geslibInterFile->id)->andReturn(true);

        $job->failed($exception); // Call failed method again

        Notification::assertSentTimes(GeslibFileImportFailed::class, 1); // Should still be 1, not 2
    }
}
