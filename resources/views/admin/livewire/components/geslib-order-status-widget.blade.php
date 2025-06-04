<div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
        Geslib Sync Status
    </h3>
    <div class="mt-3 space-y-2">
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
            <div>
                <a href="{{ $logDetailsLink }}"
                   class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400">
                    View Sync History &rarr;
                </a>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No Geslib sync information for this order.
            </p>
        @endif
    </div>
</div>
