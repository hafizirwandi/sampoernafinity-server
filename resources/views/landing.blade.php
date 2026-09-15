<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="{{ config('app.name') }} — platform trigger & action untuk live TikTok. Atur overlay, suara, dan reaksi otomatis setiap ada yang join, komen, atau kirim gift.">

        <title>{{ config('app.name') }} — Toolkit Trigger &amp; Action untuk Live TikTok</title>

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

        {{-- Navbar --}}
        <header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-border bg-surface/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">A</div>
                    <span class="text-base font-semibold tracking-tight">{{ config('app.name') }}</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-text-muted md:flex">
                    <a href="#fitur" class="hover:text-text">Fitur</a>
                    <a href="#cara-kerja" class="hover:text-text">Cara Kerja</a>
                    <a href="#harga" class="hover:text-text">Harga</a>
                    <a href="#faq" class="hover:text-text">FAQ</a>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <x-ui.theme-toggle />
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-text-muted hover:text-text">Masuk</a>
                        <a href="{{ route('login') }}" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Buka Dashboard</a>
                    @endauth
                </div>

                <button type="button" x-on:click="mobileOpen = !mobileOpen" class="inline-flex items-center justify-center rounded-lg p-2 text-text-muted hover:bg-surface-alt md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>
            </div>

            <div x-show="mobileOpen" x-transition style="display: none;" class="space-y-1 border-t border-border bg-surface px-4 py-3 md:hidden">
                <a href="#fitur" class="block rounded-lg px-3 py-2 text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text">Fitur</a>
                <a href="#cara-kerja" class="block rounded-lg px-3 py-2 text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text">Cara Kerja</a>
                <a href="#harga" class="block rounded-lg px-3 py-2 text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text">Harga</a>
                <a href="#faq" class="block rounded-lg px-3 py-2 text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text">FAQ</a>
                <div class="my-2 border-t border-border"></div>
                <div class="flex items-center justify-between px-3 py-1">
                    <span class="text-sm text-text-muted">Tema</span>
                    <x-ui.theme-toggle />
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="block rounded-lg bg-primary-600 px-3 py-2 text-center text-sm font-semibold text-white">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-center text-sm font-medium text-text-muted hover:bg-surface-alt hover:text-text">Masuk</a>
                    <a href="{{ route('login') }}" class="block rounded-lg bg-primary-600 px-3 py-2 text-center text-sm font-semibold text-white">Buka Dashboard</a>
                @endauth
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-surface-alt">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:items-center lg:py-28">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700">
                        🔴 Toolkit trigger &amp; action untuk live TikTok
                    </span>

                    <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                        Bikin Live TikTok-mu ke
                        <span class="relative inline-block text-primary-600">
                            Level Berikutnya
                            <svg class="absolute -bottom-1 left-0 w-full text-primary-300" viewBox="0 0 200 8" preserveAspectRatio="none" fill="none">
                                <path d="M1 5.5C40 1.5 160 1.5 199 5.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                        </span>
                    </h1>

                    <p class="mt-5 max-w-lg text-base text-text-muted sm:text-lg">
                        Atur overlay, suara, dan reaksi otomatis setiap kali ada penonton yang join, komentar, atau kirim gift — semua trigger &amp; action diatur dari satu dashboard.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-900/10 hover:bg-primary-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download Aplikasi
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-border px-5 py-3 text-sm font-semibold text-text hover:bg-surface">
                            Buka Web App
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-text-muted">Tersedia untuk Windows &amp; browser modern. Sedang dalam pengembangan aktif.</p>
                </div>

                <div class="relative mx-auto w-full max-w-sm lg:max-w-none">
                    <div class="rounded-2xl border border-border bg-surface p-5 shadow-xl">
                        <p class="text-xs font-medium text-text-muted">Contoh tampilan overlay live</p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-xl bg-surface-alt p-3 text-center">
                                <p class="text-lg font-bold">1.234</p>
                                <p class="text-[11px] text-text-muted">Viewers</p>
                            </div>
                            <div class="rounded-xl bg-surface-alt p-3 text-center">
                                <p class="text-lg font-bold">89</p>
                                <p class="text-[11px] text-text-muted">Gift</p>
                            </div>
                            <div class="rounded-xl bg-primary-50 p-3 text-center">
                                <p class="text-lg font-bold text-primary-700">ON</p>
                                <p class="text-[11px] text-primary-600">Trigger Aktif</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -right-3 -top-4 rounded-xl border border-border bg-surface px-3 py-2 text-xs font-medium shadow-lg sm:-right-6">
                        ✅ Overlay aktif!
                    </div>
                    <div class="absolute -bottom-4 -left-3 rounded-xl border border-border bg-surface px-3 py-2 text-xs font-medium shadow-lg sm:-left-6">
                        🎮 Trigger gift diterima
                    </div>
                </div>
            </div>
        </section>

        {{-- Value strip --}}
        <section class="border-y border-border bg-surface">
            <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 py-8 text-center sm:px-6 lg:grid-cols-4">
                <div>
                    <p class="text-xl font-bold text-primary-600">Realtime</p>
                    <p class="mt-1 text-xs text-text-muted">Trigger diproses lokal, tanpa lag</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-primary-600">Fleksibel</p>
                    <p class="mt-1 text-xs text-text-muted">Kombinasi trigger &amp; action bebas diatur</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-primary-600">Overlay + Suara</p>
                    <p class="mt-1 text-xs text-text-muted">Dua jenis action utama, terus bertambah</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-primary-600">Bayar Sesuai Pakai</p>
                    <p class="mt-1 text-xs text-text-muted">Aktifkan trigger per kebutuhan streammu</p>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="fitur" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
            <div class="grid gap-4 sm:grid-cols-2 sm:items-end">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-primary-600">Fitur Unggulan</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                        Semua yang kamu butuhkan, <span class="text-primary-600">satu tempat.</span>
                    </h2>
                </div>
                <p class="text-sm text-text-muted sm:text-right">
                    Trigger dari TikTok live-mu, dipetakan ke action yang kamu tentukan sendiri lewat dashboard admin.
                </p>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl bg-primary-600 p-6 text-white sm:col-span-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-lg">💬</div>
                    <h3 class="mt-4 text-lg font-semibold">Overlay Live TikTok</h3>
                    <p class="mt-1 text-sm text-white/85">Tampilkan overlay interaktif langsung saat live TikTok berjalan, temanya bisa disesuaikan lewat admin.</p>
                </div>

                <div class="relative rounded-2xl border border-border bg-surface p-6">
                    <span class="absolute right-4 top-4 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700">Segera</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-lg">🔊</div>
                    <h3 class="mt-4 text-sm font-semibold">Soundboard Otomatis</h3>
                    <p class="mt-1 text-sm text-text-muted">Putar efek suara otomatis begitu trigger tertentu terjadi, misalnya gift atau komentar spesifik.</p>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6">
                    <p class="text-3xl font-bold text-primary-600">100%</p>
                    <p class="mt-1 text-sm text-text-muted">diproses lokal di aplikasi kamu, jadi tetap responsif walau trigger lagi ramai-ramainya.</p>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-lg">🎁</div>
                    <h3 class="mt-4 text-sm font-semibold">Gift Value Mapping</h3>
                    <p class="mt-1 text-sm text-text-muted">Atur reaksi berbeda berdasarkan jenis dan nilai gift yang diterima.</p>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-lg">✨</div>
                    <h3 class="mt-4 text-sm font-semibold">Custom Trigger &amp; Action</h3>
                    <p class="mt-1 text-sm text-text-muted">Buat kombinasi trigger dan action sendiri sesuai kebutuhan live kamu.</p>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-lg">🔗</div>
                    <h3 class="mt-4 text-sm font-semibold">Integrasi Platform Donasi</h3>
                    <p class="mt-1 text-sm text-text-muted">Direncanakan terhubung dengan Saweria, Trakteer, dan platform donasi populer lainnya.</p>
                </div>

                <div class="flex flex-col justify-between rounded-2xl bg-zinc-950 p-6 text-white sm:col-span-2 lg:col-span-1">
                    <p class="text-sm text-white/70">Trigger &amp; action terus bertambah tiap update.</p>
                    <a href="#harga" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-400 hover:text-primary-300">
                        Lihat roadmap fitur →
                    </a>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="cara-kerja" class="border-y border-border bg-surface-alt">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div class="text-center">
                    <span class="text-xs font-semibold uppercase tracking-wide text-primary-600">Cara Kerja</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">4 langkah mudah untuk <span class="text-primary-600">memulai</span></h2>
                </div>

                <div class="mx-auto mt-10 max-w-2xl space-y-8">
                    @foreach ([
                        ['icon' => '⬇️', 'title' => 'Download atau Buka via Web', 'desc' => 'Unduh aplikasi desktop untuk Windows, atau langsung gunakan versi web tanpa perlu install.'],
                        ['icon' => '🔌', 'title' => 'Hubungkan Akun TikTok', 'desc' => 'Sambungkan username TikTok live kamu ke dashboard admin.'],
                        ['icon' => '⚙️', 'title' => 'Atur Trigger & Action', 'desc' => 'Pilih trigger yang mau diaktifkan, lalu tentukan overlay atau suara yang muncul.'],
                        ['icon' => '▶️', 'title' => 'Mulai Live', 'desc' => 'Trigger berjalan otomatis secara realtime begitu live TikTok kamu dimulai.'],
                    ] as $i => $step)
                        <div class="flex gap-4 rounded-2xl border border-border bg-surface p-5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-sm font-bold text-white">
                                {{ $i + 1 }}
                            </div>
                            <div>
                                <p class="font-semibold">{{ $step['icon'] }} {{ $step['title'] }}</p>
                                <p class="mt-1 text-sm text-text-muted">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-700">Buka Dashboard</a>
                    <a href="#fitur" class="inline-flex items-center justify-center rounded-lg border border-border px-5 py-3 text-sm font-semibold text-text hover:bg-surface">Pelajari Fitur</a>
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="harga" class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24">
            <div class="text-center">
                <span class="text-xs font-semibold uppercase tracking-wide text-primary-600">Harga</span>
                <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Pilih trigger sesuai kebutuhan</h2>
                <p class="mt-2 text-sm text-text-muted">Bayar hanya untuk trigger yang kamu pakai, saldo bisa ditambah kapan saja. Harga di bawah masih contoh.</p>
            </div>

            <div class="mt-8 rounded-2xl border border-border bg-surface p-6 sm:p-8">
                @foreach ([
                    ['name' => 'Trigger Join', 'price' => 'Rp 5.000'],
                    ['name' => 'Trigger Comment', 'price' => 'Rp 10.000'],
                    ['name' => 'Trigger Follow', 'price' => 'Rp 5.000'],
                    ['name' => 'Trigger Gift', 'price' => 'Rp 15.000'],
                    ['name' => 'Action Overlay Custom', 'price' => 'Rp 20.000'],
                ] as $item)
                    <div class="flex items-center justify-between border-b border-border py-3 text-sm last:border-0">
                        <span>{{ $item['name'] }}</span>
                        <span class="font-medium text-text-muted">{{ $item['price'] }}/bulan</span>
                    </div>
                @endforeach

                <div class="mt-4 flex items-center justify-between rounded-lg bg-surface-alt px-4 py-3">
                    <span class="text-sm font-semibold">Total kalau semua diaktifkan</span>
                    <span class="text-sm font-bold text-primary-600">Rp 55.000/bulan</span>
                </div>

                <a href="{{ route('login') }}" class="mt-6 block rounded-lg bg-primary-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-primary-700">
                    Isi Saldo &amp; Aktifkan
                </a>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="border-t border-border bg-surface-alt">
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24">
                <div class="text-center">
                    <span class="text-xs font-semibold uppercase tracking-wide text-primary-600">FAQ</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Ada yang ingin <span class="text-primary-600">ditanyakan?</span></h2>
                </div>

                <div class="mt-8 space-y-3">
                    @foreach ([
                        ['q' => 'Apa itu ' . config('app.name') . '?', 'a' => 'Platform yang menghubungkan event live TikTok-mu (join, komentar, gift, dst) ke overlay, suara, atau aksi otomatis lainnya.'],
                        ['q' => 'Trigger apa saja yang didukung?', 'a' => 'Dimulai dari join, lalu bertahap ditambah comment, gift, follow, like, dan share.'],
                        ['q' => 'Perlu install aplikasi atau bisa lewat browser?', 'a' => 'Dua-duanya direncanakan tersedia: aplikasi desktop untuk Windows dan akses lewat web.'],
                        ['q' => 'Apakah bisa dipakai gratis?', 'a' => 'Dashboard dasar gratis, trigger tertentu berbayar per bulan sesuai yang kamu aktifkan.'],
                    ] as $item)
                        <div x-data="{ open: false }" class="rounded-xl border border-border bg-surface">
                            <button type="button" x-on:click="open = !open" class="flex w-full items-center justify-between px-4 py-3.5 text-left text-sm font-medium">
                                {{ $item['q'] }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 shrink-0 transition-transform" :class="open && 'rotate-45'">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 text-sm text-text-muted">
                                {{ $item['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-zinc-950 text-zinc-400">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div class="col-span-2">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">A</div>
                            <span class="text-base font-semibold text-white">{{ config('app.name') }}</span>
                        </div>
                        <p class="mt-3 max-w-xs text-sm">Platform trigger &amp; action untuk live TikTok yang lebih interaktif.</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Navigasi</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="#fitur" class="hover:text-white">Fitur</a></li>
                            <li><a href="#cara-kerja" class="hover:text-white">Cara Kerja</a></li>
                            <li><a href="#harga" class="hover:text-white">Harga</a></li>
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Lainnya</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="#faq" class="hover:text-white">FAQ</a></li>
                            <li><a href="{{ route('login') }}" class="hover:text-white">Masuk</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 border-t border-zinc-800 pt-6 text-xs">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Sedang dalam tahap pengembangan.
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
