<?php

use App\Models\Package;
use App\Models\PackageSubscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin', ['heading' => 'Langganan Paket'])]
#[Title('Langganan Paket')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public string $userSearch = '';

    public ?int $selectedUserId = null;

    public string $packageId = '';

    public string $startedAt = '';

    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'subscriptions' => PackageSubscription::query()
                ->with(['user', 'package', 'approver'])
                ->when($this->search !== '', fn ($query) => $query
                    ->whereHas('user', fn ($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")))
                ->latest()
                ->paginate(10),
            'candidateUsers' => User::query()
                ->withoutPermission('access admin')
                ->when($this->userSearch !== '', fn ($query) => $query
                    ->where(fn ($q) => $q
                        ->where('name', 'like', "%{$this->userSearch}%")
                        ->orWhere('email', 'like', "%{$this->userSearch}%")))
                ->orderBy('name')
                ->limit(20)
                ->get(),
            'activePackages' => Package::where('is_active', true)->orderBy('price')->get(),
        ];
    }

    public function create(): void
    {
        $this->reset(['userSearch', 'selectedUserId', 'packageId', 'notes']);
        $this->startedAt = Carbon::today()->toDateString();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
    }

    public function save(): void
    {
        $data = $this->validate([
            'selectedUserId' => ['required', Rule::exists('users', 'id')],
            'packageId' => ['required', Rule::exists('packages', 'id')->where(fn ($q) => $q->where('is_active', true))],
            'startedAt' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $package = Package::findOrFail($data['packageId']);
        $startedAt = Carbon::parse($data['startedAt'])->startOfDay();

        PackageSubscription::create([
            'user_id' => $data['selectedUserId'],
            'package_id' => $package->id,
            'approved_by' => auth()->id(),
            'started_at' => $startedAt,
            'ended_at' => $package->calculateEndDate($startedAt),
            'status' => 'active',
            'notes' => $data['notes'] ?: null,
        ]);

        $this->showModal = false;
    }

    public function cancel(int $subscriptionId): void
    {
        $subscription = PackageSubscription::findOrFail($subscriptionId);

        if ($subscription->status === 'active') {
            $subscription->update(['status' => 'cancelled']);
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
};
?>

<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-text-muted">{{ $subscriptions->total() }} riwayat langganan</p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-ui.search-input model="search" placeholder="Cari nama atau email pengguna..." class="sm:w-72" />

            <button wire:click="create" class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                <x-heroicon-o-plus class="h-4 w-4" />
                Daftarkan Paket
            </button>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($subscriptions as $subscription)
            <div class="flex flex-col gap-3 rounded-2xl border border-border bg-surface p-4 sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="truncate text-sm font-medium">{{ $subscription->user->name }}</p>
                        <span @class([
                            'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium',
                            'bg-green-50 text-green-700' => $subscription->status === 'active',
                            'bg-surface-alt text-text-muted' => $subscription->status === 'cancelled',
                            'bg-primary-50 text-primary-700' => $subscription->status === 'expired',
                        ])>
                            {{ $subscription->statusLabel() }}
                        </span>
                    </div>
                    <p class="truncate text-xs text-text-muted">{{ $subscription->user->email }}</p>
                    <p class="mt-1 text-xs text-text-muted">
                        Paket <span class="font-medium text-text">{{ $subscription->package->name }}</span>
                        &middot; {{ $subscription->started_at->format('d M Y') }} &ndash; {{ $subscription->ended_at->format('d M Y') }}
                        &middot; disetujui {{ $subscription->approver?->name ?? '-' }}
                    </p>
                </div>

                @if ($subscription->status === 'active')
                    <div class="flex shrink-0 gap-2">
                        <button
                            x-data
                            x-on:click="confirm('Batalkan langganan {{ $subscription->user->name }}?') && $wire.cancel({{ $subscription->id }})"
                            class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50"
                        >
                            <x-heroicon-o-x-circle class="h-4 w-4" />
                            Batalkan
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-text-muted">
                Belum ada pengguna yang didaftarkan ke paket manapun.
            </div>
        @endforelse
    </div>

    {{ $subscriptions->links('components.ui.pagination') }}

    {{-- Create modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">Daftarkan Paket ke Pengguna</h2>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Pengguna</label>

                        <x-ui.search-input model="userSearch" placeholder="Cari nama atau email..." class="mb-3" />

                        <div class="max-h-40 space-y-1.5 overflow-y-auto">
                            @forelse ($candidateUsers as $candidate)
                                <label class="flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm hover:bg-surface-alt">
                                    <input
                                        type="radio"
                                        wire:click="selectUser({{ $candidate->id }})"
                                        @checked($selectedUserId === $candidate->id)
                                        class="border-border text-primary-600 focus:ring-primary-600"
                                    >
                                    <span class="min-w-0 flex-1 truncate">{{ $candidate->name }} <span class="text-text-muted">&middot; {{ $candidate->email }}</span></span>
                                </label>
                            @empty
                                <p class="text-sm text-text-muted">Tidak ada pengguna yang cocok.</p>
                            @endforelse
                        </div>
                        @error('selectedUserId') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="subscription-package" class="mb-1.5 block text-sm font-medium">Paket</label>
                            <select
                                id="subscription-package"
                                wire:model="packageId"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                                <option value="">Pilih paket...</option>
                                @foreach ($activePackages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }} &mdash; Rp {{ number_format($package->price, 0, ',', '.') }} / {{ $package->durationLabel() }}</option>
                                @endforeach
                            </select>
                            @error('packageId') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="subscription-started" class="mb-1.5 block text-sm font-medium">Mulai Berlaku</label>
                            <input
                                type="date"
                                id="subscription-started"
                                wire:model="startedAt"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('startedAt') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="subscription-notes" class="mb-1.5 block text-sm font-medium">Catatan <span class="font-normal text-text-muted">(opsional)</span></label>
                        <textarea
                            id="subscription-notes"
                            wire:model="notes"
                            rows="2"
                            placeholder="mis. dibayar via transfer manual"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        ></textarea>
                        @error('notes') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border p-4">
                    <button wire:click="closeModal" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-surface-alt">
                        Batal
                    </button>
                    <button wire:click="save" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                        Daftarkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
