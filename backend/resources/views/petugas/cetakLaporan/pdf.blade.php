<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        .meta { margin-bottom: 16px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 6px; border: 1px solid #d1d5db; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; font-size: 9px; text-transform: uppercase; }
        .number { text-align: right; white-space: nowrap; }
        .items { margin: 0; padding-left: 14px; }
        .empty { padding: 16px; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Laporan Peminjaman Alat</h1>
    <div class="meta">
        Dicetak {{ now()->format('d/m/Y H:i') }}
        @if(request('start_date') || request('end_date'))
            | Periode: {{ request('start_date') ?: 'Semua' }} sampai {{ request('end_date') ?: 'Semua' }}
        @endif
        @if(request('status'))
            | Status: {{ ucfirst(request('status')) }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Peminjam</th>
                <th>Alat dan Jumlah</th>
                <th>Tanggal Pinjam</th>
                <th>Janji Kembali</th>
                <th>Status</th>
                <th class="number">Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjamans as $item)
                <tr>
                    <td>{{ $item->user->name ?? 'User Dihapus' }}</td>
                    <td>
                        <ul class="items">
                            @foreach($item->detailPinjam as $detail)
                                <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }} pcs)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ $item->tgl_pinjam }}</td>
                    <td>{{ $item->tgl_kembali_plan }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td class="number">Rp {{ number_format(optional($item->pengembalian)->denda ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="6">Tidak ada data laporan ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
