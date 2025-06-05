<div>
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            Geslib File Import Logs
        </h1>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('adminhub.geslib.dashboard') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600">
                Back to Dashboard
            </a>
        </div>
    </header>

    @if (session()->has('message'))
        <div class="p-4 mt-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-700 dark:text-green-100" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mt-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-700 dark:text-red-100" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 bg-white shadow rounded-lg dark:bg-gray-800">
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="searchFilename" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Filename</label>
                    <input wire:model.debounce.300ms="searchFilename" type="text" id="searchFilename" placeholder="Enter filename..."
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Status</label>
                    <select wire:model="filterStatus" id="filterStatus"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="processed">Processed</option>
                        <option value="error">Error</option>
                        <option value="archived">Archived</option> {{-- Assuming these statuses exist --}}
                    </select>
                </div>
                <div>
                    <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date From</label>
                    <input wire:model="filterDateFrom" type="date" id="filterDateFrom"
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
                <div>
                    <label for="filterDateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date To</label>
                    <input wire:model="filterDateTo" type="date" id="filterDateTo"
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">ID</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Filename</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Timestamp</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Notes/Records</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $log->id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $log->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                    @if($log->status == 'processed') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                                    @elseif($log->status == 'error') bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100
                                    @elseif($log->status == 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                                    @elseif($log->status == 'processing') bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100 @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ Str::limit($log->notes ?? 'N/A', 50) }}
                                {{-- Assuming 'records_created', 'records_updated' fields might exist --}}
                                {{-- @if(isset($log->records_created)) Records: C:{{$log->records_created}} U:{{$log->records_updated}} D:{{$log->records_deleted}} @endif --}}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                {{-- Basic details view could be a modal later --}}
                                {{-- <button wire:click="showDetailsModal({{ $log->id }})" class="text-primary-600 hover:text-primary-900 dark:text-primary-500 dark:hover:text-primary-400">Details</button> --}}
                                @if($log->status == 'error')
                                    <button wire:click="reprocessFile({{ $log->id }})" wire:loading.attr="disabled"
                                            class="ml-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Reprocess
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-sm text-center text-gray-500 dark:text-gray-400">
                                No file import logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
