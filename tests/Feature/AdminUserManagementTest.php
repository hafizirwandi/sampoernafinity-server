<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['access admin', 'manage users', 'manage admin users', 'manage roles'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('Super Admin')->givePermissionTo(Permission::all());
        Role::findOrCreate('Operator')->givePermissionTo(['access admin', 'manage users']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_creating_a_staff_account_with_a_role(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test('admin.admin-users.index')
            ->call('create')
            ->set('name', 'New Staff')
            ->set('email', 'newstaff@example.com')
            ->set('password', 'password123')
            ->set('role', 'Operator')
            ->call('save');

        $user = User::where('email', 'newstaff@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Operator'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_editing_a_staff_account_without_changing_password_keeps_the_old_one(): void
    {
        $this->actingAs($this->superAdmin());

        $staff = User::factory()->create(['password' => bcrypt('original-password')]);
        $staff->assignRole('Operator');

        Livewire::test('admin.admin-users.index')
            ->call('edit', $staff->id)
            ->set('name', 'Renamed Staff')
            ->set('password', '')
            ->call('save');

        $staff->refresh();
        $this->assertSame('Renamed Staff', $staff->name);
        $this->assertTrue(Hash::check('original-password', $staff->password));
    }

    public function test_cannot_delete_own_account(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        Livewire::test('admin.admin-users.index')->call('delete', $admin->id);

        $this->assertNotNull(User::find($admin->id));
    }

    public function test_cannot_delete_the_last_super_admin_even_by_another_staff_member(): void
    {
        $admin = $this->superAdmin();

        $otherStaff = User::factory()->create();
        $otherStaff->assignRole('Operator');
        $this->actingAs($otherStaff);

        Livewire::test('admin.admin-users.index')->call('delete', $admin->id);

        $this->assertNotNull(User::find($admin->id));
    }

    public function test_cannot_demote_the_last_super_admin(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        Livewire::test('admin.admin-users.index')
            ->call('edit', $admin->id)
            ->set('role', 'Operator')
            ->call('save')
            ->assertHasErrors('role');

        $this->assertTrue($admin->fresh()->hasRole('Super Admin'));
    }

    public function test_cannot_deactivate_the_last_super_admin(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        Livewire::test('admin.admin-users.index')
            ->call('edit', $admin->id)
            ->set('isActive', false)
            ->call('save')
            ->assertHasErrors('isActive');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_creating_a_staff_account_with_username_and_extra_permission(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test('admin.admin-users.index')
            ->call('create')
            ->set('name', 'New Staff')
            ->set('username', 'new.staff')
            ->set('email', 'newstaff@example.com')
            ->set('password', 'password123')
            ->set('role', 'Operator')
            ->set('selectedPermissions', ['manage roles'])
            ->call('save');

        $user = User::where('email', 'newstaff@example.com')->firstOrFail();
        $this->assertSame('new.staff', $user->username);
        $this->assertTrue($user->hasDirectPermission('manage roles'));
        $this->assertFalse($user->hasRole('Super Admin'));
    }

    public function test_username_must_be_unique(): void
    {
        $this->actingAs($this->superAdmin());
        User::factory()->create(['username' => 'taken']);

        Livewire::test('admin.admin-users.index')
            ->call('create')
            ->set('name', 'New Staff')
            ->set('username', 'taken')
            ->set('email', 'newstaff@example.com')
            ->set('password', 'password123')
            ->set('role', 'Operator')
            ->call('save')
            ->assertHasErrors('username');
    }
}
