<?php

namespace App\Services;

use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratCuti;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocstoreSyncService
{
    protected string $apiUrl;
    protected string $apiToken;

    public function __construct()
    {
        $this->apiUrl = env('DOCSTORE_API_URL', 'http://localhost:8000/api');
        $this->apiToken = env('DOCSTORE_API_TOKEN', '');
    }

    public function syncSp3(SuratSp3 $surat)
    {
        // Reload fresh model with relations to bypass cached relations
        $surat = SuratSp3::with(['approvals.users.karyawan', 'details'])->findOrFail($surat->id);

        // Prepare content
        $content = [
            'no' => $surat->no,
            'tahun' => $surat->tahun,
            'tgl' => $surat->tgl,
            'rekanan' => $surat->rekanan,
            'bayar' => $surat->bayar,
            'keterangan' => $surat->keterangan,
            'disetujui' => optional($surat->disetujui) ? (optional(\App\Models\Sdm\Karyawan::find($surat->disetujui))->nama ?? 'Sistem') : 'Sistem',
            'jabatan' => $surat->jabatan,
            'items' => $surat->details->map(function ($det) {
                return [
                    'keterangan' => $det->keterangan,
                    'nominal' => $det->nominal
                ];
            })->toArray()
        ];

        // Prepare signatures
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
                continue;
            }

            // Cari SignatureLog berdasarkan sign_type dan sign_id (andal)
            // data_hash di signature_logs bisa berisi nilai random dari migrasi lama,
            // sehingga tidak bisa digunakan sebagai identifier.
            $log = SignatureLogs::where('sign_type', 'persetujuan_sp3')
                ->where('sign_id', $surat->id)
                ->where('user_id', $approval->disetujui)
                ->orderBy('id', 'desc')
                ->first();

            $certs = null;
            if ($log) {
                $certs = SignatureCerts::where('user_id', $approval->disetujui)->latest('id')->first();
            }

            // Fallback jika log tidak ditemukan (dokumen seeder/tanpa log)
            $originalData = $log ? $log->data : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $signature = $log ? $log->signature : 'MOCK_SIGNATURE_' . $approval->signature_hash;

            $publicKey = '';
            if ($certs) {
                $publicKey = $certs->public_key;
            } else {
                $fallbackCert = SignatureCerts::where('user_id', 1)->first();
                $publicKey = $fallbackCert ? $fallbackCert->public_key : 'MOCK_PUBLIC_KEY';
            }

            $signatures[] = [
                'signature_hash' => $approval->signature_hash,
                'original_data' => $originalData,
                'signature' => $signature,
                'data_hash' => $log ? $log->data_hash : null,
                'algorithm' => $log ? ($log->algorithm ?? 'sha256') : 'sha256',
                'public_key' => $publicKey,
                'signer_name' => optional($approval->users->karyawan)->full_nama ?? optional($approval->users)->name ?? 'Sistem',
                'signer_role' => $approval->jabatan,
                'status' => is_object($approval->status) ? $approval->status->value : $approval->status,
                'signed_at' => $approval->approved_at ?? now()->toIso8601String()
            ];
        }

        // Send payload
        $payload = [
            'document_type' => 'sp3',
            'document_id' => $surat->id,
            'document_number' => $surat->no,
            'status' => is_object($surat->status) ? $surat->status->value : $surat->status,
            'content' => $content,
            'signatures' => $signatures
        ];

        return $this->sendToDocstore($payload);
    }

    public function syncCuti(SuratCuti $surat)
    {
        // Reload fresh model with relations to bypass cached relations
        $surat = SuratCuti::with(['karyawan.jabatan', 'jenis', 'approvals.karyawan.jabatan'])->findOrFail($surat->id);

        // Prepare content
        $karyawan = $surat->karyawan;
        $content = [
            'no_surat' => $surat->no_surat,
            'tgl_surat' => $surat->tgl_surat,
            'tgl_mulai' => $surat->tgl_mulai,
            'tgl_akhir' => $surat->tgl_akhir,
            'tgl_cuti' => $surat->tgl_cuti,
            'lama_cuti' => $surat->lama_cuti,
            'urgensi' => $surat->urgensi,
            'keterangan' => $surat->keterangan,
            'alamat' => $surat->alamat,
            'jenis_cuti' => optional($surat->jenis)->nama,
            'karyawan_name' => optional($karyawan)->nama,
            'karyawan_nip' => optional($karyawan)->nip,
            'karyawan_hp' => optional($karyawan)->hp,
            'karyawan_jabatan' => optional(optional($karyawan)->jabatan?->first())->nama,
        ];

        // Prepare signatures
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
                continue;
            }

            // Cari SignatureLog berdasarkan sign_type dan sign_id (andal)
            $user = User::where('karyawan_id', $approval->disetujui_oleh)->first();

            $log = SignatureLogs::where('sign_type', 'surat_cuti_approval')
                ->where('sign_id', $surat->id)
                ->when($user, fn($q) => $q->where('user_id', $user->id))
                ->orderBy('id', 'desc')
                ->first();

            $certs = null;
            if ($user && $log) {
                $certs = SignatureCerts::where('user_id', $user->id)->latest('id')->first();
            }

            // Fallback jika log tidak ditemukan (dokumen seeder/tanpa log)
            $originalData = $log ? $log->data : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $signature = $log ? $log->signature : 'MOCK_SIGNATURE_' . $approval->signature_hash;

            $publicKey = '';
            if ($certs) {
                $publicKey = $certs->public_key;
            } else {
                $fallbackCert = SignatureCerts::where('user_id', 1)->first();
                $publicKey = $fallbackCert ? $fallbackCert->public_key : 'MOCK_PUBLIC_KEY';
            }

            $signatures[] = [
                'signature_hash' => $approval->signature_hash,
                'original_data' => $originalData,
                'signature' => $signature,
                'data_hash' => $log ? $log->data_hash : null,
                'algorithm' => $log ? ($log->algorithm ?? 'sha256') : 'sha256',
                'public_key' => $publicKey,
                'signer_name' => optional($approval->karyawan)->full_nama ?? optional($approval->karyawan)->nama ?? 'Sistem',
                'signer_role' => optional(optional($approval->karyawan)->jabatan?->first())->nama,
                'status' => is_object($approval->status) ? $approval->status->value : $approval->status,
                'signed_at' => $approval->approved_at ?? now()->toIso8601String()
            ];
        }

        // Send payload
        $payload = [
            'document_type' => 'cuti',
            'document_id' => $surat->id,
            'document_number' => $surat->no_surat,
            'status' => is_object($surat->status) ? $surat->status->value : $surat->status,
            'content' => $content,
            'signatures' => $signatures
        ];

        return $this->sendToDocstore($payload);
    }

    protected function sendToDocstore(array $payload)
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(5)
                ->post($this->apiUrl . '/documents', $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Docstore sync failed: ' . $response->body(), [
                'status' => $response->status(),
                'payload' => $payload
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Docstore sync connection error: ' . $e->getMessage(), [
                'payload' => $payload
            ]);
            return false;
        }
    }
}
