<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use App\Models\Sdm\JadwalKerja;
use App\Services\AturanJadwalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class JadwalKerjaPdfController extends Controller
{
    public function exportPdf(int $id, AturanJadwalService $service)
    {
        $jadwalKerja = JadwalKerja::with([
            'ruangan',
            'bagian',
            'details.karyawan.latestJabatan.jabatan',
            'details.shift',
            'pembuat',
            'diketahuiOleh',
            'disetujuiOleh'
        ])->findOrFail($id);

        $perusahaan = \App\Models\Perusahaan::first();
        $namaPerusahaan = $perusahaan?->nama ?? 'RS BHAYANGKARA AMBON';

        $logoSrc = null;
        $logoPath = null;
        if ($perusahaan && $perusahaan->logo && file_exists(storage_path('app/public/' . $perusahaan->logo))) {
            $logoPath = storage_path('app/public/' . $perusahaan->logo);
        } elseif (file_exists(public_path('logo-fallback.png'))) {
            $logoPath = public_path('logo-fallback.png');
        }

        if ($logoPath && file_exists($logoPath)) {
            $mime = mime_content_type($logoPath);
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:' . $mime . ';base64,' . $logoData;
        }

        $user = auth()->user();
        if ($user && $user->hasRole('Guest') && !$user->isKoordinator() && !$user->can('view-kepegawaian-jadwal-kerja')) {
            abort_unless(
                in_array((int) $jadwalKerja->ruangan_id, $user->getOwnRuanganIds(), true),
                403,
                'Anda tidak memiliki akses untuk mengunduh PDF jadwal ruangan ini.'
            );
        }

        $daysInMonth = Carbon::create($jadwalKerja->tahun, $jadwalKerja->bulan, 1)->daysInMonth;
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = Carbon::create($jadwalKerja->tahun, $jadwalKerja->bulan, $d);
        }

        // Shift valid untuk ruangan
        $validShifts = $service->shiftValidUntukRuangan($jadwalKerja->ruangan_id, $jadwalKerja->bagian_id);
        $shiftOptions = $validShifts->map(function ($rs) {
            $shift = $rs->shift;
            return [
                'id' => $shift->id,
                'kode' => $shift->kode,
                'nama' => $shift->nama,
                'warna' => $shift->warna ?? '#e2e8f0',
                'jam_masuk' => $rs->jam_masuk_efektif,
                'jam_keluar' => $rs->jam_keluar_efektif,
            ];
        })->toArray();

        // Cuti Map
        $karyawanIds = $jadwalKerja->details->pluck('karyawan_id')->unique()->toArray();
        $approvedCutis = \App\Models\Surat\SuratCuti::whereIn('karyawan_id', $karyawanIds)
            ->where('status', 'approved')
            ->get();

        $cutiDates = [];
        foreach ($approvedCutis as $sc) {
            $cDates = json_decode($sc->tgl_cuti, true);
            if (is_array($cDates)) {
                foreach ($cDates as $cd) {
                    $cutiDates["{$sc->karyawan_id}-{$cd}"] = $sc->no_surat;
                }
            }
        }

        // Group details by Karyawan
        $grouped = $jadwalKerja->details->groupBy('karyawan_id');
        $karyawans = [];

        foreach ($grouped as $karyawanId => $details) {
            $karyawan = $details->first()->karyawan;
            if (!$karyawan) continue;

            $isDokter = $karyawan->dokterRecord()->exists();
            if ($jadwalKerja->tipe === 'dokter' && !$isDokter) continue;
            if ($jadwalKerja->tipe === 'karyawan' && $isDokter) continue;

            $jabatanNama = $karyawan->latestJabatan?->jabatan?->nama;
            if (!$jabatanNama) {
                $jabatanNama = $isDokter ? 'Dokter Umum' : 'Staf';
            }

            $row = [
                'id' => $karyawan->id,
                'nip' => $karyawan->nip,
                'nama' => $karyawan->full_nama,
                'jabatan' => $jabatanNama,
                'hp' => $karyawan->hp ?: ($karyawan->hp2 ?: '-'),
                'kategori' => $karyawan->kategori_kerja?->nama() ?? 'Shift',
                'details' => [],
            ];

            foreach ($details as $detail) {
                $day = $detail->tanggal->day;
                $row['details'][$day] = $detail;
            }

            $karyawans[] = $row;
        }

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ])->loadView('pdf.jadwal-kerja', [
            'namaPerusahaan' => $namaPerusahaan,
            'logoSrc' => $logoSrc,
            'jadwalKerja' => $jadwalKerja,
            'dates' => $dates,
            'daysInMonth' => $daysInMonth,
            'shiftOptions' => $shiftOptions,
            'karyawans' => $karyawans,
            'cutiDates' => $cutiDates,
        ])->setPaper('a4', 'landscape');

        $sanitizedRuangan = preg_replace('/[^A-Za-z0-9_\-]/', '_', $jadwalKerja->ruangan->nama ?? 'Ruangan');
        $filename = "Jadwal_Kerja_{$sanitizedRuangan}_{$jadwalKerja->bulan}_{$jadwalKerja->tahun}.pdf";

        return $pdf->stream($filename);
    }
}
