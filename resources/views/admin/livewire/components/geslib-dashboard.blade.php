<div>
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            Geslib Integration Dashboard
        </h1>
    </header>

    <div class="mt-6 space-y-6">
        <!-- File Import Section -->
        <section class="p-6 bg-white shadow rounded-lg dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">File Import Status</h2>
            <div class="mt-1">
                 <a href="{{ route('adminhub.geslib.file-import-log') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400">
                    View Detailed Logs &rarr;
                </a>
            </div>
            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last Import Run Status:</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $lastImportRunStatus }} at {{ $lastImportRunTimestamp }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Files Processed:</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $totalFilesProcessed }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Records Created (Approx.):</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $totalRecordsCreated }}</p>
                </div>
                {{-- Add more stats as they become available/implementable --}}
                {{--
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Records Updated (Approx.):</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $totalRecordsUpdated }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Records Deleted (Approx.):</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $totalRecordsDeleted }}</p>
                </div>
                --}}

                @if(!empty($recentImportErrors) && $recentImportErrors->count() > 0)
                    <div class="pt-4">
                        <h3 class="text-md font-medium text-gray-900 dark:text-white">Recent Import Errors:</h3>
                        <ul class="mt-2 space-y-2">
                            @foreach($recentImportErrors as $error)
                                <li class="p-3 bg-red-50 rounded-md dark:bg-red-900/50">
                                    <p class="text-sm text-red-700 dark:text-red-300">
                                        File: {{ $error->name ?? 'N/A' }} (ID: {{ $error->id }}) - {{ $error->processed_at ? $error->processed_at->toDateTimeString() : $error->created_at->toDateTimeString() }}
                                    </p>
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        Message: {{ $error->notes ?? $error->message ?? 'No specific error message.' }} {{-- Assuming notes or message field exists --}}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400">No recent import errors.</p>
                @endif
            </div>
        </section>

        <!-- Order Export Section -->
        <section class="p-6 bg-white shadow rounded-lg dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Order Export Status</h2>
            <div class="mt-1">
                <a href="{{ route('adminhub.geslib.order-export-log') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400">
                    View Detailed Logs &rarr;
                </a>
            </div>
            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Orders Awaiting Sync:</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $ordersAwaitingSync }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Orders Successfully Synced:</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $ordersSuccessfullySynced }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Orders Failed Sync:</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $ordersFailedSync }}</p>
                </div>

                @if(!empty($recentOrderSyncErrors) && $recentOrderSyncErrors->count() > 0)
                    <div class="pt-4">
                        <h3 class="text-md font-medium text-gray-900 dark:text-white">Recent Order Sync Errors:</h3>
                        <ul class="mt-2 space-y-2">
                            @foreach($recentOrderSyncErrors as $error)
                                <li class="p-3 bg-red-50 rounded-md dark:bg-red-900/50">
                                    <p class="text-sm text-red-700 dark:text-red-300">
                                        Order ID: {{ $error->order_id }} - {{ $error->created_at->toDateTimeString() }}
                                    </p>
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        Endpoint: {{ $error->geslib_endpoint_called }} <br>
                                        Message: {{ $error->message ?? 'No specific error message.' }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @elseif ($ordersAwaitingSync == 0 && $ordersSuccessfullySynced == 0 && $ordersFailedSync == 0)
                     <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">No order sync data available.</p>
                @else
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400">No recent order sync errors.</p>
                @endif
            </div>
        </section>

        <!-- Configuration Check Section -->
        <section class="p-6 bg-white shadow rounded-lg dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Configuration Check</h2>
            <div class="mt-4 space-y-2">
                @if(!empty($configValues))
                    @foreach($configValues as $key => $value)
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $key }}:</p>
                            @if($value === 'Set' || (is_string($value) && str_starts_with($value, 'Set ')))
                                <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    {{ $value }}
                                </span>
                            @elseif($value === 'Not Set' || (is_string($value) && str_starts_with($value, 'Not Set')))
                                <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">
                                    {{ $value }} - Needs attention
                                </span>
                            @else
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $value }}</span>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Could not load Geslib configuration.</p>
                @endif
            </div>
        </section>
    </div>
</div>
