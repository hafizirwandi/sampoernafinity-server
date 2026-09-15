<?php

use App\Models\Feature;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.admin', ['heading' => 'Master Fitur'])]
#[Title('Master Fitur')]
class extends Component
{
    public function with(): array
    {
        return [
            'features' => Feature::query()->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    public function toggleFree(int $featureId): void
    {
        $feature = Feature::findOrFail($featureId);
        $feature->update(['is_free' => ! $feature->is_free]);
    }
};
?>

<div class="space-y-4">
    <div class="rounded-2xl border border-border bg-surface p-4 text-sm text-text-muted">
        Daftar fitur ini didaftarkan lewat seeder dan tidak bisa ditambah atau dihapus dari sini — hanya status Gratis/Berbayar per fitur yang bisa diubah. Fitur ini nantinya dicentang per paket di halaman Paket Harga.
    </div>

    <div class="space-y-3">
        @forelse ($features as $feature)
            <div class="flex items-center gap-4 rounded-2xl border border-border bg-surface p-4">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ $feature->name }}</p>
                    <p class="truncate text-xs text-text-muted">{{ $feature->key }}</p>
                </div>

                <button
                    wire:click="toggleFree({{ $feature->id }})"
                    class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $feature->is_free ? 'bg-green-50 text-green-700' : 'bg-primary-50 text-primary-700' }}"
                >
                    {{ $feature->is_free ? 'Gratis' : 'Berbayar' }}
                </button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-text-muted">
                Belum ada fitur terdaftar. Jalankan seeder terlebih dahulu.
            </div>
        @endforelse
    </div>
</div>
