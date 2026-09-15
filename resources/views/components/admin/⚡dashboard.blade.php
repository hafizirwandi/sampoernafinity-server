<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.admin', ['heading' => 'Dashboard'])]
#[Title('Admin Dashboard')]
class extends Component
{
    //
};
?>

<div class="space-y-6">
    <div class="rounded-2xl border border-border bg-surface p-5">
        <p class="text-sm text-text-muted">Selamat datang,</p>
        <p class="text-lg font-semibold">{{ auth()->user()->name }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-sm text-text-muted">Total Pengguna</p>
            <p class="mt-2 text-2xl font-semibold">{{ \App\Models\User::count() }}</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-sm text-text-muted">Trigger Aktif</p>
            <p class="mt-2 text-2xl font-semibold">0</p>
            <p class="mt-1 text-xs text-text-muted">Menyusul di fase berikutnya</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-sm text-text-muted">Action Terpasang</p>
            <p class="mt-2 text-2xl font-semibold">0</p>
            <p class="mt-1 text-xs text-text-muted">Menyusul di fase berikutnya</p>
        </div>
    </div>
</div>
