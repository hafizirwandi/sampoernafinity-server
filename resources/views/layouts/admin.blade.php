<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ isset($heading) ? $heading . ' - ' . config('app.name') : config('app.name') }}</title>

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
    <body class="bg-bg text-text antialiased">
        <div
            x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
            x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))"
            class="min-h-screen lg:flex"
        >
            {{-- Mobile backdrop --}}
            <div
                x-show="sidebarOpen"
                x-on:click="sidebarOpen = false"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-64']"
                class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 transform flex-col overflow-hidden border-r border-border bg-surface transition-all duration-200 ease-in-out lg:sticky lg:inset-y-auto lg:top-0 lg:h-screen lg:translate-x-0"
            >
                <div class="flex h-16 shrink-0 items-center gap-2 border-b border-border px-5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-base font-bold text-white">
                        A
                    </div>
                    <span class="truncate text-base font-semibold tracking-tight" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ config('app.name') }}</span>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-4">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        title="Dashboard"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                    >
                        <x-heroicon-o-home class="h-5 w-5 shrink-0" />
                        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                    </a>

                    @can('manage users')
                        <a
                            href="{{ route('admin.users.index') }}"
                            title="Pengguna"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                        >
                            <x-heroicon-o-users class="h-5 w-5 shrink-0" />
                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Pengguna</span>
                        </a>
                    @endcan

                    @canany(['manage features', 'manage packages', 'manage subscriptions'])
                        <p
                            class="mt-4 mb-1 truncate px-3 text-[11px] font-semibold uppercase tracking-wide text-text-muted"
                            :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        >
                            Monetisasi
                        </p>

                        @can('manage features')
                            <a
                                href="{{ route('admin.features.index') }}"
                                title="Master Fitur"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.features.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                            >
                                <x-heroicon-o-puzzle-piece class="h-5 w-5 shrink-0" />
                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Master Fitur</span>
                            </a>
                        @endcan

                        @can('manage packages')
                            <a
                                href="{{ route('admin.packages.index') }}"
                                title="Paket Harga"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.packages.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                            >
                                <x-heroicon-o-tag class="h-5 w-5 shrink-0" />
                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Paket Harga</span>
                            </a>
                        @endcan

                        @can('manage subscriptions')
                            <a
                                href="{{ route('admin.subscriptions.index') }}"
                                title="Langganan Paket"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.subscriptions.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                            >
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0" />
                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Langganan Paket</span>
                            </a>
                        @endcan
                    @endcanany

                    @can('manage gifts')
                        <p
                            class="mt-4 mb-1 truncate px-3 text-[11px] font-semibold uppercase tracking-wide text-text-muted"
                            :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        >
                            Konten TikTok
                        </p>

                        <a
                            href="{{ route('admin.gifts.index') }}"
                            title="TikTok Gift & Stiker"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.gifts.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                        >
                            <x-heroicon-o-gift class="h-5 w-5 shrink-0" />
                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">TikTok Gift & Stiker</span>
                        </a>
                    @endcan

                    @canany(['manage admin users', 'manage roles'])
                        <p
                            class="mt-4 mb-1 truncate px-3 text-[11px] font-semibold uppercase tracking-wide text-text-muted"
                            :class="sidebarCollapsed ? 'lg:hidden' : ''"
                        >
                            Administrasi
                        </p>

                        @can('manage admin users')
                            <a
                                href="{{ route('admin.admin-users.index') }}"
                                title="Users Admin"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.admin-users.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                            >
                                <x-heroicon-o-user-circle class="h-5 w-5 shrink-0" />
                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Users Admin</span>
                            </a>
                        @endcan

                        @can('manage roles')
                            <a
                                href="{{ route('admin.roles.index') }}"
                                title="Role & Permission"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'bg-primary-600 text-white' : 'text-text-muted hover:bg-surface-alt hover:text-text' }}"
                            >
                                <x-heroicon-o-shield-check class="h-5 w-5 shrink-0" />
                                <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Role &amp; Permission</span>
                            </a>
                        @endcan
                    @endcanany
                </nav>

                <div class="shrink-0 border-t border-border">
                    {{-- Collapse/expand toggle (desktop only) --}}
                    <button
                        type="button"
                        x-on:click="sidebarCollapsed = !sidebarCollapsed"
                        :title="sidebarCollapsed ? 'Perluas menu' : 'Ciutkan menu'"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                        class="hidden w-full items-center gap-3 px-3 py-3 text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text lg:flex"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5 shrink-0 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">Ciutkan menu</span>
                    </button>
                </div>
            </aside>

            {{-- Main column --}}
            <div class="flex min-h-screen flex-1 flex-col lg:min-w-0">
                {{-- Topbar --}}
                <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-border bg-surface px-4 sm:px-6">
                    <button
                        type="button"
                        x-on:click="sidebarOpen = true"
                        class="-ml-1 inline-flex items-center justify-center rounded-lg p-2 text-text-muted hover:bg-surface-alt hover:text-text lg:hidden"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>

                    <h1 class="min-w-0 flex-1 truncate text-base font-semibold sm:text-lg">
                        {{ $heading ?? '' }}
                    </h1>

                    <x-ui.theme-toggle />

                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            x-on:click="open = !open"
                            x-on:click.outside="open = false"
                            class="flex items-center gap-2 rounded-lg p-1.5 pr-2 hover:bg-surface-alt"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                                {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                            </div>
                            <span class="hidden text-sm font-medium sm:inline">{{ auth()->user()->name ?? '' }}</span>
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            style="display: none;"
                            class="absolute right-0 z-30 mt-2 w-56 rounded-xl border border-border bg-surface p-1 shadow-lg"
                        >
                            <div class="px-3 py-2 text-sm">
                                <p class="truncate font-medium">{{ auth()->user()->name ?? '' }}</p>
                                <p class="truncate text-xs text-text-muted">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="my-1 border-t border-border"></div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-text-muted hover:bg-surface-alt hover:text-text">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-4 py-6 sm:px-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
