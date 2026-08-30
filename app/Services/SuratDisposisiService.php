<?php

namespace App\Services;

use App\Models\Sdm\Jabatan;
use App\Models\Sdm\KaryawanJabatan;
use App\Models\Surat\SuratDisposisi;
use App\Models\Surat\SuratDisposisiDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratDisposisiService
{
    /**
     * Mengambil format pattern No. Agenda saat ini.
     */
    public function getNoAgendaPattern(): string
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('surat_disposisi_setting')) {
                $setting = DB::table('surat_disposisi_setting')
                    ->where('key', 'no_agenda_pattern')
                    ->first();

                if ($setting && !empty($setting->value)) {
                    return $setting->value;
                }
            }
        } catch (\Throwable $e) {
            // Fallback default
        }

        return 'AG/{YYYY}/{NUMBER:4}';
    }

    /**
     * Menyimpan format pattern No. Agenda baru.
     */
    public function saveNoAgendaPattern(string $pattern): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('surat_disposisi_setting')) {
                DB::table('surat_disposisi_setting')->updateOrInsert(
                    ['key' => 'no_agenda_pattern'],
                    ['value' => $pattern, 'updated_at' => now()]
                );
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Menghasilkan No. Agenda berikutnya secara otomatis berdasarkan pattern.
     */
    public function getNextNoAgenda(): string
    {
        $pattern = $this->getNoAgendaPattern();
        $year = date('Y');
        $month = date('m');

        $countYear = SuratDisposisi::whereYear('created_at', $year)->count() + 1;

        $formattedNumber = sprintf('%04d', $countYear);
        if (preg_match('/\{NUMBER:(\d+)\}/', $pattern, $matches)) {
            $digits = (int) $matches[1];
            $formattedNumber = sprintf("%0{$digits}d", $countYear);
            $result = str_replace($matches[0], $formattedNumber, $pattern);
        } else {
            $result = str_replace('{NUMBER}', $formattedNumber, $pattern);
        }

        $result = str_replace('{YYYY}', $year, $result);
        $result = str_replace('{MM}', $month, $result);

        return $result;
    }

    /**
     * Mengambil daftar master karyawan & jabatan untuk daftar "Kepada YTH" (dibatasi 11 posisi resmi disposisi).
     */
    public function getMasterJabatanList(): array
    {
        $targetPositions = [
            'Wadir Medis',
            'Wadir Keuangan',
            'Wadir SDM & Umum',
            'Ka. Bid. Pelayanan',
            'Ka. Bid. Keperawatan',
            'Ka. Bid. Rawat Jalan',
            'Ka. Teknologi Informasi',
            'Ka. Manajemen Bisnis',
            'Ka SDM',
            'Ka Layanan Umum',
            'Ka Keuangan',
        ];

        $allJabatans = Jabatan::with(['jabatans.karyawan'])->get();

        $result = [];
        foreach ($targetPositions as $posName) {
            $matchedJabatan = null;
            $karyawanNama = null;
            $karyawanId = null;
            $userId = null;
            $jabatanId = null;

            // Search best match in sdm_jabatan
            foreach ($allJabatans as $j) {
                $dbNama = strtolower($j->nama ?? '');
                $targetLower = strtolower($posName);

                // Check string match or keywords
                if (
                    $dbNama === $targetLower ||
                    str_contains($dbNama, $targetLower) ||
                    (str_contains($targetLower, 'ti') && (str_contains($dbNama, 'teknologi informasi') || str_contains($dbNama, 'ti'))) ||
                    (str_contains($targetLower, 'wadir medis') && str_contains($dbNama, 'medis'))
                ) {
                    $matchedJabatan = $j;
                    break;
                }
            }

            if ($matchedJabatan) {
                $jabatanId = $matchedJabatan->id;
                $activeKaryawanJabatan = $matchedJabatan->jabatans->first();
                if ($activeKaryawanJabatan && $activeKaryawanJabatan->karyawan) {
                    $karyawan = $activeKaryawanJabatan->karyawan;
                    $karyawanNama = $karyawan->full_nama ?? ($karyawan->nama_depan . ' ' . $karyawan->nama_belakang);
                    $karyawanId = $karyawan->id;

                    $user = User::where('karyawan_id', $karyawan->id)->first();
                    if ($user) {
                        $userId = $user->id;
                    }
                }
            }

            $result[] = [
                'jabatan_id' => $jabatanId,
                'nama_jabatan' => $posName,
                'karyawan_id' => $karyawanId,
                'karyawan_nama' => $karyawanNama,
                'user_id' => $userId,
                'is_custom' => false,
            ];
        }

        return $result;
    }

    /**
     * Memproses pembuatan disposisi, TTD Digital Hash Direktur, dan auto-dispatch.
     */
    public function createDisposisi(array $data, array $recipients, int $creatorUserId): SuratDisposisi
    {
        return DB::transaction(function () use ($data, $recipients, $creatorUserId) {
            $noAgenda = $this->getNextNoAgenda();
            
            // Hash signature unik untuk verifikasi keabsahan dokumen
            $sigHash = hash('sha256', Str::uuid()->toString() . time() . ($data['no_surat'] ?? '') . $creatorUserId);

            $disposisi = SuratDisposisi::create([
                'no_agenda' => $noAgenda,
                'surat_masuk_id' => $data['surat_masuk_id'] ?? null,
                'tgl_surat' => $data['tgl_surat'],
                'no_surat' => $data['no_surat'],
                'perihal' => $data['perihal'],
                'asal_surat' => $data['asal_surat'],
                'catatan' => $data['catatan'] ?? null,
                'diterima_oleh' => $data['diterima_oleh'] ?? null,
                'tgl_diterima' => $data['tgl_diterima'] ?? null,
                'jam_diterima' => $data['jam_diterima'] ?? null,
                'direktur_id' => $creatorUserId,
                'signature_hash' => $sigHash,
                'signed_at' => now(),
                'status' => 'dispatched',
                'created_by' => $creatorUserId,
            ]);

            foreach ($recipients as $item) {
                if (!empty($item['is_info']) || !empty($item['is_action']) || !empty($item['is_arsip'])) {
                    SuratDisposisiDetail::create([
                        'surat_disposisi_id' => $disposisi->id,
                        'jabatan_id' => $item['jabatan_id'] ?? null,
                        'karyawan_id' => $item['karyawan_id'] ?? null,
                        'user_id' => $item['user_id'] ?? null,
                        'nama_tujuan' => $item['nama_jabatan'],
                        'is_info' => !empty($item['is_info']),
                        'is_action' => !empty($item['is_action']),
                        'is_arsip' => !empty($item['is_arsip']),
                        'status_tindak_lanjut' => 'pending',
                    ]);
                }
            }

            return $disposisi;
        });
    }
}
