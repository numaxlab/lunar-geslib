<?php

namespace NumaxLab\Lunar\Geslib\Tests\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Models\Order;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderStatusWidget;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

// Assuming Lunar's Order model is available

class GeslibOrderStatusWidgetTest extends TestCase
{
    use RefreshDatabase;

    // Helper to create a mock Order.
    // In a full Lunar setup, you'd use Order::factory()->create().
    // Here, we might need to mock it if Order factory isn't easily available
    // or has too many dependencies for this package's tests.
    // For simplicity, we'll try creating a basic one if Lunar's migrations are run.
    // If Lunar's base migrations are not run by default by TestCase, this will fail
    // and require a more sophisticated Order mock or factory setup.
    /** @test */
    public function component_renders_with_no_sync_information()
    {
        $order = $this->createTestOrder(['id' => 1]);

        Livewire::test(GeslibOrderStatusWidget::class, ['order' => $order])
            ->assertStatus(200)
            ->assertSee('Geslib Sync Status')
            ->assertSee('No Geslib sync information for this order.');
    }

    private function createTestOrder(array $attributes = []): Order
    {
        // This is a simplified creation. Lunar's Order model has many relations and attributes.
        // This will only work if the 'orders' table schema is available (e.g., from Lunar core migrations).
        // If not, these tests will need adjustment or a mock Order object.
        // Let's assume for now TestCase is configured to run necessary core migrations
        // or that Order model doesn't strictly require all fields for new instance.
        if (!class_exists(Order::class)) {
            // Mock Order if Lunar core is not fully bootstrapped for tests
            $orderMock = $this->createMock(Order::class);
            $orderMock->id = $attributes['id'] ?? rand(1, 1000);
            // Add other properties if the component uses them directly from the Order model.
            return $orderMock;
        }

        // Attempt to use factory if available (requires Lunar test setup)
        if (method_exists(Order::class, 'factory')) {
            return Order::factory()->create($attributes);
        }

        // Fallback to simple create if no factory and class exists
        // This is highly likely to fail due to missing fields/relations in a real Lunar setup
        // but might pass if the widget only uses $order->id.
        // The component uses $this->order->id, so an object with an id property is key.
        $orderData = array_merge([
            'user_id' => null, // Or create a mock user
            'channel_id' => 1, // Or create a mock channel
            'status' => 'pending',
            'reference' => 'test-order-' . uniqid(),
            'sub_total' => 1000,
            'total' => 1200,
            'discount_total' => 0,
            'shipping_total' => 200,
            'tax_breakdown' => json_encode([]),
            'tax_total' => 0,
            'currency_code' => 'USD',
            'compare_currency_code' => 'USD',
            'exchange_rate' => 1,
            'placed_at' => now(),
            'meta' => null, // Or json_encode([])
            'new_customer' => false,
        ], $attributes);

        // Manually create if factory is not available
        // This is a hack for testing environment where full Lunar setup might be missing.
        // $order = new Order($orderData);
        // $order->id = $orderData['id'] ?? rand(1,1000); // Ensure ID is set if not auto-incrementing in test
        // return $order;
        // For this test, the component only needs $order->id
        // So, a stdClass or a mock might be safer if Order creation is problematic.

        $mockOrder = new Order(); // Eloquent model
        $mockOrder->id = $attributes['id'] ?? rand(1, 1000);
        // We are not saving it to DB to avoid full Order table schema dependency.
        // The component receives it as a parameter.
        return $mockOrder;
    }

    /** @test */
    public function component_renders_with_sync_information()
    {
        $order = $this->createTestOrder(['id' => 2]);
        GeslibOrderSyncLog::create([
            'order_id' => $order->id,
            'status' => 'success',
            'created_at' => now()->subHour(),
            'geslib_endpoint_called' => 'test/endpoint',
            'message' => 'Order synced. Geslib ID: G123',
        ]);

        Livewire::test(GeslibOrderStatusWidget::class, ['order' => $order])
            ->assertStatus(200)
            ->assertSee('Geslib Sync Status')
            ->assertSee('Status:')
            ->assertSee('Success')
            ->assertSee('Last Sync Attempt:')
            ->assertSee(now()->subHour()->format('Y-m-d H:i:s'))
            ->assertSee('Geslib ID:')
            ->assertSee('G123')
            ->assertSee('View Sync History');
    }

    /** @test */
    public function link_to_order_export_log_is_correct()
    {
        $order = $this->createTestOrder(['id' => 3]);
        GeslibOrderSyncLog::create([
            'order_id' => $order->id,
            'status' => 'error',
            'created_at' => now(),
        ]);

        $expectedLink = route('adminhub.geslib.order-export-log', ['q_order_id' => $order->id]);

        Livewire::test(GeslibOrderStatusWidget::class, ['order' => $order])
            ->assertSeeHtml('href="' . $expectedLink . '"');
    }
}
