<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.member', ['heading' => 'Dashboard'])]
#[Title('Dashboard')]
class extends Component
{
    //
};
?>

<div class="space-y-6">
    <div class="rounded-2xl border border-border bg-surface p-5">
        <p class="text-sm text-text-muted">Halo,</p>
        <p class="text-lg font-semibold">{{ auth()->user()->name }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="font-semibold">Paket Saya</p>
            <p class="mt-1 text-sm text-text-muted">Belum ada trigger yang diaktifkan.</p>
            <span class="mt-3 inline-block rounded-full bg-surface-alt px-2.5 py-1 text-xs text-text-muted">Segera hadir</span>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="font-semibold">Manual Book</p>
            <p class="mt-1 text-sm text-text-muted">Panduan pemakaian aplikasi &amp; dashboard.</p>
            <span class="mt-3 inline-block rounded-full bg-surface-alt px-2.5 py-1 text-xs text-text-muted">Segera hadir</span>
        </div>
    </div>
</div>
