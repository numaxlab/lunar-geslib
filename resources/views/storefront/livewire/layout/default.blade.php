<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ !empty($title) ? $title . ' | ' . config('app.name') : config('app.name') }}</title>

    @vite('resources/css/app.css')
    @if (isset($head))
        {{ $head }}
    @endif
</head>
<body>
<x-lunar-geslib::header/>

<main>
    <div class="container mx-auto px-4">
        {{ $slot }}
    </div>
</main>

<x-lunar-geslib::footer/>

@vite('resources/js/app.js')
@if (isset($scripts))
    {{ $scripts }}
@endif
</body>
</html>
