<?php

namespace App\Services;

use App\Enums\StatusTukarJadwal;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\TukarJadwalDokter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TukarJadwalDokterService
{
    /**
     * Dokter A membuat pengajuan tukar jadwal dengan Dokter B.
     */
    public function ajukanTukar(
        Karyawan $dokterA,
        int $jadwalDetailAId,
        Karyawan $dokterB,
        int $jadwalDetailBId,
        ?string $alasan = null
    ): TukarJadwalDokter {
        if ($dokterA->id === $dokterB->id) {
            throw new InvalidArgumentException('Tidak dapat menukar jadwal dengan diri sendiri.');
        }

        $detailA = JadwalKerjaDetail::findOrFail($jadwalDetailAId);
        $detailB = JadwalKerjaDetail::findOrFail($jadwalDetailBId);

        if ($detailA->karyawan_id !== $dokterA->id) {
            throw new InvalidArgumentException('Jadwal pengaju tidak sesuai dengan data Dokter A.');
        }

        if ($detailB->karyawan_id !== $dokterB->id) {
            throw new InvalidArgumentException('Jadwal pengganti tidak sesuai dengan data Dokter B.');
        }

        return DB::transaction(function () use ($dokterA, $detailA, $dokterB, $detailB, $alasan) {
            return TukarJadwalDokter::create([
                'dokter_pengaju_id'          => $dokterA->id,
                'jadwal_detail_pengaju_id'   => $detailA->id,
                'dokter_pengganti_id'        => $dokterB->id,
                'jadwal_detail_pengganti_id' => $detailB->id,
                'alasan'                     => $alasan,
                'status'                     => StatusTukarJadwal::MENUNGGU_KONFIRMASI_DOKTER,
            ]);
        });
    }

    /**
     * Dokter B mengonfirmasi (Menyetujui / Menolak) pengajuan tukar jadwal dari Dokter A.
     */
    public function konfirmasiDokter(TukarJadwalDokter $tukar, bool $setuju): TukarJadwalDokter
    {
        if ($tukar->status !== StatusTukarJadwal::MENUNGGU_KONFIRMASI_DOKTER) {
            throw new InvalidArgumentException('Pengajuan ini tidak sedang menunggu konfirmasi Dokter B.');
        }

        $tukar->update([
            'status'               => $setuju ? StatusTukarJadwal::MENUNGGU_WADIR : StatusTukarJadwal::DITOLAK_DOKTER,
            'konfirmasi_dokter_at' => now(),
        ]);

        return $tukar;
    }

    /**
     * Wadir memberikan persetujuan final (Setuju / Tolak) atas pengajuan tukar jadwal yang sudah disetujui Dokter B.
     */
    public function approveWadir(
        TukarJadwalDokter $tukar,
        bool $setuju,
        ?User $wadir = null,
        ?string $catatan = null
    ): TukarJadwalDokter {
        if ($tukar->status !== StatusTukarJadwal::MENUNGGU_WADIR) {
            throw new InvalidArgumentException('Pengajuan ini tidak sedang menunggu approval Wadir.');
        }

        return DB::transaction(function () use ($tukar, $setuju, $wadir, $catatan) {
            if ($setuju) {
                $tukar->update([
                    'status'               => StatusTukarJadwal::DISETUJUI,
                    'catatan_wadir'        => $catatan,
                    'disetujui_wadir_at'   => now(),
                    'disetujui_wadir_oleh' => $wadir?->id,
                ]);

                // Eksekusi pertukaran shift_id pada JadwalKerjaDetail
                $this->executeSwap($tukar);
            } else {
                $tukar->update([
                    'status'               => StatusTukarJadwal::DITOLAK_WADIR,
                    'catatan_wadir'        => $catatan,
                    'disetujui_wadir_at'   => now(),
                    'disetujui_wadir_oleh' => $wadir?->id,
                ]);
            }

            return $tukar;
        }
    );
    }

    /**
     * Menukar shift_id antara Dokter A & Dokter B pada JadwalKerjaDetail.
     */
    protected function executeSwap(TukarJadwalDokter $tukar): void
    {
        $detailA = JadwalKerjaDetail::findOrFail($tukar->jadwal_detail_pengaju_id);
        $detailB = JadwalKerjaDetail::findOrFail($tukar->jadwal_detail_pengganti_id);

        $tempShiftId = $detailA->shift_id;
        $detailA->update(['shift_id' => $detailB->shift_id]);
        $detailB->update(['shift_id' => $tempShiftId]);
    }
}
