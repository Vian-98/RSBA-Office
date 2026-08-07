<?php

namespace App\Models\Sdm;

use App\Enums\StatusKaryawan;
use App\Enums\KategoriKerja;
use App\Models\Surat\CutiJenis;
use App\Models\Surat\SuratCuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    protected $table = 'sdm_karyawan';
    protected $guarded = [];
    protected $appends = ['full_nama'];

    // casting enum status karyawan
    protected $casts = [
        'status' => StatusKaryawan::class,
        'kategori_kerja' => KategoriKerja::class,
    ];

    protected static function booted(): void
    {
        static::saved(function (Karyawan $karyawan) {
            if ($karyawan->wasChanged('kategori_kerja')) {
                $kategori = $karyawan->kategori_kerja instanceof KategoriKerja
                    ? $karyawan->kategori_kerja
                    : KategoriKerja::tryFrom($karyawan->kategori_kerja);

                if ($kategori === KategoriKerja::REGULER) {
                    \App\Models\Sdm\JadwalKerja::syncKaryawanRegulerSchedule($karyawan->id);
                }
            }
        });
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    public function dokterRecord(): HasOne
    {
        return $this->hasOne(Dokter::class, 'karyawan_id');
    }

    public function masakerja(): Attribute
    {

        return Attribute::make(
            get: function () {
                $tglMasuk = Carbon::parse($this->tgl_masuk);
                $now = Carbon::now();

                if ($tglMasuk->greaterThan($now)) {
                    return 'Belum Masuk Kerja';
                }

                // set Different date
                $diff = $tglMasuk->diff($now);

                return  "{$diff->y} Tahun {$diff->m} Bulan ";
            }
        );
    }

    public function usia(): Attribute
    {
        return Attribute::make(
            // get: fn() => Carbon::parse($this->tgl_lahir)->diffInYears() . ' Tahun '
            get: function () {
                $tglLahir = Carbon::parse($this->tgl_lahir);
                $now = Carbon::now();

                $diff = $tglLahir->diff($now);

                return "{$diff->y} Tahun";
            }
        );
    }

    function latestJabatan()
    {
        return $this->hasOne(KaryawanJabatan::class, 'karyawan_id')
            ->latest('created_at')
            ->with('jabatan');
    }

    // Get History Jabatan
    function historyJabatan()
    {
        return $this->belongsToMany(Jabatan::class, KaryawanJabatan::class)
            ->withPivot('id', 'bagian_id', 'created_at', 'tgl_mulai', 'tgl_berakhir')
            ->orderByPivot('created_at', 'desc');
    }


    // get Jabatan Aktif Saat Ini (tgl_berakhir IS NULL)
    function jabatan()
    {
        return $this->belongsToMany(Jabatan::class, 'sdm_kary_jabatan', 'karyawan_id', 'jabatan_id')
            ->withPivot('id', 'bagian_id', 'created_at', 'tgl_mulai', 'tgl_berakhir')
            ->wherePivotNull('tgl_berakhir')
            ->orderByPivot('tgl_mulai', 'desc');
    }

    /**
     * The effective department for the current assignment.
     * Assignment-level department wins; the job master is the legacy/default fallback.
     */
    public function getActiveBagianIdAttribute(): ?int
    {
        $jabatan = $this->jabatan->first();

        return $jabatan?->pivot?->bagian_id
            ?? $jabatan?->bagian_id;
    }

    // Get History Ruangan
    public function historyRuangan()
    {
        return $this->belongsToMany(\App\Models\Ruangan::class, 'sdm_kary_ruangan', 'karyawan_id', 'ruangan_id')
            ->using(KaryawanRuangan::class)
            ->withPivot('id', 'created_at', 'tgl_mulai', 'tgl_berakhir', 'is_utama', 'keterangan')
            ->orderByPivot('created_at', 'desc');
    }

    // Get Ruangan Aktif (Multi-Ruangan)
    public function ruangans()
    {
        return $this->belongsToMany(\App\Models\Ruangan::class, 'sdm_kary_ruangan', 'karyawan_id', 'ruangan_id')
            ->using(KaryawanRuangan::class)
            ->withPivot('id', 'tgl_mulai', 'tgl_berakhir', 'is_utama', 'keterangan')
            ->wherePivotNull('tgl_berakhir');
    }

    // Get Ruangan Utama (Primary Room)
    public function ruanganUtama()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id');
    }

    public function getFullNamaAttribute(): string
    {
        $parts = array_filter([
            $this->gelar_depan,
            $this->nama,
            $this->gelar_belakang,
        ]);

        return implode(' ', $parts);
    }

    public function getSisaCutiUntukJenis(int $jenisCutiId): int
    {
        $jenis = CutiJenis::find($jenisCutiId);
        if (!$jenis) {
            return 0;
        }

        if ($jenis->lama === 0 || is_null($jenis->lama)) {
            return 999;
        }

        $quota = $jenis->lama;

        if (empty($this->tgl_masuk)) {
            return 0;
        }

        $tglMasuk = Carbon::parse($this->tgl_masuk);
        $now = Carbon::now();

        // Cuti Tahunan (ID = 1) requires 1 year of service
        if ($jenisCutiId === 1 && $now->lt($tglMasuk->copy()->addYear())) {
            return -1;
        }

        // Determine start and end date of the period based on $jenis->periode
        if ($jenis->periode === 'Y') {
            // Anniversary reset
            $anniversaryThisYear = $tglMasuk->copy()->year($now->year);
            if ($now->gte($anniversaryThisYear)) {
                $startDate = $anniversaryThisYear;
                $endDate = $anniversaryThisYear->copy()->addYear();
            } else {
                $startDate = $anniversaryThisYear->copy()->subYear();
                $endDate = $anniversaryThisYear;
            }
        } elseif ($jenis->periode === 'M') {
            // Monthly reset
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } else {
            // Lifetime or no reset
            $startDate = Carbon::parse('1970-01-01');
            $endDate = Carbon::parse('2099-12-31');
        }

        // Sum the used leave for this specific type that is not rejected in this period
        $used = $this->suratCuti()
            ->where('urgensi_id', $jenisCutiId)
            ->where('status', '!=', 'rejected')
            ->whereBetween('tgl_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('lama_cuti');

        return max(0, $quota - $used);
    }

    public function getSisaCutiAttribute(): int
    {
        return $this->getSisaCutiUntukJenis(1); // 1 = Cuti Tahunan
    }

    // Relation cuti
    public function suratCuti()
    {
        return $this->hasMany(SuratCuti::class, 'karyawan_id');
    }

    public function ruanganKoordinasi()
    {
        return $this->belongsToMany(\App\Models\Ruangan::class, 'sdm_ruangan_koordinator', 'karyawan_id', 'ruangan_id')
            ->wherePivot('aktif', true);
    }

    public function isKoordinatorRuangan(int $ruanganId): bool
    {
        return $this->ruanganKoordinasi()->where('ruangan.id', $ruanganId)->exists();
    }

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id');
    }
}
