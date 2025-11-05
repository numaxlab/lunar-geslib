<div>
    <span class="text-sm  font-medium leading-6 text-gray-950 dark:text-white">
        Código:
    </span>
    @if ($getRecord()->geslib_code === null)
        --
    @else
        {{ $getRecord()->geslib_code }}
    @endif
</div>