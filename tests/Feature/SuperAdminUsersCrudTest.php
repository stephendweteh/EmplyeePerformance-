<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUsersCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_user(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employee',
            'permissions' => ['view_activity_logs'],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'employee',
        ]);
    }

    public function test_super_admin_can_update_user(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $user = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($superAdmin)->put(route('admin.users.update', $user), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'admin',
            'permissions' => ['manage_users', 'view_activity_logs'],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_super_admin_can_delete_user(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin));

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }
}
