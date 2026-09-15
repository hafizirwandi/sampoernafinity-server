<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('access admin');
        Role::findOrCreate('Super Admin')->givePermissionTo('access admin');
    }

    private function admin(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'username' => 'staff.one',
            'password' => bcrypt('password123'),
        ], $attributes));
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_staff_can_log_in_with_email(): void
    {
        $admin = $this->admin();

        Livewire::test('admin.auth.login')
            ->set('login', $admin->email)
            ->set('password', 'password123')
            ->call('login');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_staff_can_log_in_with_username(): void
    {
        $admin = $this->admin();

        Livewire::test('admin.auth.login')
            ->set('login', 'staff.one')
            ->set('password', 'password123')
            ->call('login');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin();

        Livewire::test('admin.auth.login')
            ->set('login', 'staff.one')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_deactivated_staff_cannot_log_in(): void
    {
        $this->admin(['is_active' => false]);

        Livewire::test('admin.auth.login')
            ->set('login', 'staff.one')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_account_without_admin_access_is_rejected(): void
    {
        User::factory()->create(['username' => 'plain.user', 'password' => bcrypt('password123')]);

        Livewire::test('admin.auth.login')
            ->set('login', 'plain.user')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('login');

        $this->assertGuest();
    }
}
