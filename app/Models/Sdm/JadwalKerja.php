<?php

namespace App\Models\Sdm;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusJadwalKerja;

class JadwalKerja extends Model
{
    use Blameable;
    
    protected $table = 'sdm_jadwal_kerja';
    protected $guarded = [];
    protected $casts = [
        'status' => StatusJadwalKerja::class
    ];

    public function details()
    {
        return $this->hasMany(JadwalKerjaDetail::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(Karyawan::class, 'dibuat_oleh');
    }

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id');
    }

    public static function ensureEmployeeDetailsExist($karyawanId, $bulan, $tahun)
    {
        $karyawan = \App\Models\Sdm\Karyawan::find($karyawanId);
        if (!$karyawan || !$karyawan->ruangan_id) {
            return;
        }

        $jadwalKerja = self::where('ruangan_id', $karyawan->ruangan_id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        if (!$jadwalKerja) {
            try {
                $jadwalKerja = self::create([
                    'ruangan_id' => $karyawan->ruangan_id,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'status' => \App\Enums\StatusJadwalKerja::DRAFT,
                    'dibuat_oleh' => 1,
                ]);
            } catch (\Throwable $e) {
                $jadwalKerja = self::where('ruangan_id', $karyawan->ruangan_id)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();
                if (!$jadwalKerja) {
                    return;
                }
            }
        }

        $hasDetails = \App\Models\Sdm\JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
            ->where('karyawan_id', $karyawanId)
            ->exists();

        if ($hasDetails) {
            $shiftReguler = \App\Models\Sdm\JadwalShift::where('kode', 'REGULER')->where('aktif', true)->first();
            if ($karyawan->kategori_kerja === \App\Enums\KategoriKerja::REGULER && $shiftReguler) {
                $details = \App\Models\Sdm\JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
                    ->where('karyawan_id', $karyawanId)
                    ->whereNull('shift_id')
                    ->where('status_kehadiran', 'belum_dicek')
                    ->get();

                foreach ($details as $detail) {
                    $date = \Carbon\Carbon::parse($detail->tanggal);
                    if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                        $detail->update(['shift_id' => $shiftReguler->id]);
                    }
                }
            }
            return;
        }

        $shiftReguler = \App\Models\Sdm\JadwalShift::where('kode', 'REGULER')->where('aktif', true)->first();
        $daysInMonth = \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth;
        
        $startDate = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate = \Carbon\Carbon::create($tahun, $bulan, $daysInMonth)->format('Y-m-d');

        $approvedCutis = \App\Models\Surat\SuratCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tgl_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tgl_akhir', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->where('tgl_mulai', '<=', $startDate)
                          ->where('tgl_akhir', '>=', $endDate);
                  });
            })
            ->get();

        $cutiMap = [];
        foreach ($approvedCutis as $sc) {
            $dates = json_decode($sc->tgl_cuti, true);
            if (is_array($dates)) {
                foreach ($dates as $d) {
                    $cutiMap[$d] = [
                        'status' => (int)$sc->urgensi_id === 4 ? \App\Enums\StatusKehadiran::IZIN : \App\Enums\StatusKehadiran::CUTI,
                        'catatan' => $sc->jenis?->nama . ' resmi (' . $sc->no_surat . ')'
                    ];
                }
            }
        }

        $newDetails = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = \Carbon\Carbon::create($tahun, $bulan, $d);
            $dateStr = $date->format('Y-m-d');

            $shiftId = null;
            if ($karyawan->kategori_kerja === \App\Enums\KategoriKerja::REGULER && $shiftReguler) {
                if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                    $shiftId = $shiftReguler->id;
                }
            }

            $statusKehadiran = 'belum_dicek';
            $catatan = null;
            $actualShiftId = $shiftId;

            if (isset($cutiMap[$dateStr])) {
                $statusKehadiran = $cutiMap[$dateStr]['status']->value;
                $catatan = $cutiMap[$dateStr]['catatan'];
                $actualShiftId = null;
            }

            $newDetails[] = [
                'jadwal_kerja_id' => $jadwalKerja->id,
                'karyawan_id' => $karyawanId,
                'shift_id' => $actualShiftId,
                'tanggal' => $dateStr,
                'status_kehadiran' => $statusKehadiran,
                'catatan' => $catatan,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            \App\Models\Sdm\JadwalKerjaDetail::insert($newDetails);
        } catch (\Throwable $e) {
            // Safe to ignore duplicate records on concurrent request
        }
    }
}
