<?php

namespace NumaxLab\Lunar\Geslib\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Lunar\Admin\AdminHubServiceProvider;
use Lunar\LunarServiceProvider;
use NumaxLab\Lunar\Geslib\LunarGeslibServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up any specific configuration or environment variables needed for tests
        // For example, if your package relies on specific config values:
        // config()->set('lunar.geslib.some_key', 'some_value');

        // It's good practice to explicitly set the database connection for tests
        // especially when using RefreshDatabase.
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class, // Assuming Lunar core services might be needed
            AdminHubServiceProvider::class, // For admin panel context, routes, etc.
            LivewireServiceProvider::class, // For Livewire components
            LunarGeslibServiceProvider::class, // Your package's service provider
        ];
    }

    /**
     * Load package alias
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            // 'YourPackageFacade' => \NumaxLab\Lunar\Geslib\Facades\YourPackageFacade::class,
        ];
    }

    /**
     * Automatically run migrations for this package.
     * You might need to manually load your package's migrations
     * if they are not automatically discovered.
     */
    protected function defineDatabaseMigrations()
    {
        // This will run migrations from the 'database/migrations' directory of the package.
        // Ensure your package's service provider loads migrations using $this->loadMigrationsFrom().
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // If you need migrations from Lunar core or other dependencies, load them here too.
        // Example: $this->loadMigrationsFrom(__DIR__.'/../../vendor/lunarphp/lunar/database/migrations');
        // This path might need adjustment based on actual vendor structure if testbench doesn't handle it.

        // For now, let's assume Lunar's own migrations are handled by its service provider or test setup.
    }
}
