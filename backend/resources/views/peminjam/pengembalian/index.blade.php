@extends('layouts.app')

@section('title', 'Pengembalian Alat - Panel Peminjam')
@section('header-title', 'Pengembalian Alat')

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-bold text-gray-900">Alat yang perlu dikembalikan</h2>
        <p class="mt-1 text-sm text-gray-500">Periksa jadwal pengembalian dan serahkan alat kepada petugas untuk pencatatan kondisi.</p>
    </div>

    <div class="space-y-4">
        @forelse($peminjamans->whereIn('status', ['dipinjam', 'telat']) as $peminjaman)
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal pinjam {{ $peminjaman->tgl_pinjam?->format('d M Y') }}</p>
                        <h3 class="mt-1 font-semibold text-gray-900">Rencana kembali {{ $peminjaman->tgl_kembali_plan?->format('d M Y') }}</h3>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $peminjaman->status === 'telat' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' }}">
                        {{ ucfirst($peminjaman->status) }}
                    </span>
                </div>

                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($peminjaman->detailPinjam as $detail)
                        <li class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span class="font-medium text-gray-800">{{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }}</span>
                            <span class="shrink-0 text-gray-500">{{ $detail->jumlah }} unit</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-3 rounded-md bg-gray-50 px-3 py-2.5 text-sm text-gray-600">Serahkan alat beserta seluruh kelengkapannya kepada petugas agar pengembalian dapat diperiksa dan dicatat.</p>
            </section>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white px-4 py-10 text-center shadow-sm">
                <h3 class="font-semibold text-gray-900">Tidak ada alat yang perlu dikembalikan</h3>
                <p class="mt-1 text-sm text-gray-500">Peminjaman aktif Anda akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>
@endsection