@props(['log'])

<div>
    @if (!empty($log) && is_array($log))
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 bg-white text-sm dark:divide-gray-700 dark:bg-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col"
                        class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900 dark:text-gray-200">
                        Nivel
                    </th>
                    <th scope="col"
                        class="whitespace-nowrap px-4 py-2 text-left font-medium text-gray-900 dark:text-gray-200">
                        Mensaje
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($log as $item)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 dark:text-gray-200">
                            {{-- Podes engadir estilos segundo o tipo de log --}}
                            @php
                                $colorClass = match($item['level'] ?? \NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract::LEVEL_INFO) {
                                    \NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract::LEVEL_ERROR => 'text-red-600 dark:text-red-400',
                                    \NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract::LEVEL_WARNING => 'text-yellow-600 dark:text-yellow-400',
                                    default => 'text-blue-600 dark:text-blue-400',
                                };
                            @endphp
                            <span class="{{ $colorClass }}">{{ $item['level'] ?? 'N/A' }}</span>
                        </td>
                        <td class="whitespace-normal px-4 py-2 text-black dark:text-white">
                            {{ $item['message'] ?? 'No hay mensaje' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-gray-500 dark:text-gray-400">No hay registros de log.</p>
    @endif
</div>