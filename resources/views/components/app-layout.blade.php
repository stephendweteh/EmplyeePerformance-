@props(['header'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=roboto:300,400,500,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100">
        <div class="min-h-screen bg-gradient-to-br from-slate-100 via-sky-50 to-amber-50">
            @include('layouts.navigation')

            @if ($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 text-xl font-semibold text-gray-800">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
