<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Geslib Sync Status
        </x-slot>

        @if($record && property_exists($record, 'id') && $record->id)
            <div class="space-y-2">
                @if($hasLogs)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</span>
                        <span class="ml-1 text-sm font-semibold
                            @if($syncStatus == 'Success') text-green-600 dark:text-green-400
                            @elseif($syncStatus == 'Error') text-red-600 dark:text-red-400
                            @elseif($syncStatus == 'Pending') text-yellow-600 dark:text-yellow-400
                            @else text-gray-700 dark:text-gray-300 @endif">
                            {{ $syncStatus }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Sync Attempt:</span>
                        <span class="ml-1 text-sm text-gray-700 dark:text-gray-300">{{ $lastSyncTimestamp }}</span>
                    </div>
                    @if($geslibIdentifier !== 'N/A')
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Geslib ID:</span>
                        <span class="ml-1 text-sm text-gray-700 dark:text-gray-300">{{ $geslibIdentifier }}</span>
                    </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $syncStatus }} {{-- Will show "Not Synced Yet" or "Order context not available" --}}
                    </p>
                @endif
                <div>
                    <a href="{{ $logDetailsLink }}"
                       class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400">
                        View Sync History &rarr;
                    </a>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Order information not available for Geslib sync status.
            </p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
