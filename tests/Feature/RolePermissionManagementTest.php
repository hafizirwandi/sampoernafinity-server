<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['access admin', 'manage users', 'manage admin users', 'manage roles'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    private function superAdmin(): User
    {
        $role = tap(Role::findOrCreate('Super Admin'))->givePermissionTo(Permission::all());

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_creating_a_role_with_selected_permissions(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test('admin.roles.index')
            ->call('create')
            ->set('name', 'Support')
            ->set('selectedPermissions', ['manage users'])
            ->call('save');

        $role = Role::where('name', 'Support')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('manage users'));
        $this->assertFalse($role->hasPermissionTo('manage roles'));
    }

    public function test_editing_a_role_replaces_its_permission_set(): void
    {
        $this->actingAs($this->superAdmin());

        $role = Role::findOrCreate('Operator');
        $role->givePermissionTo(['access admin', 'manage users']);

        Livewire::test('admin.roles.index')
            ->call('edit', $role->id)
            ->set('selectedPermissions', ['access admin'])
            ->call('save');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('access admin'));
        $this->assertFalse($role->hasPermissionTo('manage users'));
    }

    public function test_permission_search_filters_the_checkbox_list(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test('admin.roles.index')
            ->call('create')
            ->set('permissionSearch', 'roles')
            ->assertSee('manage roles')
            ->assertDontSee('manage users');
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        Livewire::test('admin.roles.index')->call('delete', $superAdminRole->id);

        $this->assertNotNull(Role::find($superAdminRole->id));
    }

    public function test_a_role_still_assigned_to_someone_cannot_be_deleted(): void
    {
        $this->actingAs($this->superAdmin());

        $role = Role::findOrCreate('Operator');
        $role->givePermissionTo('access admin');
        User::factory()->create()->assignRole($role);

        Livewire::test('admin.roles.index')->call('delete', $role->id);

        $this->assertNotNull(Role::find($role->id));
    }
}
