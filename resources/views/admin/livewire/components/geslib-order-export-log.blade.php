<div>
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            Geslib Order Export Logs
        </h1>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('adminhub.geslib.dashboard') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600">
                Back to Dashboard
            </a>
        </div>
    </header>

    <div class="mt-6 bg-white shadow rounded-lg dark:bg-gray-800">
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label for="searchOrderId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Order ID</label>
                    <input wire:model.debounce.300ms="searchOrderId" type="text" id="searchOrderId" placeholder="Order ID..."
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
                <div>
                    <label for="searchEndpoint" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Endpoint</label>
                    <input wire:model.debounce.300ms="searchEndpoint" type="text" id="searchEndpoint" placeholder="Endpoint..."
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Status</label>
                    <select wire:model="filterStatus" id="filterStatus"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
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
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Order ID</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Endpoint</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Message</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Timestamp</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $log->order_id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ Str::limit($log->geslib_endpoint_called, 40) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                               <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                    @if($log->status == 'success') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                                    @elseif($log->status == 'error') bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100
                                    @elseif($log->status == 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100 @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ Str::limit($log->message ?? 'N/A', 50) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <button wire:click="showDetailsModal({{ $log->id }})" class="text-primary-600 hover:text-primary-900 dark:text-primary-500 dark:hover:text-primary-400">
                                    Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-sm text-center text-gray-500 dark:text-gray-400">
                                No order export logs found.
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

    {{-- Modal for Log Details --}}
    @if($showingModal && $selectedLog)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-data x-cloak @keydown.escape.window="$wire.closeModal()">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="$wire.closeModal()"></div>
        <div class="relative w-full max-w-2xl p-4 mx-auto bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:p-6">
            <header class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Order Export Log Details (ID: {{ $selectedLog->id }})
                </h3>
                <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                    <x-hub::icon ref="x" class="w-5 h-5" /> {{-- Assuming x-hub::icon is available --}}
                </button>
            </header>
            <div class="mt-4 space-y-4">
                <dl class="sm:divide-y sm:divide-gray-200 dark:sm:divide-gray-700">
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $selectedLog->order_id }}</dd>
                    </div>
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Endpoint Called</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $selectedLog->geslib_endpoint_called }}</dd>
                    </div>
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $selectedLog->status }}</dd>
                    </div>
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Timestamp</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $selectedLog->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                     <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Message</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2"><pre class="whitespace-pre-wrap">{{ $selectedLog->message ?? 'N/A' }}</pre></dd>
                    </div>
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payload to Geslib</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2"><pre class="whitespace-pre-wrap">{{ $selectedLog->payload_to_geslib ?? 'N/A' }}</pre></dd>
                    </div>
                    <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payload from Geslib</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2"><pre class="whitespace-pre-wrap">{{ $selectedLog->payload_from_geslib ?? 'N/A' }}</pre></dd>
                    </div>
                </dl>
            </div>
            <footer class="pt-4 mt-4 border-t sm:flex sm:items-center sm:justify-end dark:border-gray-700">
                <button @click="$wire.closeModal()" type="button"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto">
                    Close
                </button>
            </footer>
        </div>
    </div>
    @endif
</div>
