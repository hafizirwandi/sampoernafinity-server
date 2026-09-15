<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <script>
            (function () {
                var stored = localStorage.getItem('theme');
                var theme = stored ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-bg text-text min-h-screen antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">
            <div class="mb-8 flex flex-col items-center gap-2">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary-600 text-2xl font-bold text-white shadow-lg shadow-primary-900/20">
                    A
                </div>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
            </div>

            <div class="w-full max-w-sm rounded-2xl border border-border bg-surface p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>

            <x-ui.theme-toggle class="mt-8" />
        </div>

        @livewireScripts
    </body>
</html>
