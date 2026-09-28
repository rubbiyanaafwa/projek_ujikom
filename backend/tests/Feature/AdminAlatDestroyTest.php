<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAlatDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_tool_with_an_active_loan_from_web(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $alat = $this->createToolWithLoan('dipinjam');

        $this->actingAs($admin)
            ->delete(route('admin.alat.destroy', $alat))
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('alat', ['id' => $alat->id]);
    }

    public function test_admin_can_delete_tool_with_only_returned_loan_history_from_web(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $alat = $this->createToolWithLoan('dikembalikan');

        $this->actingAs($admin)
            ->delete(route('admin.alat.destroy', $alat))
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('alat', ['id' => $alat->id]);
    }

    public function test_admin_cannot_delete_tool_with_an_active_loan_from_api(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $alat = $this->createToolWithLoan('telat');
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/alat/'.$alat->id)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Alat tidak dapat dihapus karena masih terkait dengan peminjaman aktif.');

        $this->assertDatabaseHas('alat', ['id' => $alat->id]);
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

    private function createToolWithLoan(string $status): Alat
    {
        $kategori = Kategori::create(['nama_kategori' => 'Peralatan']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Mikroskop',
            'stok' => 1,
            'status_kondisi' => 'Baik',
        ]);
        $user = $this->createUser('peminjam', 'peminjam@example.com');
        $peminjaman = Peminjaman::create([
            'user_id' => $user->id,
            'tgl_pinjam' => now()->toDateString(),
            'tgl_kembali_plan' => now()->addDay()->toDateString(),
            'status' => $status,
        ]);

        DetailPinjam::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        return $alat;
    }
}
