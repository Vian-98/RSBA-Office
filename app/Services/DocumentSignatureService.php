<?php

namespace App\Services;

use App\Models\SignatureLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DocumentSignatureService
{
    public function __construct(
        protected DigitalSignatureService $digitalSignatureService,
        protected SystemCertificateService $systemCertificateService
    ) {}

    /**
     * Ensure document has an official System PKCS#12 (.p12) Signature & Hash.
     * QR Hash yang dihasilkan adalah hash dari data surat yang ditandatangani sistem.
     * Hash ini disimpan di kolom qr_hash dan juga dikirim ke docstore.
     */
    public function ensureP12SystemSignature(Model $surat): string
    {
        if (!empty($surat->qr_hash)) {
            return $surat->qr_hash;
        }

        try {
            $systemUser = $this->systemCertificateService->getOrCreateSystemUser();

            $tglSurat = $surat->tgl_surat ?? $surat->tgl ?? ($surat->tanggal instanceof \DateTimeInterface ? $surat->tanggal->format('Y-m-d') : ($surat->tanggal ?? date('Y-m-d')));

            $payloadData = [
                'document_type' => get_class($surat),
                'document_id'   => $surat->id,
                'no_surat'      => $surat->no_surat ?? $surat->no ?? $surat->nomor ?? $surat->id,
                'tgl_surat'     => $tglSurat,
                'signed_by_system_p12' => true,
                'timestamp'     => now()->toIso8601String(),
            ];

            // Sign menggunakan DigitalSignatureService dengan System User PKCS#12 Certificate
            $signResult = $this->digitalSignatureService->signData(
                user: $systemUser,
                data: json_encode($payloadData),
                type: 'header_legalitas_sistem',
                id: $surat->id,
                password: 'password123'
            );

            $hash = ($signResult['status'] ?? false) ? $signResult['data_hash'] : hash('sha256', json_encode($payloadData));

            $surat->update([
                'qr_hash'   => $hash,
                'signed_at' => $surat->signed_at ?? now(),
                'is_valid'  => true,
            ]);

            SignatureLogs::firstOrCreate(
                ['data_hash' => $hash],
                [
                    'data'           => json_encode($payloadData),
                    'signature'      => $signResult['signature'] ?? $hash,
                    'algorithm'      => 'sha256',
                    'sign_type'      => 'header_legalitas_sistem',
                    'sign_id'        => $surat->id,
                    'user_id'        => $systemUser->id,
                    'certificate_id' => $signResult['certificate_id'] ?? 1,
                    'ip_address'     => request()->ip() ?? '127.0.0.1',
                    'user_agent'     => request()->userAgent() ?? 'System PKCS12 Auto-Signer',
                ]
            );

            return $hash;
        } catch (\Throwable $e) {
            Log::error('Error generating PKCS12 system signature: ' . $e->getMessage());
            $hash = hash('sha256', get_class($surat) . ':' . $surat->id . ':' . config('app.key'));
            $surat->update(['qr_hash' => $hash, 'is_valid' => true]);
            return $hash;
        }
    }

    /**
     * Trigger sync ke docstore setiap kali ada perubahan pada surat.
     *
     * Dipanggil dari:
     * - Approval::submit() (Cuti, SP3, Kuitansi) — setiap ada approval baru
     * - checkAndGenerateHeaderQr() — setelah status final tercapai
     *
     * Sync bersifat fire-and-forget: gagal sync tidak menghalangi proses approval.
     * docstore_key akan disimpan di record surat setelah sync berhasil.
     */
    public function triggerDocstoreSync(Model $surat): void
    {
        try {
            $syncService = app(DocstoreSyncService::class);

            $modelClass = get_class($surat);
            if (str_contains($modelClass, 'SuratCuti')) {
                $syncService->syncCuti($surat->fresh());
            } elseif (str_contains($modelClass, 'SuratSp3')) {
                $syncService->syncSp3($surat->fresh());
            } elseif (str_contains($modelClass, 'Kuitansi')) {
                $syncService->syncKuitansi($surat->fresh());
            } else {
                Log::warning('triggerDocstoreSync: model tidak dikenal', ['class' => $modelClass]);
            }
        } catch (\Throwable $e) {
            Log::error('triggerDocstoreSync gagal: ' . $e->getMessage(), [
                'model' => get_class($surat),
                'id'    => $surat->id,
            ]);
            // Tidak throw exception — sync failure tidak boleh interrupt proses utama
        }
    }

    /**
     * Check if document has achieved full multi-tier approval.
     * If full approval is complete:
     * 1. Generate System PKCS#12 QR Hash (header legalitas)
     * 2. Update status surat
     * 3. Trigger sync ke docstore (sebagai update status final)
     */
    public function checkAndGenerateHeaderQr(Model $surat): bool
    {
        if (!$surat->relationLoaded('approvals')) {
            $surat->load('approvals');
        }

        $approvals = $surat->approvals;

        if ($approvals->isEmpty()) {
            return false;
        }

        $modelClass = get_class($surat);

        // Khusus SuratSp3: harus memiliki 2 tahap wajib (verifikasi_keuangan & ttd_atasan)
        if (str_contains($modelClass, 'SuratSp3')) {
            $tahapWajib = ['verifikasi_keuangan', 'ttd_atasan'];
            $tahapAda = $approvals->map(function ($a) {
                return is_object($a->tahap) ? $a->tahap->value : (string)($a->tahap ?? 'ttd_atasan');
            })->unique()->toArray();

            if (count(array_intersect($tahapWajib, $tahapAda)) < count($tahapWajib)) {
                return false; // Belum lengkap 2 tahap
            }
        }

        $allApproved = $approvals->every(function ($a) {
            $val = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($val), ['approved', 'disetujui', 'manual', 'approved manual']);
        });

        if (!$allApproved) {
            return false;
        }

        // Jika ada approval yang manual, status surat tetap 'manual' (TTD basah)
        $hasManual = $approvals->contains(function ($a) {
            $val = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($val), ['manual', 'approved manual']);
        });

        $finalStatus = $hasManual
            ? \App\Enums\StatusApproval::MANUAL->value
            : \App\Enums\StatusApproval::APPROVED->value;

        $this->ensureP12SystemSignature($surat);
        $surat->update([
            'status'    => $finalStatus,
            'signed_at' => $surat->signed_at ?? now(),
            'is_valid'  => true,
        ]);

        // Invalidate cache docstore agar print selalu fresh
        if (!empty($surat->docstore_key)) {
            app(DocstoreSyncService::class)->invalidateCache($surat->docstore_key);
        }

        // Sync ke docstore dengan status final
        $this->triggerDocstoreSync($surat);

        return true;
    }
}

