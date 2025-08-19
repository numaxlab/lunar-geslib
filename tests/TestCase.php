<?php

namespace Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\Admin\LunarPanelProvider;
use Lunar\LunarServiceProvider;
use NumaxLab\Lunar\Geslib\LunarGeslibServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            LunarPanelProvider::class,
            ConverterServiceProvider::class,
            LivewireServiceProvider::class,
            LunarGeslibServiceProvider::class,
        ];
    }
}
