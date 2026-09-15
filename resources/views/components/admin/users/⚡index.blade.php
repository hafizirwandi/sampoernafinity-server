<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.admin', ['heading' => 'Pengguna'])]
#[Title('Pengguna')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->withoutPermission('access admin')
                ->when($this->search !== '', fn ($query) => $query
                    ->where(fn ($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")))
                ->orderBy('name')
                ->paginate(10),
        ];
    }
};
?>

<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-text-muted">{{ $users->total() }} pengguna terdaftar</p>

        <x-ui.search-input model="search" placeholder="Cari nama atau email..." class="sm:w-72" />
    </div>

    <div class="space-y-3">
        @forelse ($users as $user)
            <div class="flex items-center gap-4 rounded-2xl border border-border bg-surface p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                    <p class="truncate text-xs text-text-muted">{{ $user->email }}</p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-text-muted">
                Tidak ada pengguna yang cocok.
            </div>
        @endforelse
    </div>

    {{ $users->links('components.ui.pagination') }}
</div>
