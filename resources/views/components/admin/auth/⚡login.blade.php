<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.guest')]
#[Title('Masuk Admin')]
class extends Component
{
    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $data = $this->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $data['login'])
            ->orWhere('username', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Email/username atau kata sandi salah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Akun ini sudah dinonaktifkan.',
            ]);
        }

        if (! $user->can('access admin')) {
            throw ValidationException::withMessages([
                'login' => 'Akun ini tidak memiliki akses ke admin panel.',
            ]);
        }

        Auth::login($user, $this->remember);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirect(route('admin.dashboard'), navigate: true);
    }
};
?>

<div>
    <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700">
        Admin Panel
    </span>
    <h2 class="mt-3 text-xl font-semibold">Masuk ke Admin</h2>
    <p class="mt-1 text-sm text-text-muted">Khusus untuk staf pengelola platform.</p>

    <form wire:submit="login" class="mt-6 space-y-4">
        <div>
            <label for="login" class="mb-1.5 block text-sm font-medium">Email atau Username</label>
            <input
                type="text"
                id="login"
                wire:model="login"
                autofocus
                autocomplete="username"
                class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                placeholder="nama@contoh.com atau username"
            >
            @error('login') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium">Kata Sandi</label>
            <x-ui.password-input model="password" id="password" />
            @error('password') <p class="mt-1.5 text-sm text-primary-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-text-muted">
            <input type="checkbox" wire:model="remember" class="rounded border-border text-primary-600 focus:ring-primary-600">
            Ingat saya
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memproses...
            </span>
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-text-muted">
        Bukan staf? <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:underline">Masuk sebagai pengguna</a>
    </p>
</div>
