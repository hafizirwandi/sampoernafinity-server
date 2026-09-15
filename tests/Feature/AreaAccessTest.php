<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AreaAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::findOrCreate('Super Admin');
        $role->givePermissionTo(Permission::findOrCreate('access admin'));

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_hitting_dashboard_is_sent_to_customer_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_guest_hitting_admin_dashboard_is_sent_to_admin_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }

    public function test_customer_can_open_their_own_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_customer_hitting_admin_dashboard_is_bounced_to_their_own_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/dashboard')
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_admin_hitting_customer_dashboard_is_bounced_to_admin_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }
}
