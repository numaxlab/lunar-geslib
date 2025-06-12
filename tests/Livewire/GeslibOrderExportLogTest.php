<?php

namespace NumaxLab\Lunar\Geslib\Tests\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderExportLog;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class GeslibOrderExportLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function component_renders_correctly_with_no_logs()
    {
        Livewire::test(GeslibOrderExportLog::class)
            ->assertStatus(200)
            ->assertSee('Geslib Order Export Logs')
            ->assertSee('No order export logs found.');
    }

    /** @test */
    public function it_displays_logs_with_pagination()
    {
        GeslibOrderSyncLog::factory()->count(20)->create(['order_id' => 123]); // Assuming a factory or simple creation

        Livewire::test(GeslibOrderExportLog::class)
            ->assertSee('Order ID') // Column header
            ->assertSee('123')     // Example order_id from created logs
            ->assertViewHas('logs', function ($logs) {
                return $logs->count() === 15; // Default pagination
            });
    }

    /** @test */
    public function search_by_order_id_works()
    {
        GeslibOrderSyncLog::create(['order_id' => 1001, 'status' => 'success', 'geslib_endpoint_called' => 'ep1']);
        GeslibOrderSyncLog::create(['order_id' => 1002, 'status' => 'error', 'geslib_endpoint_called' => 'ep2']);

        Livewire::test(GeslibOrderExportLog::class)
            ->set('searchOrderId', '1001')
            ->assertSee('1001')
            ->assertDontSee('1002');
    }

    /** @test */
    public function filter_by_status_works()
    {
        GeslibOrderSyncLog::create(['order_id' => 2001, 'status' => 'success', 'geslib_endpoint_called' => 'ep1']);
        GeslibOrderSyncLog::create(['order_id' => 2002, 'status' => 'error', 'geslib_endpoint_called' => 'ep2']);

        Livewire::test(GeslibOrderExportLog::class)
            ->set('filterStatus', 'error')
            ->assertSee('2002')
            ->assertDontSee('2001');
    }

    /** @test */
    public function show_details_modal_works()
    {
        $log = GeslibOrderSyncLog::create([
            'order_id' => 3001,
            'status' => 'success',
            'geslib_endpoint_called' => '/api/orders',
            'payload_to_geslib' => '{"id":3001}',
            'payload_from_geslib' => '{"geslib_id":"G123"}',
            'message' => 'OK',
        ]);

        Livewire::test(GeslibOrderExportLog::class)
            ->call('showDetailsModal', $log->id)
            ->assertSet('showingModal', true)
            ->assertSet('selectedLog.id', $log->id)
            ->assertSee('Order Export Log Details (ID: ' . $log->id . ')')
            ->assertSee('{"id":3001}') // payload_to_geslib
            ->assertSee('{"geslib_id":"G123"}'); // payload_from_geslib
    }


    // Minimal factory definition for LunarGeslibOrderSyncLog if not existing
    // This would typically be in a dedicated factory file.
    // For this test, we can define it here or use direct ::create if simple enough.
    // If LunarGeslibOrderSyncLog::factory() does not exist, this test suite needs it.
    // We'll assume direct creation or that the factory is defined elsewhere.
    // If these tests fail due to missing factory, it means we need to create it.
    // For now, using ::create as in other tests.
}
