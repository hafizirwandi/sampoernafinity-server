<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Masuk')]
class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun ini sudah dinonaktifkan.',
            ]);
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Masuk - {{ config('app.name') }}</title>

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
        <div class="flex min-h-screen flex-col lg:flex-row">
            {{-- Brand panel: always dark, same treatment as the landing page footer --}}
            <div class="relative hidden w-full flex-col justify-between bg-zinc-950 p-10 text-white lg:flex lg:max-w-md xl:max-w-lg">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-base font-bold">A</div>
                    <span class="text-lg font-semibold">{{ config('app.name') }}</span>
                </a>

                <div>
                    <h1 class="text-3xl font-bold leading-tight">
                        Platform trigger &amp; action untuk live TikTok yang lebih interaktif, menarik, dan profesional.
                    </h1>

                    <ul class="mt-8 space-y-4 text-sm text-white/80">
                        <li class="flex items-start gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-600/20 text-primary-400">
                                <x-heroicon-o-bolt class="h-4 w-4" />
                            </span>
                            <span class="pt-1.5">Overlay &amp; suara realtime setiap ada trigger baru</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-600/20 text-primary-400">
                                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                            </span>
                            <span class="pt-1.5">Soundboard, overlay, dan gift mapping dalam satu dashboard</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-600/20 text-primary-400">
                                <x-heroicon-o-adjustments-horizontal class="h-4 w-4" />
                            </span>
                            <span class="pt-1.5">Atur trigger &amp; action sepenuhnya dari dashboard admin</span>
                        </li>
                    </ul>
                </div>

                <p class="text-xs text-white/50">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </div>

            {{-- Form panel --}}
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-center justify-between p-6">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2 lg:hidden">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">A</div>
                        <span class="text-base font-semibold">{{ config('app.name') }}</span>
                    </a>
                    <x-ui.theme-toggle class="ml-auto" />
                </div>

                <div class="flex min-w-0 flex-1 items-center justify-center px-4 pb-10 sm:px-6">
                    <div class="w-full min-w-0 max-w-sm">
                        <h2 class="text-xl font-semibold sm:text-2xl">Masuk ke {{ config('app.name') }}</h2>
                        <p class="mt-1 text-sm text-text-muted">Belum punya akun? Daftar otomatis saat kamu pertama masuk.</p>

                        @if (session('status'))
                            <p class="mt-4 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 text-sm text-primary-700">
                                {{ session('status') }}
                            </p>
                        @endif

                        <div x-data="{ redirecting: '' }" class="mt-6 space-y-3">
                            <a
                                href="{{ route('social.redirect', 'google') }}"
                                x-on:click="redirecting = 'google'"
                                :class="redirecting && redirecting !== 'google' ? 'pointer-events-none opacity-50' : ''"
                                class="flex items-center justify-center gap-2 rounded-lg border border-border bg-surface px-4 py-2.5 text-sm font-medium text-text hover:bg-surface-alt"
                            >
                                <svg x-show="redirecting !== 'google'" class="h-5 w-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M23.52 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.28 1.48-1.13 2.73-2.4 3.58v2.98h3.89c2.28-2.1 3.56-5.19 3.56-8.8z" />
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.92l-3.89-2.98c-1.08.72-2.45 1.15-4.04 1.15-3.11 0-5.74-2.1-6.68-4.92H1.32v3.09C3.29 21.3 7.31 24 12 24z" />
                                    <path fill="#FBBC05" d="M5.32 14.33c-.24-.72-.38-1.49-.38-2.28s.14-1.56.38-2.28V6.68H1.32C.48 8.34 0 10.12 0 12s.48 3.66 1.32 5.32z" />
                                    <path fill="#EA4335" d="M12 4.75c1.76 0 3.34.61 4.58 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.29 2.7 1.32 6.68l4 3.09c.94-2.82 3.57-4.92 6.68-5.02z" />
                                </svg>
                                <svg x-show="redirecting === 'google'" style="display: none;" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="redirecting === 'google' ? 'Mengarahkan...' : 'Lanjutkan dengan Google'"></span>
                            </a>

                            <a
                                href="{{ route('social.redirect', 'discord') }}"
                                x-on:click="redirecting = 'discord'"
                                :class="redirecting && redirecting !== 'discord' ? 'pointer-events-none opacity-50' : ''"
                                class="flex items-center justify-center gap-2 rounded-lg bg-[#5865F2] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#4a54e1]"
                            >
                                <svg x-show="redirecting !== 'discord'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.317 4.369a19.79 19.79 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.369a.07.07 0 0 0-.032.027C.533 9.045-.32 13.58.099 18.057a.082.082 0 0 0 .031.056 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.1 13.1 0 0 1-1.872-.892.077.077 0 0 1-.008-.128c.126-.094.252-.192.372-.291a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.099.246.197.373.291a.077.077 0 0 1-.006.128 12.3 12.3 0 0 1-1.873.892.076.076 0 0 0-.04.106c.36.699.772 1.364 1.225 1.994a.077.077 0 0 0 .084.028 19.84 19.84 0 0 0 6-3.03.077.077 0 0 0 .032-.055c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.331c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.211 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />
                                </svg>
                                <svg x-show="redirecting === 'discord'" style="display: none;" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="redirecting === 'discord' ? 'Mengarahkan...' : 'Lanjutkan dengan Discord'"></span>
                            </a>
                        </div>

                        <div class="my-6 flex items-center gap-3">
                            <div class="h-px flex-1 bg-border"></div>
                            <span class="text-xs text-text-muted">atau</span>
                            <div class="h-px flex-1 bg-border"></div>
                        </div>

                        <form wire:submit="login" class="space-y-4">
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    wire:model="email"
                                    autocomplete="username"
                                    class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    placeholder="nama@contoh.com"
                                >
                                @error('email') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-medium">Kata Sandi</label>
                                <x-ui.password-input model="password" id="password" />
                                @error('password') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                            </div>

                            <label class="flex items-center gap-2 text-sm text-text-muted">
                                <input type="checkbox" wire:model="remember" class="rounded border-border text-primary-600 focus:ring-primary-600">
                                Ingat saya
                            </label>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="login"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="login">Masuk dengan Email</span>
                                <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                        </form>

                        <p class="mt-6 text-center text-xs text-text-muted">
                            Dengan masuk, kamu menyetujui
                            <a href="#" class="underline hover:text-text">Syarat &amp; Ketentuan</a>
                            dan
                            <a href="#" class="underline hover:text-text">Kebijakan Privasi</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
