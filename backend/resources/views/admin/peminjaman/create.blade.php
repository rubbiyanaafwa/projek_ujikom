@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Tambah Transaksi Peminjaman')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    
    {{-- Alert Error jika Validasi Gagal --}}
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.peminjaman.store') }}" method="POST">
        @csrf

        {{-- Dropdown Pilih Peminjam (User) --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Peminjam (User)</label>
            <select name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('user_id') 
                <span class="text-red-500 text-xs">{{ $message }}</span> 
            @enderror
        </div>

        {{-- Input Tanggal --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                <input type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', date('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('tgl_pinjam') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Rencana Tanggal Kembali</label>
                <input type="date" name="tgl_kembali_plan" value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('tgl_kembali_plan') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Bagian Daftar Alat yang Dipinjam (Dinamis) --}}
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Daftar Alat yang Dipinjam</label>
            
            <div id="alat-container" class="space-y-3">
                {{-- Baris Input Pertama --}}
                <div class="flex items-center gap-2 alat-row">
                    <select name="alat_id[]" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Alat --</option>
                        @foreach($alat as $alats)
                            <option value="{{ $alats->id }}">
                                {{ $alats->name }} (Stok: {{ $alats->stok }})
                            </option>
                        @endforeach
                    </select>

                    <input type="number" name="jumlah[]" min="1" placeholder="Jumlah" required
                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    
                    <button type="button" onclick="removeRow(this)" 
                        class="bg-red-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-600 transition">
                        X
                    </button>
                </div>
            </div>

            {{-- Tombol Tambah Baris Alat --}}
            <button type="button" onclick="addRow()" 
                class="mt-3 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                + Tambah Alat Lain
            </button>
        </div>

        {{-- Tombol Aksi Akhir --}}
        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.peminjaman.index') }}" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Simpan Peminjaman
            </button>
        </div>
    </form>
</div>

{{-- Script Sederhana untuk Tambah/Hapus Baris Alat Dinamis --}}
<script>
function addRow() {
    const container = document.getElementById('alat-container');
    const firstRow = container.querySelector('.alat-row');
    
    // Gandakan elemen baris pertama
    const newRow = firstRow.cloneNode(true);
    
    // Kosongkan nilai input pada baris baru agar tidak menduplikasi input sebelumnya
    newRow.querySelector('select').value = '';
    newRow.querySelector('input').value = '';
    
    // Tambahkan baris baru ke wadah kontainer
    container.appendChild(newRow);
}

function removeRow(button) {
    const rows = document.querySelectorAll('.alat-row');
    
    // Pastikan minimal tersisa 1 baris input di form
    if (rows.length > 1) {
        button.closest('.alat-row').remove();
    } else {
        alert('Minimal harus ada 1 alat yang dipilih.');
    }
}
</script>
@endsection
