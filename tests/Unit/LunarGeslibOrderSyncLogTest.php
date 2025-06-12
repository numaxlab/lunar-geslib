<?php

namespace NumaxLab\Lunar\Geslib\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class LunarGeslibOrderSyncLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_order_sync_log_entry()
    {
        // Note: order_id would typically be a foreign key to an orders table.
        // For this test, we are just checking if the model can be created.
        // In a full Lunar setup, you might create a mock Order model or use factories.
        $data = [
            'order_id' => 12345, // Example Order ID
            'geslib_endpoint_called' => '/api/v1/orders',
            'status' => 'success',
            'message' => 'Order synced successfully to Geslib.',
            'payload_to_geslib' => json_encode(['order_id' => 12345, 'amount' => 100.00]),
            'payload_from_geslib' => json_encode(['geslib_id' => 'GSLB_ORD_001', 'status' => 'received']),
        ];

        $log = GeslibOrderSyncLog::create($data);

        $this->assertInstanceOf(GeslibOrderSyncLog::class, $log);
        $this->assertDatabaseHas('lunar_geslib_order_sync_log', [
            'order_id' => 12345,
            'status' => 'success',
            'geslib_endpoint_called' => '/api/v1/orders',
        ]);
        $this->assertEquals('Order synced successfully to Geslib.', $log->message);
    }

    /** @test */
    public function status_and_message_can_be_updated()
    {
        $log = GeslibOrderSyncLog::create([
            'order_id' => 67890,
            'geslib_endpoint_called' => '/api/v1/order_update',
            'status' => 'pending',
        ]);

        $log->update([
            'status' => 'error',
            'message' => 'Failed to connect to Geslib endpoint.',
        ]);

        $this->assertEquals('error', $log->fresh()->status);
        $this->assertEquals('Failed to connect to Geslib endpoint.', $log->fresh()->message);
    }
}
