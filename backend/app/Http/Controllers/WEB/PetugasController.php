<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class PetugasController extends Controller
{
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                return redirect()->back()
                    ->with('error', 'Peminjaman sudah diproses.');
            }

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);

                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception(
                        "Stok alat {$alat->nama_alat} tidak mencukupi."
                    );
                }

                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'dipinjam']);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Peminjaman berhasil disetujui.');
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                return redirect()->back()
                    ->with('error', 'Status peminjaman sudah berubah.');
            }

            $peminjaman->delete();

            return redirect()->back()
                ->with('success', 'Pengajuan peminjaman berhasil ditolak.');
        } catch (Throwable $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with([
                'peminjaman.user',
                'peminjaman.detailPinjam.alat',
            ])
            ->when($search, function ($query, $search) {
                $query->whereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $validated = $request->validate([
            'kondisi_kembali' => ['required', 'string'],
            'denda' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->lockForUpdate()
                ->findOrFail($peminjamanId);

            if ($peminjaman->status !== 'dipinjam') {
                return redirect()->back()
                    ->with('error', 'Peminjaman belum berstatus dipinjam.');
            }

            if (Pengembalian::where('peminjaman_id', $peminjaman->id)->exists()) {
                return redirect()->back()
                    ->with('error', 'Pengembalian sudah diproses.');
            }

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $validated['kondisi_kembali'],
                'denda' => $validated['denda'] ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            foreach ($peminjaman->detailPinjam as $detail) {
                Alat::whereKey($detail->alat_id)
                    ->increment('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'dikembalikan']);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengembalian berhasil dicatat.');
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function filterLaporan(Request $request)
    {
        return Peminjaman::with([
                'user',
                'detailPinjam.alat',
                'pengembalian.petugas',
            ])
            ->when(
                $request->filled('start_date'),
                fn ($query) => $query->whereDate('tgl_pinjam', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn ($query) => $query->whereDate('tgl_pinjam', '<=', $request->end_date)
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest();
    }

    public function laporan(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:diajukan,dipinjam,dikembalikan,telat'],
        ]);

        $peminjamans = $this->filterLaporan($request)->get();

        $statistik = [
            'total_pengajuan' => Peminjaman::count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
            'total_telat' => Peminjaman::where('status', 'telat')->count(),
            'total_denda' => Pengembalian::sum('denda'),
        ];

        return view('petugas.cetakLaporan.index', compact(
            'peminjamans',
            'statistik'
        ));
    }

    public function cetakPdf(Request $request)
    {
        $peminjamans = $this->filterLaporan($request)->get();

        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans'))
            ->setPaper('a4', 'landscape');

        return $pdf->download(
            'Laporan-Peminjaman-Alat-' . now()->format('Ymd') . '.pdf'
        );
    }
}
