<?php

namespace Tests\Providers;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;
use NumaxLab\Lunar\Geslib\Admin\Filament\GeslibPlugin;

class LunarPanelTestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::panel(function ($panel) {
            return $panel->plugins([
                GeslibPlugin::make(),
            ]);
        })->register();
    }
}
