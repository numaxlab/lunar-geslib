<?php

namespace NumaxLab\Lunar\Geslib\Tests\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibDashboard;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\LunarGeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Notifications\GeslibConfigurationError;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class GeslibDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        // Default valid config for most tests
        config()->set('lunar.geslib.inter_files_disk', 'local');
        config()->set('lunar.geslib.inter_files_path', 'geslib_test_inter_files');
        config()->set('lunar.geslib.product_types_taxation', ['L0' => 1]);
        config()->set('lunar.geslib.notifications.enabled', true);
        config()->set('lunar.geslib.notifications.mail_to', 'test@example.com');
        config()->set('lunar.geslib.notifications.throttle_period_minutes', 60);
    }

    /** @test */
    public function component_renders_correctly()
    {
        Livewire::test(GeslibDashboard::class)
            ->assertStatus(200)
            ->assertSee('Geslib Integration Dashboard')
            ->assertSee('File Import Status')
            ->assertSee('Order Export Status')
            ->assertSee('Configuration Check');
    }

    /** @test */
    public function it_displays_file_import_stats()
    {
        GeslibInterFile::create(['name' => 'file1.txt', 'status' => 'processed', 'created_at' => now()->subDay()]);
        GeslibInterFile::create(['name' => 'file2.txt', 'status' => 'error', 'notes' => 'test error', 'created_at' => now()]);

        Livewire::test(GeslibDashboard::class)
            ->assertSee('Last Import Run Status:')
            ->assertSee('Error at ' . now()->toDateTimeString()) // Assuming latest is the error one
            ->assertSee('Files Processed:')
            ->assertSee('2') // Total files
            ->assertSee('Recent Import Errors:')
            ->assertSee('test error');
    }

    /** @test */
    public function it_displays_order_export_stats_when_no_data()
    {
        Livewire::test(GeslibDashboard::class)
            ->assertSee('Orders Awaiting Sync:')
            ->assertSee('0')
            ->assertSee('No order sync data available.');
    }

    /** @test */
    public function it_displays_order_export_stats_with_data()
    {
        LunarGeslibOrderSyncLog::create([
            'order_id' => 1, 'status' => 'success', 'geslib_endpoint_called' => 'ep1'
        ]);
        LunarGeslibOrderSyncLog::create([
            'order_id' => 2, 'status' => 'pending', 'geslib_endpoint_called' => 'ep1'
        ]);
        LunarGeslibOrderSyncLog::create([
            'order_id' => 3, 'status' => 'error', 'geslib_endpoint_called' => 'ep2', 'message' => 'sync failed'
        ]);

        Livewire::test(GeslibDashboard::class)
            ->assertSee('OrdersAwaitingSync') // Property name in component
            ->assertSet('ordersAwaitingSync', 1)
            ->assertSet('ordersSuccessfullySynced', 1)
            ->assertSet('ordersFailedSync', 1)
            ->assertSee('Recent Order Sync Errors:')
            ->assertSee('sync failed');
    }

    /** @test */
    public function it_displays_configuration_status_correctly()
    {
        Livewire::test(GeslibDashboard::class)
            ->assertSee('Inter Files Disk:')
            ->assertSee('Set')
            ->assertSee('Notifications Mail To:')
            ->assertSee('Set');
    }

    /** @test */
    public function it_sends_configuration_error_notification_for_missing_disk()
    {
        config()->set('lunar.geslib.inter_files_disk', null);

        Livewire::test(GeslibDashboard::class); // Mount component to trigger fetchConfigData

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            GeslibConfigurationError::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'test@example.com' &&
                       $notification->configKey === 'inter_files_disk';
            }
        );
    }

    /** @test */
    public function configuration_error_notification_is_throttled()
    {
        config()->set('lunar.geslib.inter_files_path', null); // Missing path

        // First time
        Cache::shouldReceive('has')->once()->with('geslib_notification_config_error_inter_files_path')->andReturn(false);
        Cache::shouldReceive('put')->once()->with('geslib_notification_config_error_inter_files_path', true, \Mockery::any());
        Livewire::test(GeslibDashboard::class);

        Notification::assertSentTimes(GeslibConfigurationError::class, 1);

        // Second time (should be throttled)
        Cache::shouldReceive('has')->once()->with('geslib_notification_config_error_inter_files_path')->andReturn(true);
        Livewire::test(GeslibDashboard::class); // Re-mount

        Notification::assertSentTimes(GeslibConfigurationError::class, 1); // Still 1
    }

    /** @test */
    public function no_config_notification_if_notifications_disabled()
    {
        config()->set('lunar.geslib.notifications.enabled', false);
        config()->set('lunar.geslib.inter_files_disk', null); // Critical error

        Livewire::test(GeslibDashboard::class);

        Notification::assertNothingSent();
    }
}
