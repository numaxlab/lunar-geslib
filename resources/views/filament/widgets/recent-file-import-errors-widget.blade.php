<x-filament-widgets::widget class="filament-geslib-recent-errors-widget">
    <x-filament::section>
        <x-slot name="heading">
            Recent File Import Errors
        </x-slot>
        <x-slot name="headerEnd" >
             @if($recentErrors->isNotEmpty())
                <x-filament::link :href="app($getTableResource())::getUrl('index', ['tableFilters[status][value]' => 'error'])">
                    View all errors
                </x-filament::link>
            @endif
        </x-slot>

        @if($recentErrors->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No recent file import errors.
            </p>
        @else
            <div class="space-y-3">
                @foreach ($recentErrors as $error)
                    <div class="p-3 bg-danger-50 rounded-lg dark:bg-danger-500/10">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-danger-700 dark:text-danger-300">
                                {{ $error->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $error->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                            {{ Str::limit($error->notes ?? 'No specific error message.', 100) }}
                        </p>
                        <div class="mt-2">
                             <x-filament::link :href="app($getTableResource())::getUrl('index', ['tableSearchQuery' => $error->name])" size="xs">
                                View Log
                            </x-filament::link>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
