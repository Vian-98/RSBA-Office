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
     */
    public function ensureP12SystemSignature(Model $surat): string
    {
        if (!empty($surat->qr_hash)) {
            return $surat->qr_hash;
        }

        try {
            $systemUser = $this->systemCertificateService->getOrCreateSystemUser();

            $payloadData = [
                'document_type' => get_class($surat),
                'document_id'   => $surat->id,
                'no_surat'      => $surat->no_surat ?? $surat->no ?? $surat->id,
                'tgl_surat'     => $surat->tgl_surat ?? $surat->tgl ?? date('Y-m-d'),
                'signed_by_system_p12' => true,
                'timestamp'     => now()->toIso8601String(),
            ];

            // Sign using DigitalSignatureService with System User PKCS#12 Certificate
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
     * Check if document has achieved full multi-tier approval.
     * If full approval is complete, sign document using System PKCS#12 (.p12) Certificate
     * and generate official System Header QR Hash.
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

        $allApproved = $approvals->every(function ($a) {
            $val = is_object($a->status) ? $a->status->value : (string)$a->status;
            return in_array(strtolower($val), ['approved', 'disetujui', 'manual', 'approved manual']);
        });

        if (!$allApproved) {
            return false;
        }

        // Jika ada approval yang manual, status surat tetap 'manual' (TTD basah)
        // Hanya set 'approved' jika semua approval via sistem (bukan manual)
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

        return true;
    }
}
