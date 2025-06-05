<x-filament-widgets::widget class="filament-geslib-config-check-widget">
    <x-filament::section>
        <x-slot name="heading">
            Configuration Check
        </x-slot>

        <div class="space-y-2">
            @if(!empty($configValues))
                @foreach($configValues as $key => $value)
                    <div class="flex items-center justify-between p-2 rounded-md @if($value['status'] === 'Error') bg-danger-50 dark:bg-danger-500/10 @elseif($value['status'] === 'Warning') bg-warning-50 dark:bg-warning-500/10 @else bg-gray-50 dark:bg-gray-700/20 @endif">
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $key }}:</dt>
                        <dd class="text-sm
                            @if($value['status'] === 'Error') text-danger-700 dark:text-danger-300
                            @elseif($value['status'] === 'Warning') text-warning-700 dark:text-warning-300
                            @else text-gray-900 dark:text-gray-100 @endif">
                            {{ $value['value'] }}
                            @if($value['status'] === 'Error')
                                <span class="font-semibold"> (Needs attention)</span>
                            @elseif($value['status'] === 'Warning')
                                <span class="font-semibold"> (Review recommended)</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Could not load Geslib configuration for display.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
