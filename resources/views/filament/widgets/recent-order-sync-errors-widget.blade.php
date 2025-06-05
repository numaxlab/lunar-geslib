<x-filament-widgets::widget class="filament-geslib-recent-sync-errors-widget">
    <x-filament::section>
        <x-slot name="heading">
            Recent Order Sync Errors
        </x-slot>
         <x-slot name="headerEnd" >
             @if($recentSyncErrors->isNotEmpty())
                <x-filament::link :href="app($getTableResource())::getUrl('index', ['tableFilters[status][value]' => 'error'])">
                    View all errors
                </x-filament::link>
            @endif
        </x-slot>

        @if($recentSyncErrors->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No recent order sync errors. (UI Shell - Functionality pending)
            </p>
        @else
            <div class="space-y-3">
                @foreach ($recentSyncErrors as $error)
                    <div class="p-3 bg-danger-50 rounded-lg dark:bg-danger-500/10">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-danger-700 dark:text-danger-300">
                                Order ID: {{ $error->order_id }}
                            </p>
                             <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $error->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                           Endpoint: {{ $error->geslib_endpoint_called }} <br>
                           Message: {{ Str::limit($error->message ?? 'No specific error message.', 100) }}
                        </p>
                        <div class="mt-2">
                             <x-filament::link :href="app($getTableResource())::getUrl('index', ['tableFilters[status][value]' => 'error', 'tableSearchQuery' => $error->order_id])" size="xs">
                                View Log
                            </x-filament::link>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
