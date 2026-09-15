<?php

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new
#[Layout('layouts.admin', ['heading' => 'Role & Permission'])]
#[Title('Role & Permission')]
class extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingRoleId = null;

    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public string $permissionSearch = '';

    public function with(): array
    {
        return [
            'roles' => Role::query()
                ->withCount(['permissions', 'users'])
                ->orderBy('name')
                ->paginate(10),
            'permissions' => Permission::query()
                ->when($this->permissionSearch !== '', fn ($q) => $q->where('name', 'like', "%{$this->permissionSearch}%"))
                ->orderBy('name')
                ->get(),
        ];
    }

    public function create(): void
    {
        $this->reset(['editingRoleId', 'name', 'selectedPermissions', 'permissionSearch']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->all();
        $this->permissionSearch = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingRoleId)],
        ]);

        $role = $this->editingRoleId
            ? tap(Role::findOrFail($this->editingRoleId))->update($data)
            : Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
    }

    public function delete(int $roleId): void
    {
        $role = Role::withCount('users')->findOrFail($roleId);

        // Guard rails: never delete the base Super Admin role, and never
        // delete a role that is still assigned to someone.
        if ($role->name === 'Super Admin' || $role->users_count > 0) {
            return;
        }

        $role->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
};
?>

<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-text-muted">{{ $roles->total() }} role</p>

        <button wire:click="create" class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Role
        </button>
    </div>

    <div class="space-y-3">
        @foreach ($roles as $role)
            <div class="flex items-center gap-4 rounded-2xl border border-border bg-surface p-4">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">{{ $role->name }}</p>
                    <p class="text-xs text-text-muted">{{ $role->permissions_count }} permission &middot; {{ $role->users_count }} akun</p>
                </div>

                <div class="flex shrink-0 gap-2">
                    <button wire:click="edit({{ $role->id }})" class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium hover:bg-surface-alt">
                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                        Edit
                    </button>

                    @if ($role->name !== 'Super Admin' && $role->users_count === 0)
                        <button
                            x-data
                            x-on:click="confirm('Hapus role {{ $role->name }}?') && $wire.delete({{ $role->id }})"
                            class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50"
                        >
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{ $roles->links('components.ui.pagination') }}

    {{-- Create/edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">{{ $editingRoleId ? 'Edit Role' : 'Tambah Role' }}</h2>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div>
                        <label for="role-name" class="mb-1.5 block text-sm font-medium">Nama Role</label>
                        <input
                            type="text"
                            id="role-name"
                            wire:model="name"
                            placeholder="mis. Operator"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('name') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="block text-sm font-medium">Permission</label>
                            <span class="text-xs text-text-muted">{{ count($selectedPermissions) }} dipilih</span>
                        </div>

                        <x-ui.search-input model="permissionSearch" placeholder="Cari permission..." class="mb-3" />

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @forelse ($permissions as $permission)
                                <label class="flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm hover:bg-surface-alt">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedPermissions"
                                        value="{{ $permission->name }}"
                                        class="rounded border-border text-primary-600 focus:ring-primary-600"
                                    >
                                    {{ $permission->name }}
                                </label>
                            @empty
                                <p class="col-span-full text-sm text-text-muted">Tidak ada permission yang cocok.</p>
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
