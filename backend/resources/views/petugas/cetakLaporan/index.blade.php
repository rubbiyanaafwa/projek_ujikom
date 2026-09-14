@extends('layouts.app')

@section('title', 'Laporan Sirkulasi')
@section('header-title', 'Manajemen Laporan')

@section('content')
<div class="space-y-6">
    <!-- Statistik Riil Database -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-400 uppercase">Total Pengajuan</p>
            <h4 class="text-2xl font-bold text-gray-800 mt-1">{{ $statistik['total_pengajuan'] }}</h4>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-400 uppercase">Sedang Dipinjam</p>
            <h4 class="text-2xl font-bold text-blue-600 mt-1">{{ $statistik['sedang_dipinjam'] }}</h4>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-400 uppercase">Kasus Keterlambatan</p>
            <h4 class="text-2xl font-bold text-red-600 mt-1">{{ $statistik['total_telat'] }}</h4>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-400 uppercase">Denda Terkumpul</p>
            <h4 class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($statistik['total_denda'], 0, ',', '.') }}</h4>
        </div>
    </div>

    <!-- Form Filter -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-gray-700 text-xs font-semibold mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700 text-xs font-semibold mb-2">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-gray-700 text-xs font-semibold mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="dipinjam">Dipinjam</option>
                    <option value="telat">Telat</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-gray-800 text-white text-sm font-semibold py-2 rounded-lg">Filter</button>
                <a href="{{ route('petugas.laporan.pdf', request()->all()) }}" class="bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg text-center">Unduh PDF</a>
            </div>
        </form>
    </div>

    <!-- Tabel Hasil -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase border-b">
                    <th class="py-3 px-4">Nama Peminjam</th>
                    <th class="py-3 px-4">Alat & Jumlah</th>
                    <th class="py-3 px-4">Tanggal Pinjam / Janji</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Denda</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y">
                @forelse($peminjamans as $item)
                <tr>
                    <td class="py-3 px-4 font-medium">{{ $item->user->name ?? 'User Dihapus' }}</td>
                    <td class="py-3 px-4">
                        <ul class="list-disc list-inside">
                            @foreach($item->detailPinjam as $detail)
                            <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }} pcs)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="py-3 px-4">Pinjam: {{ $item->tgl_pinjam }}<br><span class="text-xs text-gray-400">Janji: {{ $item->tgl_kembali_plan }}</span></td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full uppercase">{{ $item->status }}</span></td>
                    <td class="py-3 px-4 text-right font-semibold">
                        Rp {{ number_format(optional($item->pengembalian)->denda ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-4 text-center text-gray-500">Tidak ada data laporan ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
