<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\DetailPinjam;
use App\Models\LogAktivitas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        return view('admin.dashboard', compact('logs'));
    }



    // CRUD Alat: Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
    $search = $request->input('search');

    $alat = Alat::with('kategori')
        ->when($search, function ($query, $search) {
            return $query->where('nama_alat', 'like', "%{$search}%")
                ->orWhere('status_kondisi', 'like', "%{$search}%")
                ->orWhereHas('kategori', function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', "%{$search}%");
                });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        return view('admin.alat.index', compact('alat', 'search'));
    }

    public function createAlat()
    {
    $kategori = Kategori::all();
        return view('admin.alat.create', compact('kategori'));
    }

    public function storeAlat(Request $request)
    {
    $request->validate([
        'nama_alat' => 'required|string|max:255',
        'kategori_id' => 'required|exists:kategori,id',
        'stok' => 'required|integer|min:0',
        'status_kondisi' => 'required|string|max:100',
        'deskripsi' => 'nullable|string',
        'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = $request->all();

    // Handle Upload Gambar jika ada
    if ($request->hasFile('gambar')) {
        $file = $request->file('gambar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('storage/alat'), $filename);
        $data['gambar'] = 'storage/alat/' . $filename;
    }

    Alat::create($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
    $alat = Alat::findOrFail($id);
    $kategori = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategori'));
    }

    public function updateAlat(Request $request, $id)
    {
    $alat = Alat::findOrFail($id);

    $request->validate([
        'nama_alat' => 'required|string|max:255',
        'kategori_id' => 'required|exists:kategori,id',
        'stok' => 'required|integer|min:0',
        'status_kondisi' => 'required|string|max:100',
        'deskripsi' => 'nullable|string',
        'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = $request->all();

    // Handle Update Gambar jika ada file baru
    if ($request->hasFile('gambar')) {
        // Hapus gambar lama jika ada fisik filenya
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $file = $request->file('gambar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('storage/alat'), $filename);
        $data['gambar'] = 'storage/alat/' . $filename;
    }

    $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
    $alat = Alat::findOrFail($id);

    // Hapus file gambar fisik jika ada
    if ($alat->gambar && file_exists(public_path($alat->gambar))) {
        unlink(public_path($alat->gambar));
    }

    $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }



    // CRUD User (Manajemen User Admin, Petugas, Peminjam)
    public function indexUser(Request $request)
    {
    $search = $request->input('search');

    $users = User::when($search, function ($query, $search) {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('role', 'like', "%{$search}%");
    })
    ->latest()
    ->paginate(10) // Tampilkan 10 data per halaman
    ->withQueryString(); // Memastikan parameter search tetap ada saat pindah halaman

        return view('admin.user.index', compact('users', 'search'));
    }


    public function createUser()
    {
        return view('admin.user.create');
    }

    // Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

            $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Menghapus user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }



    //CRUD Kategori
    public function indexKategori(Request $request)
    {
    $search = $request->input('search');

    $kategori = Kategori::when($search, function ($query, $search) {
        return $query->where('nama_kategori', 'like', "%{$search}%");
    })
    ->latest()
    ->paginate(5)
    ->withQueryString();

        return view('admin.kategori.index', compact('kategori', 'search'));
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,'.$id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
    $kategori = Kategori::findOrFail($id);

    // Opsional: Cek apakah kategori masih dipakai oleh alat
    if ($kategori->alat()->count() > 0) {
        return redirect()->route('admin.kategori.index')
            ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
    }

    $kategori->delete();
        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }



    //CRUD Peminjaman
    public function indexPeminjaman(Request $request)
    {
    $search = $request->input('search');

    $peminjamans = Peminjaman::with(['user', 'DetailPinjam.alat'])
        ->when($search, function ($query, $search) {
            return $query->where('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function createPeminjaman()
    {
    $users = User::where('role', 'peminjam')->get(); 
    $alat = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alat'));
    }

    public function storePeminjaman(Request $request)
    {
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'tgl_pinjam' => 'required|date',
        'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
        'alat_id' => 'required|array',
        'alat_id.*' => 'exists:alat,id',
        'jumlah' => 'required|array',
        'jumlah.*' => 'integer|min:1',
    ]);

    DB::beginTransaction();
    try {
        // Buat transaksi utama peminjaman
        $peminjaman = Peminjaman::create([
            'user_id' => $request->user_id,
            'tgl_pinjam' => $request->tgl_pinjam,
            'tgl_kembali_plan' => $request->tgl_kembali_plan,
            'status' => 'diajukan', // Status awal
        ]);

        // Simpan detail alat yang dipinjam
        foreach ($request->alat_id as $index => $alatId) {
            $jumlahPinjam = $request->jumlah[$index];
            $alat = Alat::findOrFail($alatId);

            // Validasi stok
            if ($alat->stok < $jumlahPinjam) {
                throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
            }

            DetailPinjam::create([
                'peminjaman_id' => $peminjaman->id,
                'alat_id' => $alatId,
                'jumlah' => $jumlahPinjam,
            ]);
        }

        DB::commit();
        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
    $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);
    
    $request->validate([
        'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat', // sesuaikan enum status Anda
    ]);

    DB::beginTransaction();
    try {
        $statusLama = $peminjaman->status;
        $statusBaru = $request->status;

        // 1. LOGIKA JIKA BARANG BARU DIPINJAM (Mengurangi Stok)
        if ($statusLama != 'dipinjam' && $statusBaru == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = $detail->alat;
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi.");
                }
                $alat->decrement('stok', $detail->jumlah);
            }
        } 
        
        // 2. LOGIKA JIKA STATUS DIUBAH MENJADI dikembalikan / DIKEMBALIKAN (Mengembalikan Stok + ISI TABEL PENGEMBALIAN)
        elseif ($statusLama == 'dipinjam' && ($statusBaru == 'dikembalikan' || $statusBaru == 'dikembalikan')) {
            
            // >>> PERBAIKAN: Otomatis buat data di tabel pengembalians <<<
            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => now(),
                'kondisi_kembali' => 'bagus', // default jika diubah lewat status cepat
                'denda'           => 0,        // default tanpa denda
                'petugas_id'      => auth()->id(),
            ]);

            // Kembalikan stok barang ke inventaris
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }
        }

        // Update status di tabel peminjaman
        $peminjaman->update(['status' => $statusBaru]);
        
        DB::commit();
        return redirect()->back()->with('success', 'Status peminjaman diperbarui dan riwayat pengembalian tercatat.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
    }

    public function destroyPeminjaman($id)
    {
    $peminjaman = Peminjaman::with('DetailPinjam')->findOrFail($id);

    // Jika statusnya sedang dipinjam, kembalikan stok terlebih dahulu sebelum dihapus
    if ($peminjaman->status == 'dipinjam') {
        foreach ($peminjaman->DetailPinjam as $detail) {
            $detail->alat->increment('stok', $detail->jumlah);
        }
    }

    $peminjaman->delete();
        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }


    //CRUD Pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                $query->whereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalians', 'search'));
    }

    // 2. Menampilkan Form Edit Pengembalian
    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        return view('admin.pengembalian.edit', compact('pengembalian'));
    }

    // 3. Memperbarui Data Pengembalian (Hanya Kondisi & Denda)
    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);

        $pengembalian = Pengembalian::findOrFail($id);
        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda ?? 0,
        ]);

        return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil diperbarui.');
    }

    // 4. Menghapus Data Pengembalian
    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        $pengembalian->delete();

        return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil dihapus.');
    }
}
