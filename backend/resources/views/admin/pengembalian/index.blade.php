@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Pengembalian Alat')

@section('content')
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

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <h3 class="text-lg font-bold text-gray-800">Daftar Pengembalian</h3>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.pengembalian.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider border-b">
                    <th class="py-3 px-4">Peminjam</th>
                    <th class="py-3 px-4">Alat</th>
                    <th class="py-3 px-4">Tgl Kembali</th>
                    <th class="py-3 px-4">Kondisi Kembali</th>
                    <th class="py-3 px-4">Denda</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse($pengembalians as $pengembalian)
                <tr class="hover:bg-gray-55 transition border-b">
                    <td class="py-3 px-4 font-medium">{{ $pengembalian->peminjaman->user->name ?? 'User Dihapus' }}</td>
                    <td class="py-3 px-4">
                        <ul class="list-disc list-inside">
                            @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                                <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }} pcs)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="py-3 px-4">{{ \Carbon\Carbon::parse($pengembalian->tgl_kembali)->format('d/m/Y') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ strtolower($pengembalian->kondisi_kembali) == 'bagus' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $pengembalian->kondisi_kembali }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-red-600 font-semibold">Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center space-x-2">
                        
                            <!-- Tombol Edit -->
                            <a href="{{ route('admin.pengembalian.edit', $pengembalian->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                Edit
                            </a>

                            <!-- Tombol Hapus -->
                            <form action="{{ route('admin.pengembalian.destroy', $pengembalian->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">Belum ada data pengembalian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        {{ $pengembalians->links() }}
    </div>
</div>
@endsection
