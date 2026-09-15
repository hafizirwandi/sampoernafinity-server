<?php

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new
#[Layout('layouts.admin', ['heading' => 'Users Admin'])]
#[Title('Users Admin')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public bool $isActive = true;

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public string $permissionSearch = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->permission('access admin')
                ->with(['roles', 'permissions'])
                ->when($this->search !== '', fn ($query) => $query
                    ->where(fn ($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")))
                ->orderBy('name')
                ->paginate(10),
            'availableRoles' => Role::permission('access admin')->orderBy('name')->get(),
            'permissions' => Permission::query()
                ->when($this->permissionSearch !== '', fn ($q) => $q->where('name', 'like', "%{$this->permissionSearch}%"))
                ->orderBy('name')
                ->get(),
            'superAdminCount' => User::role('Super Admin')->count(),
        ];
    }

    public function create(): void
    {
        $this->reset(['editingUserId', 'name', 'username', 'email', 'password', 'role', 'selectedPermissions', 'permissionSearch']);
        $this->isActive = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()?->name ?? '';
        $this->isActive = $user->is_active;
        $this->selectedPermissions = $user->getDirectPermissions()->pluck('name')->all();
        $this->permissionSearch = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'regex:/^[a-zA-Z0-9._-]+$/', 'max:255', Rule::unique('users', 'username')->ignore($this->editingUserId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'password' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'selectedPermissions.*' => [Rule::exists('permissions', 'name')],
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);

            // Guard rails around the last remaining Super Admin: never let
            // them be demoted or deactivated with no one left to replace them.
            if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
                if ($data['role'] !== 'Super Admin') {
                    $this->addError('role', 'Tidak bisa mengubah role Super Admin terakhir.');

                    return;
                }

                if (! $this->isActive) {
                    $this->addError('isActive', 'Tidak bisa menonaktifkan Super Admin terakhir.');

                    return;
                }
            }

            $user->update([
                'name' => $data['name'],
                'username' => $data['username'] ?: null,
                'email' => $data['email'],
                'is_active' => $this->isActive,
                ...(filled($data['password']) ? ['password' => bcrypt($data['password'])] : []),
            ]);
        } else {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'] ?: null,
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'is_active' => $this->isActive,
            ]);
        }

        $user->syncRoles([$data['role']]);
        $user->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
    }

    public function delete(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Guard rails: never delete your own account, and never delete the
        // last remaining Super Admin.
        if ($user->id === auth()->id()) {
            return;
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return;
        }

        $user->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
};
?>

<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-text-muted">{{ $users->total() }} akun staf</p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-ui.search-input model="search" placeholder="Cari nama, email, atau username..." class="sm:w-72" />

            <button wire:click="create" class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                <x-heroicon-o-user-plus class="h-4 w-4" />
                Tambah Staf
            </button>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($users as $user)
            <div class="flex items-center gap-4 rounded-2xl border border-border bg-surface p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                        @if (! $user->is_active)
                            <span class="shrink-0 rounded-full bg-surface-alt px-2 py-0.5 text-[11px] font-medium text-text-muted">Nonaktif</span>
                        @endif
                    </div>
                    <p class="truncate text-xs text-text-muted">
                        {{ $user->email }}
                        @if ($user->username)
                            &middot; {{ '@'.$user->username }}
                        @endif
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                    @foreach ($user->roles as $role)
                        <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700">
                            {{ $role->name }}
                        </span>
                    @endforeach
                    @if ($user->permissions->isNotEmpty())
                        <span class="rounded-full bg-surface-alt px-2.5 py-1 text-xs text-text-muted" title="{{ $user->permissions->pluck('name')->implode(', ') }}">
                            +{{ $user->permissions->count() }} permission
                        </span>
                    @endif
                </div>

                <div class="flex shrink-0 gap-2">
                    <button wire:click="edit({{ $user->id }})" class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium hover:bg-surface-alt">
                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                        Edit
                    </button>

                    @if ($user->id !== auth()->id() && ! ($user->hasRole('Super Admin') && $superAdminCount <= 1))
                        <button
                            x-data
                            x-on:click="confirm('Hapus akun {{ $user->name }}?') && $wire.delete({{ $user->id }})"
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
                Tidak ada akun staf yang cocok.
            </div>
        @endforelse
    </div>

    {{ $users->links('components.ui.pagination') }}

    {{-- Create/edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col rounded-2xl border border-border bg-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h2 class="text-base font-semibold">{{ $editingUserId ? 'Edit Staf' : 'Tambah Staf' }}</h2>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-text-muted hover:bg-surface-alt hover:text-text">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="staff-name" class="mb-1.5 block text-sm font-medium">Nama</label>
                            <input
                                type="text"
                                id="staff-name"
                                wire:model="name"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('name') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="staff-username" class="mb-1.5 block text-sm font-medium">Username <span class="font-normal text-text-muted">(opsional)</span></label>
                            <input
                                type="text"
                                id="staff-username"
                                wire:model="username"
                                placeholder="mis. budi.admin"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('username') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="staff-email" class="mb-1.5 block text-sm font-medium">Email</label>
                        <input
                            type="email"
                            id="staff-email"
                            wire:model="email"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('email') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-text-muted">Bisa dipakai untuk masuk, selain username di atas.</p>
                    </div>

                    <div>
                        <label for="staff-password" class="mb-1.5 block text-sm font-medium">
                            Kata Sandi
                            @if ($editingUserId) <span class="font-normal text-text-muted">(kosongkan jika tidak diubah)</span> @endif
                        </label>
                        <input
                            type="password"
                            id="staff-password"
                            wire:model="password"
                            placeholder="********"
                            class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        >
                        @error('password') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="staff-role" class="mb-1.5 block text-sm font-medium">Role</label>
                            <select
                                id="staff-role"
                                wire:model="role"
                                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                                <option value="">Pilih role...</option>
                                @foreach ($availableRoles as $availableRole)
                                    <option value="{{ $availableRole->name }}">{{ $availableRole->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <span class="mb-1.5 block text-sm font-medium">Status</span>
                            <label class="flex items-center gap-2 rounded-lg border border-border bg-bg px-3 py-2 text-sm">
                                <input type="checkbox" wire:model="isActive" class="rounded border-border text-primary-600 focus:ring-primary-600">
                                Akun aktif
                            </label>
                            @error('isActive') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="block text-sm font-medium">Permission Tambahan <span class="font-normal text-text-muted">(di luar bawaan role)</span></label>
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
