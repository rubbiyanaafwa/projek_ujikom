@extends('layouts.app')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat')

@section('content')
<div class="space-y-6">

   <div class="container">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h3 class="mb-3">Katalog Alat Tersedia</h3>
        
        <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
            @csrf
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <label class="form-label">Rencana Tanggal Kembali</label>
                    <input type="date" name="tgl_kembali_plan" class="form-control" required>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="50">Pilih</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok Tersedia</th>
                        <th width="150">Jumlah Pinjam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alats as $index => $alat)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" class="form-check-input">
                        </td>
                        <td>{{ $alat->nama_alat }}</td>
                        <td>{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $alat->stok }}</td>
                        <td>
                            <input type="number" name="jumlah[]" class="form-control form-control-sm" value="1" min="1" max="{{ $alat->stok }}">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada alat yang tersedia saat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="text-end mb-5">
                <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
            </div>
        </form>
    </div>
</div>
@endsection
