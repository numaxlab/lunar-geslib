<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.GeslibFileImportStatsWidget />
            <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.GeslibOrderSyncStatsWidget />
        </div>

        <div class="mt-4"> {{-- Corrected class to mt-4 for spacing --}}
            <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.GeslibConfigCheckWidget />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">
            <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.RecentFileImportErrorsWidget />
            <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.RecentOrderSyncErrorsWidget />
        </div>
    </div>
</x-filament-panels::page>
