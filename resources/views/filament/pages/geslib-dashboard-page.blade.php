<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-y-8">
            <section>
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Ficheros de intercambio
                </h2>
                <div class="grid grid-cols-1 gap-4 py-8 lg:grid-cols-2">
                    @livewire(NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\GeslibFileImportStatsWidget::class)
                    @livewire(NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\RecentFileImportErrorsWidget::class)
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Sincronización de pedidos
                </h2>
                <div class="grid grid-cols-1 gap-4 py-8 lg:grid-cols-2">
                    @livewire(NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\GeslibOrderSyncStatsWidget::class)
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
