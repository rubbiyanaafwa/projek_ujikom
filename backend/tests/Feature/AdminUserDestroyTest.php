<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUserDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_own_account_from_web(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');

        $this->actingAs($admin)
            ->delete(route('admin.user.destroy', $admin))
            ->assertRedirect(route('admin.user.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_user_from_web(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $user = $this->createUser('peminjam', 'user@example.com');

        $this->actingAs($admin)
            ->delete(route('admin.user.destroy', $user))
            ->assertRedirect(route('admin.user.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_own_account_from_api(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/users/'.$admin->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'Anda tidak dapat menghapus akun yang sedang digunakan.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }
}
