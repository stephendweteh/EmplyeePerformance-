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

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @if (session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                        <div class="rounded-xl bg-emerald-600 text-white px-4 py-3 shadow-md">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
                    <!-- Mobile Bottom Navigation Bar -->
                    <div class="sm:hidden fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur pb-[max(env(safe-area-inset-bottom),0.4rem)]">
                        <div class="relative h-16 grid grid-cols-5">
                            <a href="{{ Auth::user()->isEmployee() ? route('employee.dashboard') : route('employer.dashboard') }}" class="flex flex-col items-center justify-center gap-1 text-[11px] {{ request()->routeIs('employee.dashboard') || request()->routeIs('employer.dashboard') ? 'text-sky-600' : 'text-slate-500' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M3 10.5 12 3l9 7.5"></path>
                                    <path d="M5 9.5V21h14V9.5"></path>
                                </svg>
                                <span>{{ __('Home') }}</span>
                            </a>
                            <a href="{{ route('announcements.index') }}" class="flex flex-col items-center justify-center gap-1 text-[11px] {{ request()->routeIs('announcements.index') ? 'text-sky-600' : 'text-slate-500' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16"></path>
                                    <path d="M4 12h16"></path>
                                    <path d="M4 18h10"></path>
                                </svg>
                                <span>{{ __('Live') }}</span>
                            </a>
                            <a href="{{ route('activity-logs.index') }}" class="flex flex-col items-center justify-center gap-1 text-[11px] {{ request()->routeIs('activity-logs.index') ? 'text-sky-600' : 'text-slate-500' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 12h4l2-4 4 8 2-4h4"></path>
                                </svg>
                                <span>{{ __('Feed') }}</span>
                            </a>
                            <a href="{{ Auth::user()->isSuperAdmin() ? route('admin.users.index') : (Auth::user()->isEmployee() ? route('employee.dashboard') : route('teams.index')) }}" class="flex flex-col items-center justify-center gap-1 text-[11px] {{ (Auth::user()->isSuperAdmin() ? request()->routeIs('admin.users.*') : (Auth::user()->isEmployee() ? request()->routeIs('employee.dashboard') : request()->routeIs('teams.*'))) ? 'text-sky-600' : 'text-slate-500' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>{{ Auth::user()->isSuperAdmin() ? __('Users') : (Auth::user()->isEmployee() ? __('My Day') : __('Teams')) }}</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center gap-1 text-[11px] {{ request()->routeIs('profile.*') ? 'text-sky-600' : 'text-slate-500' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 21a8 8 0 0 1 16 0"></path>
                                </svg>
                                <span>{{ __('Profile') }}</span>
                            </a>
                        </div>
                    </div>
        </div>
    </body>
</html>
