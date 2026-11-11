<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\LogAktivitas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat yang tersedia
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog.index', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $validated = $request->validate([
            'tgl_kembali_plan' => ['required', 'date', 'after:today'],
            'alat_id' => ['required', 'array', 'min:1'],
            'alat_id.*' => ['required', 'integer', 'distinct', 'exists:alat,id'],
            'jumlah' => ['required', 'array'],
            'jumlah.*' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = auth()->user();

            $alats = Alat::query()
                ->whereIn('id', $validated['alat_id'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $peminjaman = Peminjaman::create([
                'user_id' => $user->id,
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $validated['tgl_kembali_plan'],
                'status' => 'diajukan',
            ]);

            $daftarAlat = [];

            foreach ($validated['alat_id'] as $alatId) {
                if (!array_key_exists($alatId, $validated['jumlah'])) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah setiap alat yang dipilih harus diisi.',
                    ]);
                }

                $jumlah = (int) $validated['jumlah'][$alatId];
                $alat = $alats->get($alatId);

                if (!$alat || $alat->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah alat melebihi stok yang tersedia.',
                    ]);
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlah,
                ]);

                $daftarAlat[] = $alat->nama_alat . ' (' . $jumlah . ')';
            }

            // Catat aktivitas pengajuan peminjaman.
            LogAktivitas::create([
                'user_id' => $user->id,
                'aktivitas' => 'Mengajukan peminjaman baru',
            ]);
        });

        return redirect()->route('peminjam.peminjaman.index')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim.');
    }

    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.peminjaman.index', compact('peminjamans'));
    }

    public function indexPengembalian()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['dipinjam', 'telat'])
            ->latest()
            ->get();

        return view('peminjam.pengembalian.index', compact('peminjamans'));
    }

    public function editProfil($id)
    { 
        $user = User::findOrFail($id);
        $auth = Auth::user();
        return view('peminjam.profil', compact('user', 'auth'));
    }

    // Memperbarui data user
    public function updateProfil(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'no_hp' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'alamat' => 'nullable|string|max:225',
        ]);

        $data = [
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ];

        if ($request->filled('name')) {
            $data['name'] = $validated['name'];
        }

        $user->update($data);

        return redirect()->route('peminjam.katalog')->with('success', 'profil berhasil diperbarui.');
    }
}
