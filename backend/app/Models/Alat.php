<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    protected $table = 'alat';

    protected $fillable = ['kategori_id', 'nama_alat', 'stok', 'status_kondisi', 'deskripsi', 'gambar'];

    protected function casts(): array {
        return [
            'stok' => 'integer',
        ];
    }

    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPinjam(): HasMany {
        return $this->HasMany(DetailPinjam::class);
    }

    public function memilikiPeminjamanAktif(): bool
    {
        return $this->detailPinjam()
            ->whereHas('peminjaman', fn ($query) => $query->whereIn('status', ['diajukan', 'dipinjam', 'telat']))
            ->exists();
    }
}
