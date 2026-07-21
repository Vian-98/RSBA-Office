<?php

namespace App\Services;

use App\Enums\StatusApproval;
use App\Enums\StatusKehadiran;
use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Surat\SuratCuti;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TerapkanCutiBersamaService
{
    protected SimulasiCutiBersamaService $simulasiService;

    public function __construct(SimulasiCutiBersamaService $simulasiService)
    {
        $this->simulasiService = $simulasiService;
    }

    /**
     * Terjemahkan dan terapkan event Cuti Bersama.
     */
    public function terapkan(CutiBersama $cutiBersama, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($cutiBersama, $userId) {
            $simulasi = $this->simulasiService->simulasikan($cutiBersama);
            $userId = $userId ?? auth()->id() ?? 1;

            $cutiMap = [];
            foreach ($simulasi['details'] as $item) {
                if ($item['potong_cuti']) {
                    $cutiMap[$item['karyawan_id']][] = $item['tanggal'];
                }

                // Update jadwal kerja detail jika sudah ada
                if (in_array($item['status_aksi'], ['DIPOTONG_CUTI', 'CUTI_BERSAMA_BEBAS'])) {
                    JadwalKerjaDetail::where('karyawan_id', $item['karyawan_id'])
                        ->where('tanggal', $item['tanggal'])
                        ->update([
                            'status_kehadiran' => StatusKehadiran::CUTI_BERSAMA,
                            'catatan' => 'Cuti Bersama: ' . $cutiBersama->nama,
                            'updated_by' => $userId,
                        ]);
                }
            }

            // Generate record surat_cuti untuk pegawai yang terpotong
            foreach ($cutiMap as $karyawanId => $tglList) {
                sort($tglList);
                $minTgl = reset($tglList);
                $maxTgl = end($tglList);

                $existing = SuratCuti::where('karyawan_id', $karyawanId)
                    ->where('cuti_bersama_id', $cutiBersama->id)
                    ->first();

                if (!$existing) {
                    $noSurat = 'CB' . str_pad($cutiBersama->id, 2, '0', STR_PAD_LEFT) . str_pad($karyawanId, 6, '0', STR_PAD_LEFT);

                    SuratCuti::create([
                        'no_surat' => $noSurat,
                        'tgl_surat' => date('Y-m-d'),
                        'karyawan_id' => $karyawanId,
                        'urgensi_id' => $cutiBersama->jenis_cuti_id ?? 1,
                        'keterangan' => 'Cuti Bersama: ' . $cutiBersama->nama,
                        'tgl_mulai' => $minTgl,
                        'tgl_akhir' => $maxTgl,
                        'tgl_cuti' => json_encode($tglList),
                        'lama_cuti' => count($tglList),
                        'status' => StatusApproval::APPROVED,
                        'sumber' => 'cuti_bersama',
                        'cuti_bersama_id' => $cutiBersama->id,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }

            $cutiBersama->update([
                'status' => 'diterapkan',
                'diproses_oleh' => $userId,
                'diproses_at' => now(),
                'updated_by' => $userId,
            ]);

            return true;
        });
    }
}
