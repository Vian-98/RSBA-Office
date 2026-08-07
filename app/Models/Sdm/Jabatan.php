<?php

namespace App\Models\Sdm;

use App\Models\Sdm\Bagian;
use App\Models\Sdm\KaryawanJabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    protected $table = 'sdm_jabatan';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Jabatan $jabatan) {
            if (empty($jabatan->tingkat_id) || $jabatan->tingkat_id == 5) {
                $namaLower = strtolower($jabatan->nama ?? '');
                if (str_contains($namaLower, 'direktur utama') || str_contains($namaLower, 'dirut') || str_contains($namaLower, 'dewan pengawas')) {
                    $jabatan->tingkat_id = 1;
                } elseif (str_contains($namaLower, 'wadir') || str_contains($namaLower, 'wakil direktur')) {
                    $jabatan->tingkat_id = 2;
                } elseif (str_contains($namaLower, 'kabid') || str_contains($namaLower, 'kepala bidang') || str_contains($namaLower, 'kabag') || str_contains($namaLower, 'kepala bagian') || str_contains($namaLower, 'kepala dept') || str_contains($namaLower, 'manajer')) {
                    $jabatan->tingkat_id = 3;
                } elseif (str_contains($namaLower, 'koordinator') || str_contains($namaLower, 'karu') || str_contains($namaLower, 'kepala ruangan') || str_contains($namaLower, 'koor')) {
                    $jabatan->tingkat_id = 4;
                } else {
                    $jabatan->tingkat_id = 5;
                }
            }
        });
    }

    function atasan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }

    function bawahan(): HasMany
    {
        return $this->hasMany(Jabatan::class, 'parent_id');
    }

    // to get history jabatan
    function jabatans()
    {
        return $this->hasMany(KaryawanJabatan::class);
    }

    function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }

    function tingkat(): BelongsTo
    {
        return $this->belongsTo(JabatanTingkat::class, 'tingkat_id');
    }

    public function isKoordinator(): bool
    {
        return $this->tingkat?->is_penyusun_jadwal || $this->tingkat_id === 4;
    }

    public function isKepalaDept(): bool
    {
        return $this->tingkat_id === 3;
    }

    public function isWadir(): bool
    {
        return $this->tingkat_id === 2;
    }

    public function resolveTargetRoleName(): string
    {
        $urutanTingkat = (int) ($this->tingkat?->urutan ?? 99);
        $namaJabatan = strtolower($this->nama ?? '');
        $namaBagian = strtolower($this->bagian?->nama ?? '');

        // 1. Direktur Utama
        if ($urutanTingkat === 1 || str_contains($namaJabatan, 'direktur utama')) {
            return 'Direktur';
        }

        // 2. Wakil Direktur (Wadir)
        if ($urutanTingkat === 2 || str_contains($namaJabatan, 'wadir') || str_contains($namaJabatan, 'wakil direktur')) {
            if (str_contains($namaJabatan, 'medis') || str_contains($namaJabatan, 'keperawatan') || str_contains($namaBagian, 'medis') || str_contains($namaBagian, 'keperawatan')) {
                return 'Wadir-Medis-Keperawatan';
            }
            return 'Wadir-SDM-Umum';
        }

        // 3. Pengecekan Spesifik Berdasarkan Nama Jabatan Terlebih Dahulu
        if (str_contains($namaJabatan, 'sdm') || str_contains($namaJabatan, 'kepegawaian') || str_contains($namaJabatan, 'personalia')) {
            return 'Staff-SDM';
        }

        $isMedisContext = str_contains($namaJabatan, 'dokter')
            || str_contains($namaJabatan, 'dr.')
            || str_contains($namaJabatan, 'medis')
            || str_contains($namaJabatan, 'klinik')
            || str_contains($namaJabatan, 'perawat')
            || str_contains($namaJabatan, 'bidan');

        if (!$isMedisContext && (str_contains($namaJabatan, 'umum') || str_contains($namaJabatan, 'sarpras') || str_contains($namaJabatan, 'logistik'))) {
            return 'Bagian-Umum';
        }

        if (str_contains($namaJabatan, 'keuangan') || str_contains($namaJabatan, 'finansial') || str_contains($namaJabatan, 'kasir') || str_contains($namaJabatan, 'akuntansi')) {
            return 'Keuangan';
        }

        // 4. Pengecekan Sekunder dari Nama Bagian (jika Nama Jabatan bersifat umum seperti "Staf")
        if (str_contains($namaBagian, 'sdm') && !str_contains($namaBagian, 'umum')) {
            return 'Staff-SDM';
        }
        if (str_contains($namaBagian, 'umum') && !str_contains($namaBagian, 'sdm')) {
            return 'Bagian-Umum';
        }
        if (str_contains($namaBagian, 'keuangan')) {
            return 'Keuangan';
        }

        // 5. Kepala Bagian / Kabid Operasional Pelayanan (Farmasi, Medis, Keperawatan, Gizi, dll.)
        if ($urutanTingkat === 3 || str_contains($namaJabatan, 'kepala bagian') || str_contains($namaJabatan, 'kabid') || str_contains($namaJabatan, 'kabag')) {
            return 'Kepala-Bidang';
        }

        // 6. Koordinator Ruangan / Karu
        if ($urutanTingkat === 4 || $this->isKoordinator() || str_contains($namaJabatan, 'koordinator') || str_contains($namaJabatan, 'karu')) {
            return 'Koordinator';
        }

        // 7. Staf / Pelaksana Operasional Biasa
        return 'Guest';
    }
}
