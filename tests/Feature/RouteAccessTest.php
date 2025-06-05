<?php

namespace NumaxLab\Lunar\Geslib\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Admin\Models\Staff; // Assuming this is Lunar's admin user model
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class RouteAccessTest extends TestCase
{
    protected function getAdminUser(): Authenticatable
    {
        // This is a simplified way to get an admin user.
        // In a full Lunar setup, Staff::factory()->create() would be more appropriate.
        // We need an authenticatable user that passes the 'can:access-hub' gate.
        // If Staff model or factory isn't easily available or has too many dependencies,
        // we might need to mock the user and gate.
        if (class_exists(Staff::class) && method_exists(Staff::class, 'factory')) {
            // Ensure the staff member has necessary permissions if policies are strict
            // For basic access, just creating one might be enough if gates are permissive for any staff.
            return Staff::factory()->create();
        }

        // Fallback: Create a generic user and mock the Gate if needed.
        // For now, let's assume a basic staff model can be instantiated for actingAs(),
        // or that the routes don't strictly require a specific Staff model instance
        // if middleware is not super strict on model type.
        $mockUser = new \Illuminate\Foundation\Auth\User(); // Generic user
        $mockUser->id = 1;
        // You might need to mock Gate checks if 'can:access-hub' is complex:
        // \Illuminate\Support\Facades\Gate::define('access-hub', fn () => true);
        return $mockUser;
    }

    /** @test */
    public function geslib_dashboard_page_is_accessible()
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user, 'staff') // Assuming 'staff' is the guard for Lunar admin
                         ->get(route('adminhub.geslib.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('lunar-geslib.admin.geslib-dashboard');
    }

    /** @test */
    public function geslib_file_import_log_page_is_accessible()
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user, 'staff')
                         ->get(route('adminhub.geslib.file-import-log'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('lunar-geslib.admin.geslib-file-import-log');
    }

    /** @test */
    public function geslib_order_export_log_page_is_accessible()
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user, 'staff')
                         ->get(route('adminhub.geslib.order-export-log'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('lunar-geslib.admin.geslib-order-export-log');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Ensure routes are loaded for these tests
        // The TestCase already loads the LunarGeslibServiceProvider which loads routes.
        // If web middleware group is needed by routes (it is)
        // Testbench usually sets up a basic one.
        // Make sure 'can:access-hub' gate passes for testing routes.
        $app['config']->set('auth.guards.staff', [
            'driver' => 'session',
            'provider' => 'staff',
        ]);
        $app['config']->set('auth.providers.staff', [
            'driver' => 'eloquent',
            'model' => Staff::class, // Or your mock user class if Staff is too complex
        ]);
         // Define a simple gate for 'access-hub' to allow access.
        // In a real scenario, Lunar's AuthServiceProvider would define this.
        // For package testing, we often simplify this.
        $app->resolving(\Illuminate\Contracts\Auth\Access\Gate::class, function ($gate) {
            $gate->define('access-hub', fn ($user = null) => true);
        });
    }
}
