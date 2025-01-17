<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>  
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
        use Inertia\Inertia;
        $meta = $page['props']['meta'] ?? null;
        $title = isset($meta['og:title']) 
            ? $meta['og:title'] . ' ' . config('app.name', 'EstateMarket')
            : config('app.name', 'EstateMarket');

        @endphp
        
        <!-- <title inertia>{{ config('app.name', 'EstateMaket') }}</title> -->
        <title>{{ $title }}</title>
        <meta name="description" content="{{ $meta['og:description'] }}">
        <meta property="og:title" content="{{ $meta['og:title'] }}">
        <meta property="og:description" content="{{ $meta['og:description'] }}">
        <meta property="og:image" content="{{ $meta['og:image'] ?? null }}">
        <meta property="og:url" content="{{ $meta['og:url'] }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
