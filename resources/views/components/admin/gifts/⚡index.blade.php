<?php

use App\Models\Gift;
use App\Models\GiftCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.admin', ['heading' => 'TikTok Gift & Stiker'])]
#[Title('TikTok Gift & Stiker')]
class extends Component
{
    public string $type = 'gift';

    // --- Gift/sticker item modal ---
    public bool $showModal = false;

    public ?int $editingGiftId = null;

    public string $name = '';

    public string $tiktokId = '';

    public string $coin = '';

    public string $imageUrl = '';

    public ?int $categoryId = null;

    // --- Category modal ---
    public bool $showCategoryModal = false;

    public string $newCategoryName = '';

    public ?int $editingCategoryId = null;

    public string $editingCategoryName = '';

    public function with(): array
    {
        return [
            'categories' => GiftCategory::query()
                ->where('type', $this->type)
                ->withCount('gifts')
                ->with(['gifts' => fn ($q) => $q->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'uncategorized' => Gift::query()
                ->where('type', $this->type)
                ->whereNull('gift_category_id')
                ->orderBy('name')
                ->get(),
            'typeLabels' => GiftCategory::TYPES,
        ];
    }

    public function setType(string $type): void
    {
        $this->type = $type === 'sticker' ? 'sticker' : 'gift';
    }

    // --- Gift/sticker item CRUD ---

    public function create(): void
    {
        $this->reset(['editingGiftId', 'name', 'tiktokId', 'coin', 'imageUrl', 'categoryId']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $giftId): void
    {
        $gift = Gift::findOrFail($giftId);

        $this->editingGiftId = $gift->id;
        $this->name = $gift->name;
        $this->tiktokId = $gift->tiktok_id;
        $this->coin = (string) $gift->coin;
        $this->imageUrl = $gift->image_url;
        $this->categoryId = $gift->gift_category_id;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'tiktokId' => [
                'required', 'string', 'max:64',
                Rule::unique('gifts', 'tiktok_id')->where(fn ($q) => $q->where('type', $this->type))->ignore($this->editingGiftId),
            ],
            'coin' => ['required', 'integer', 'min:0'],
            'imageUrl' => ['required', 'url', 'max:2048'],
            'categoryId' => [
                'nullable',
                Rule::exists('gift_categories', 'id')->where(fn ($q) => $q->where('type', $this->type)),
            ],
        ]);

        $attributes = [
            'gift_category_id' => $data['categoryId'],
            'type' => $this->type,
            'tiktok_id' => $data['tiktokId'],
            'name' => $data['name'],
            'coin' => $data['coin'],
            'image_url' => $data['imageUrl'],
        ];

        if ($this->editingGiftId) {
            Gift::findOrFail($this->editingGiftId)->update($attributes);
        } else {
            Gift::create($attributes);
        }

        $this->showModal = false;
    }

    public function delete(int $giftId): void
    {
        Gift::where('id', $giftId)->where('type', $this->type)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    // --- Category CRUD ---

    public function openCategoryModal(): void
    {
        $this->reset(['newCategoryName', 'editingCategoryId', 'editingCategoryName']);
        $this->resetValidation();
        $this->showCategoryModal = true;
    }

    public function addCategory(): void
    {
        $data = $this->validate([
            'newCategoryName' => [
                'required', 'string', 'max:255',
                Rule::unique('gift_categories', 'slug')->where(fn ($q) => $q->where('type', $this->type)),
            ],
        ], [], ['newCategoryName' => 'nama kategori']);

        GiftCategory::create([
            'type' => $this->type,
            'name' => $data['newCategoryName'],
            'slug' => Str::slug($data['newCategoryName']),
            'sort_order' => (GiftCategory::where('type', $this->type)->max('sort_order') ?? 0) + 1,
        ]);

        $this->reset(['newCategoryName']);
    }

    public function editCategory(int $categoryId): void
    {
        $category = GiftCategory::findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->editingCategoryName = $category->name;
    }

    public function updateCategory(): void
    {
        $data = $this->validate([
            'editingCategoryName' => [
                'required', 'string', 'max:255',
                Rule::unique('gift_categories', 'slug')->where(fn ($q) => $q->where('type', $this->type))->ignore($this->editingCategoryId),
            ],
        ], [], ['editingCategoryName' => 'nama kategori']);

        GiftCategory::findOrFail($this->editingCategoryId)->update([
            'name' => $data['editingCategoryName'],
            'slug' => Str::slug($data['editingCategoryName']),
        ]);

        $this->reset(['editingCategoryId', 'editingCategoryName']);
    }

    public function cancelEditCategory(): void
    {
        $this->reset(['editingCategoryId', 'editingCategoryName']);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = GiftCategory::withCount('gifts')->where('type', $this->type)->findOrFail($categoryId);

        // Guard rail: never delete a category that still has items in it.
        if ($category->gifts_count > 0) {
            return;
        }

        $category->delete();
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
    }
};
?>

<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="inline-flex rounded-lg border border-border bg-surface p-1">
            @foreach ($typeLabels as $value => $label)
                <button
                    wire:click="setType('{{ $value }}')"
                    class="rounded-md px-4 py-1.5 text-sm font-medium {{ $type === $value ? 'bg-primary-600 text-white' : 'text-text-muted hover:text-text' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button wire:click="openCategoryModal" class="flex items-center justify-center gap-1.5 rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-surface-alt">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                Kelola Kategori
            </button>

            <button wire:click="create" class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                <x-heroicon-o-plus class="h-4 w-4" />
                Tambah {{ $typeLabels[$type] }}
            </button>
        </div>
    </div>

    <div class="space-y-6">
        @forelse ($categories as $category)
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ $category->name }}</h3>
                    <span class="text-xs text-text-muted">{{ $category->gifts_count }} item</span>
                </div>

                @if ($category->gifts->isEmpty())
                    <div class="rounded-2xl border border-dashed border-border p-6 text-center text-sm text-text-muted">
                        Belum ada item di kategori ini.
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                        @foreach ($category->gifts as $gift)
                            <div class="flex flex-col items-center gap-2 rounded-2xl border border-border bg-surface p-4 text-center">
                                <img src="{{ $gift->image_url }}" alt="{{ $gift->name }}" class="h-14 w-14 rounded-lg object-contain" loading="lazy">
                                <p class="line-clamp-2 text-sm font-medium">{{ $gift->name }}</p>
                                <p class="text-xs font-medium text-yellow-600">{{ number_format($gift->coin) }} Coin</p>
                                <p class="text-xs text-text-muted">ID: {{ $gift->tiktok_id }}</p>

                                <div class="mt-1 flex gap-1.5">
                                    <button wire:click="edit({{ $gift->id }})" class="rounded-lg border border-border p-1.5 text-text-muted hover:bg-surface-alt hover:text-text">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    <button
                                        x-data
                                        x-on:click="confirm('Hapus {{ $typeLabels[$type] }} {{ $gift->name }}?') && $wire.delete({{ $gift->id }})"
                                        class="rounded-lg border border-border p-1.5 text-text-muted hover:bg-primary-50 hover:text-primary-600"
                                    >
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-text-muted">
                Belum ada kategori {{ strtolower($typeLabels[$type]) }}. Buat kategori dulu lewat "Kelola Kategori".
            </div>
        @endforelse

        @if ($uncategorized->isNotEmpty())
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">Tanpa Kategori</h3>
                    <span class="text-xs text-text-muted">{{ $uncategorized->count() }} item</span>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach ($uncategorized as $gift)
                        <div class="flex flex-col items-center gap-2 rounded-2xl border border-border bg-surface p-4 text-center">
                            <img src="{{ $gift->image_url }}" alt="{{ $gift->name }}" class="h-14 w-14 rounded-lg object-contain" loading="lazy">
                            <p class="line-clamp-2 text-sm font-medium">{{ $gift->name }}</p>
                            <p class="text-xs font-medium text-yellow-600">{{ number_format($gift->coin) }} Coin</p>
                            <p class="text-xs text-text-muted">ID: {{ $gift->tiktok_id }}</p>

                            <div class="mt-1 flex gap-1.5">
                                <button wire:click="edit({{ $gift->id }})" class="rounded-lg border border-border p-1.5 text-text-muted hover:bg-surface-alt hover:text-text">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                                <button
                                    x-data
                                    x-on:click="confirm('Hapus {{ $typeLabels[$type] }} {{ $gift->name }}?') && $wire.delete({{ $gift->id }})"
                                    class="rounded-lg border border-border p-1.5 text-text-muted hover:bg-primary-50 hover:text-primary-600"
                                >
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Create/edit gift item modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-md flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">{{ $editingGiftId ? 'Edit' : 'Tambah' }} {{ $typeLabels[$type] }}</h2>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div>
                        <label for="gift-name" class="mb-1.5 block text-sm font-medium">Nama</label>
                        <input
                            type="text"
                            id="gift-name"
                            wire:model="name"
                            placeholder="mis. A Shard of Hope"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('name') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="gift-tiktok-id" class="mb-1.5 block text-sm font-medium">TikTok ID</label>
                            <input
                                type="text"
                                id="gift-tiktok-id"
                                wire:model="tiktokId"
                                placeholder="mis. 59881"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('tiktokId') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gift-coin" class="mb-1.5 block text-sm font-medium">Coin</label>
                            <input
                                type="number"
                                id="gift-coin"
                                wire:model="coin"
                                min="0"
                                step="1"
                                placeholder="0"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('coin') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="gift-image-url" class="mb-1.5 block text-sm font-medium">Link Gambar</label>
                        <input
                            type="text"
                            id="gift-image-url"
                            wire:model="imageUrl"
                            placeholder="https://..."
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('imageUrl') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gift-category" class="mb-1.5 block text-sm font-medium">Kategori</label>
                        <select
                            id="gift-category"
                            wire:model="categoryId"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                            <option value="">Tanpa kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
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

    {{-- Category management modal --}}
    @if ($showCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeCategoryModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-md flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">Kategori {{ $typeLabels[$type] }}</h2>
                    <button wire:click="closeCategoryModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="newCategoryName"
                                wire:keydown.enter="addCategory"
                                placeholder="Nama kategori baru..."
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            <button wire:click="addCategory" class="shrink-0 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                Tambah
                            </button>
                        </div>
                        @error('newCategoryName') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        @forelse ($categories as $category)
                            <div class="flex items-center gap-2 rounded-lg border border-border px-3 py-2">
                                @if ($editingCategoryId === $category->id)
                                    <input
                                        type="text"
                                        wire:model="editingCategoryName"
                                        wire:keydown.enter="updateCategory"
                                        class="w-full rounded-lg border border-border bg-bg px-2 py-1 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    <button wire:click="updateCategory" class="shrink-0 text-text-muted hover:text-primary-600">
                                        <x-heroicon-o-check class="h-4 w-4" />
                                    </button>
                                    <button wire:click="cancelEditCategory" class="shrink-0 text-text-muted hover:text-text">
                                        <x-heroicon-o-x-mark class="h-4 w-4" />
                                    </button>
                                @else
                                    <span class="min-w-0 flex-1 truncate text-sm">{{ $category->name }}</span>
                                    <span class="shrink-0 text-xs text-text-muted">{{ $category->gifts_count }} item</span>
                                    <button wire:click="editCategory({{ $category->id }})" class="shrink-0 text-text-muted hover:text-text">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    @if ($category->gifts_count === 0)
                                        <button
                                            x-data
                                            x-on:click="confirm('Hapus kategori {{ $category->name }}?') && $wire.deleteCategory({{ $category->id }})"
                                            class="shrink-0 text-text-muted hover:text-primary-600"
                                        >
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-text-muted">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end border-t border-border p-4">
                    <button wire:click="closeCategoryModal" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-surface-alt">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
