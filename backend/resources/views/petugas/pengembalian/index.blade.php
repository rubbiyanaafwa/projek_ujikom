@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian - Dashboard Petugas')
@section('header-title', 'Pemantauan Pengembalian Alat')

@section('content')
    <!-- Notifikasi Sukses / Gagal -->
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Kotak Utama Tampilan -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Daftar Alat yang Sudah Dikembalikan</h3>
            
            <!-- Form Pencarian Nama Peminjam -->
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex w-full md:w-80 mt-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('petugas.pengembalian.index') }}"
                       class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Monitoring Pengembalian -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b">Tanggal Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <!-- Nama Siswa / Peminjam melalui Relasi -->
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            
                            <!-- Daftar Alat Master-Detail -->
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @if(isset($item->peminjaman->detailPinjam))
                                        @foreach($item->peminjaman->detailPinjam as $detail)
                                            <li>
                                                <span class="font-semibold">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                                (Jumlah: {{ $detail->jumlah }})
                                            </li>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic">Detail alat tidak ditemukan</span>
                                    @endif
                                </ul>
                            </td>
                            
                            <!-- Tanggal Pengembalian Dicatat -->
                            <td class="py-3 px-4 border-b">
                                {{ \Carbon\Carbon::parse($item->tgl_kembali)->translatedFormat('d F Y H:i') }}
                            </td>
                            
                            <!-- Kondisi Alat Saat Kembali -->
                            <td class="py-3 px-4 border-b">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ strtolower($item->kondisi_kembali) === 'baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($item->kondisi_kembali) }}
                                </span>
                            </td>
                            
                            <!-- Nominal Denda Keterlambatan/Kerusakan -->
                            <td class="py-3 px-4 border-b font-semibold {{ $item->denda > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                @if($item->denda > 0)
                                    Rp {{ number_format($item->denda, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <!-- Jika Belum Ada Data Pengembalian -->
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Belum ada riwayat alat yang dikembalikan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
