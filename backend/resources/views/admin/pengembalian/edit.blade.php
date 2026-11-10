@extends('layouts.app')

@section('title', 'Edit Pengembalian - Panel Admin')
@section('header-title', 'Edit Data Pengembalian')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- Informasi Peminjam & Alat (Read Only / Kunci) -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-2 text-sm text-gray-600">
        <div><strong>Nama Peminjam:</strong> {{ $pengembalian->peminjaman->user->name ?? '-' }}</div>
        <div>
            <strong>Alat yang Dikembalikan:</strong>
            <ul class="list-disc list-inside ml-2">
                @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                    <li>{{ $detail->alat->nama_alat }} ({{ $detail->jumlah }} pcs)</li>
                @endforeach
            </ul>
        </div>
        <div><strong>Tanggal Kembali:</strong> {{ \Carbon\Carbon::parse($pengembalian->tgl_kembali)->format('d/m/Y') }}</div>
    </div>

    <!-- Form Edit (Hanya Kondisi & Denda) -->
    <form action="{{ route('admin.pengembalian.update', $pengembalian->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Input Kondisi Kembali -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kondisi Kembali</label>
            <input type="text" name="kondisi_kembali" value="{{ old('kondisi_kembali', $pengembalian->kondisi_kembali) }}" required
                placeholder="Contoh: Baik, Rusak Ringan, Hilang 1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('kondisi_kembali') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Input Denda -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Denda (Rp)</label>
            <input type="number" name="denda" value="{{ old('denda', $pengembalian->denda) }}" min="0" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('denda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.pengembalian.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Perbarui</button>
        </div>
    </form>
</div>
@endsection
