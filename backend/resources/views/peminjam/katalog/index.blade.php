@extends('layouts.app')

@section('title', 'Katalog Alat - Panel Peminjam')
@section('header-title', 'Katalog Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Periksa kembali pengajuan Anda.</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
        @csrf
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Alat yang tersedia</h2>
                <p class="mt-1 text-sm text-gray-500">Pilih alat dan jumlahnya, lalu tentukan tanggal rencana pengembalian.</p>
            </div>
            <div class="w-full sm:w-60">
                <label for="tgl_kembali_plan" class="mb-1 block text-sm font-semibold text-gray-700">Rencana tanggal kembali</label>
                <input id="tgl_kembali_plan" type="date" name="tgl_kembali_plan" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('tgl_kembali_plan') }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                @error('tgl_kembali_plan')
                    <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Pilih</th>
                            <th class="px-4 py-3">Nama alat</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Stok tersedia</th>
                            <th class="px-4 py-3">Jumlah pinjam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($alats as $alat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" aria-label="Pilih {{ $alat->nama_alat }}"
                                        @checked(in_array($alat->id, old('alat_id', [])))
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $alat->nama_alat }}</td>
                                <td class="px-4 py-3">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $alat->stok }} tersedia</span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="jumlah[{{ $alat->id }}]" min="1" max="{{ $alat->stok }}" value="{{ old('jumlah.' . $alat->id, 1) }}" aria-label="Jumlah {{ $alat->nama_alat }}"
                                        class="w-24 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada alat yang tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alats->isNotEmpty())
                <div class="flex justify-end border-t border-gray-200 bg-gray-50 p-4">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Ajukan Peminjaman
                    </button>
                </div>
            @endif
        </div>
    </form>
@endsection