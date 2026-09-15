<?php

use App\Models\Feature;
use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin', ['heading' => 'Paket Harga'])]
#[Title('Paket Harga')]
class extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingPackageId = null;

    public string $name = '';

    public string $price = '';

    public string $durationUnit = 'month';

    public string $description = '';

    public bool $isActive = true;

    /** @var array<int, int> */
    public array $selectedFeatures = [];

    public function with(): array
    {
        return [
            'packages' => Package::query()
                ->withCount(['features', 'subscriptions'])
                ->orderBy('price')
                ->paginate(10),
            'features' => Feature::query()->orderBy('sort_order')->orderBy('name')->get(),
            'durationUnits' => Package::DURATION_UNITS,
        ];
    }

    public function create(): void
    {
        $this->reset(['editingPackageId', 'name', 'price', 'description', 'selectedFeatures']);
        $this->durationUnit = 'month';
        $this->isActive = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $packageId): void
    {
        $package = Package::with('features')->findOrFail($packageId);

        $this->editingPackageId = $package->id;
        $this->name = $package->name;
        $this->price = (string) $package->price;
        $this->durationUnit = $package->duration_unit;
        $this->description = $package->description ?? '';
        $this->isActive = $package->is_active;
        $this->selectedFeatures = $package->features->pluck('id')->all();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'durationUnit' => ['required', Rule::in(array_keys(Package::DURATION_UNITS))],
            'description' => ['nullable', 'string'],
            'selectedFeatures.*' => [Rule::exists('features', 'id')],
        ]);

        $attributes = [
            'name' => $data['name'],
            'price' => $data['price'],
            'duration_unit' => $data['durationUnit'],
            'description' => $data['description'] ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingPackageId) {
            $package = Package::findOrFail($this->editingPackageId);
            $package->update($attributes);
        } else {
            $package = Package::create([
                ...$attributes,
                'slug' => $this->uniqueSlug($data['name']),
            ]);
        }

        $package->features()->sync($this->selectedFeatures);

        $this->showModal = false;
    }

    public function delete(int $packageId): void
    {
        $package = Package::withCount('subscriptions')->findOrFail($packageId);

        // Guard rail: never delete a package that already has subscription history.
        if ($package->subscriptions_count > 0) {
            return;
        }

        $package->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Package::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
};
?>

<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-text-muted">{{ $packages->total() }} paket</p>

        <button wire:click="create" class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Paket
        </button>
    </div>

    <div class="space-y-3">
        @forelse ($packages as $package)
            <div class="flex flex-col gap-3 rounded-2xl border border-border bg-surface p-4 sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="truncate text-sm font-medium">{{ $package->name }}</p>
                        @if (! $package->is_active)
                            <span class="shrink-0 rounded-full bg-surface-alt px-2 py-0.5 text-[11px] font-medium text-text-muted">Nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs text-text-muted">
                        Rp {{ number_format($package->price, 0, ',', '.') }} &middot; {{ $package->durationLabel() }}
                        &middot; {{ $package->features_count }} fitur &middot; {{ $package->subscriptions_count }} pelanggan
                    </p>
                </div>

                <div class="flex shrink-0 gap-2">
                    <button wire:click="edit({{ $package->id }})" class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium hover:bg-surface-alt">
                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                        Edit
                    </button>

                    @if ($package->subscriptions_count === 0)
                        <button
                            x-data
                            x-on:click="confirm('Hapus paket {{ $package->name }}?') && $wire.delete({{ $package->id }})"
                            class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50"
                        >
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-text-muted">
                Belum ada paket. Tambahkan paket pertama.
            </div>
        @endforelse
    </div>

    {{ $packages->links('components.ui.pagination') }}

    {{-- Create/edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">{{ $editingPackageId ? 'Edit Paket' : 'Tambah Paket' }}</h2>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div>
                        <label for="package-name" class="mb-1.5 block text-sm font-medium">Nama Paket</label>
                        <input
                            type="text"
                            id="package-name"
                            wire:model="name"
                            placeholder="mis. IndoFinity Pro"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('name') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="package-price" class="mb-1.5 block text-sm font-medium">Harga (Rp)</label>
                            <input
                                type="number"
                                id="package-price"
                                wire:model="price"
                                min="0"
                                step="1"
                                placeholder="0"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('price') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="package-duration" class="mb-1.5 block text-sm font-medium">Masa Berlaku</label>
                            <select
                                id="package-duration"
                                wire:model="durationUnit"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                                @foreach ($durationUnits as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('durationUnit') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="package-description" class="mb-1.5 block text-sm font-medium">Deskripsi <span class="font-normal text-text-muted">(opsional)</span></label>
                        <textarea
                            id="package-description"
                            wire:model="description"
                            rows="2"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        ></textarea>
                        @error('description') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 rounded-lg border border-border bg-bg px-3 py-2 text-sm">
                        <input type="checkbox" wire:model="isActive" class="rounded border-border text-primary-600 focus:ring-primary-600">
                        Paket aktif (bisa didaftarkan ke pengguna)
                    </label>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="block text-sm font-medium">Fitur yang Didapat</label>
                            <span class="text-xs text-text-muted">{{ count($selectedFeatures) }} dipilih</span>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @forelse ($features as $feature)
                                <label class="flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm hover:bg-surface-alt">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedFeatures"
                                        value="{{ $feature->id }}"
                                        class="rounded border-border text-primary-600 focus:ring-primary-600"
                                    >
                                    {{ $feature->name }}
                                </label>
                            @empty
                                <p class="col-span-full text-sm text-text-muted">Belum ada fitur terdaftar.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border p-4">
                    <button wire:click="closeModal" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-surface-alt">
                        Batal
                    </button>
                    <button wire:click="save" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
