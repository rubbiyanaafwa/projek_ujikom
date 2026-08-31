@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
        Selamat datang, <span class="font-semibold">{{ auth()->user()->name }}</span> Anda login sebagai hak akses 
        <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
    </div>

    <!-- Tabel Log Aktivitas -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">Log Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="p-4 border-b border-gray-200">Waktu</th>
                        <th class="p-4 border-b border-gray-200">User</th>
                        <th class="p-4 border-b border-gray-200">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 border-b border-gray-200">{{ $log->created_at }}</td>
                        <td class="p-4 border-b border-gray-200 font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</td>
                        <td class="p-4 border-b border-gray-200">{{ $log->aktivitas }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-500">Belum ada log aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
