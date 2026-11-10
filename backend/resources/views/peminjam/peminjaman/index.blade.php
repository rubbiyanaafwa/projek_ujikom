@extends('layouts.app')

@section('title', 'Peminjaman Saya - Panel Peminjam')
@section('header-title', 'Peminjaman Saya')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Riwayat pengajuan</h2>
            <p class="mt-1 text-sm text-gray-500">Pantau status pengajuan dan jadwal peminjaman alat Anda.</p>
        </div>
        <a href="{{ route('peminjam.katalog') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
            Lihat katalog alat
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Tanggal pinjam</th>
                        <th class="px-4 py-3">Daftar alat</th>
                        <th class="px-4 py-3">Rencana kembali</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse($peminjamans as $peminjaman)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-4">{{ $peminjaman->tgl_pinjam?->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                <ul class="space-y-1">
                                    @foreach($peminjaman->detailPinjam as $detail)
                                        <li><span class="font-medium text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }}</span><span class="ml-2 text-gray-500">{{ $detail->jumlah }} unit</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $peminjaman->tgl_kembali_plan?->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    @if($peminjaman->status === 'diajukan') bg-amber-50 text-amber-700
                                    @elseif($peminjaman->status === 'dipinjam') bg-blue-50 text-blue-700
                                    @elseif($peminjaman->status === 'dikembalikan') bg-emerald-50 text-emerald-700
                                    @else bg-red-50 text-red-700 @endif">
                                    {{ ucfirst($peminjaman->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">Belum ada pengajuan peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection