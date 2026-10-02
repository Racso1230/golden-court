<!DOCTYPE html>
<html lang="{{ config('seo.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="{{ config('seo.theme_color') }}">

        {{-- Paint the page white before the stylesheet arrives. --}}
        <style>
            html {
                background-color: #ffffff;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            {{-- Without SSR the server-built head tags are printed here; the client adopts them by their data-inertia keys. --}}
            @foreach ($page['props']['head'] ?? [] as $tag)
                {!! $tag !!}
            @endforeach
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
