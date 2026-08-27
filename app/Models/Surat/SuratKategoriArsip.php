<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKategoriArsip extends Model
{
    use HasFactory;

    protected $table = 'surat_kategori_arsip';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'icon',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
