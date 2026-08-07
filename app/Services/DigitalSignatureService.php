<?php

namespace App\Services;

use Throwable;
use Exception;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DigitalSignatureService
{
    private const DEFAULT_ALGORITHM = 'sha256';
    private const DEFAULT_EXPIRY_DAYS = 1095; // 3 years

    protected DocstoreSyncService $docstoreSyncService;

    public function __construct(DocstoreSyncService $docstoreSyncService)
    {
        $this->docstoreSyncService = $docstoreSyncService;
    }

    /**
     * Generate digital signature certificate via Docstore Vault (Frontdoor Proxy)
     */
    public function generate(
        User $user,
        string $nama,
        string $org,
        string $org_unit,
        string $email,
        ?string $password,
        int $exp = self::DEFAULT_EXPIRY_DAYS,
    ): array {
        try {
            $payload = [
                'user_id' => $user->id,
                'nama' => $nama,
                'org' => $org ?: 'RSBA',
                'org_unit' => $org_unit,
                'email' => $email,
                'password' => $password,
                'exp_days' => $exp,
            ];

            $res = $this->docstoreSyncService->generateVaultCertificate($payload);

            if ($res['status'] ?? false) {
                // Update previous local certs to inactive
                SignatureCerts::where('user_id', $user->id)->update(['is_active' => 0]);

                $vaultCertData = $res['certificate'] ?? [];

                // Create local cert reference metadata in Office DB
                $signatureCert = SignatureCerts::create([
                    'user_id' => $user->id,
                    'public_key' => $vaultCertData['public_key'] ?? '',
                    'cert_info' => $vaultCertData['cert_info'] ?? json_encode(['cn' => $nama]),
                    'p12_path' => 'vault://' . ($vaultCertData['id'] ?? $user->id),
                    'expired_at' => $vaultCertData['expired_at'] ?? now()->addDays($exp),
                    'is_active' => 1,
                ]);

                return [
                    'status' => true,
                    'message' => $res['message'] ?? 'Digital signature berhasil digenerate di Vault Docstore.',
                    'certificate' => $signatureCert,
                    'paths' => ['p12' => 'vault://' . ($vaultCertData['id'] ?? $user->id)]
                ];
            }

            return [
                'status' => false,
                'message' => $res['message'] ?? 'Gagal generate digital signature di Docstore Vault.',
                'error' => $res['message'] ?? 'Unknown error'
            ];
        } catch (Throwable $e) {
            Log::error('Error in DigitalSignatureService::generate: ' . $e->getMessage(), ['userId' => $user->id]);
            return [
                'status' => false,
                'message' => "Gagal generate digital signature. ({$e->getMessage()})",
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get active certificate for user (Frontdoor Proxy)
     */
    public function getActiveCertificate(int $userId)
    {
        $vaultCert = $this->docstoreSyncService->getActiveVaultCertificate($userId);

        if ($vaultCert) {
            // Return an object compatible with SignatureCerts model interface
            $certObj = SignatureCerts::where('user_id', $userId)->where('is_active', 1)->first();
            if (!$certObj) {
                $certObj = new SignatureCerts([
                    'user_id' => $userId,
                    'public_key' => $vaultCert['public_key'] ?? '',
                    'cert_info' => $vaultCert['cert_info'] ?? '{}',
                    'p12_path' => $vaultCert['p12_path'] ?? 'vault://' . ($vaultCert['id'] ?? 1),
                    'expired_at' => $vaultCert['expired_at'] ?? now()->addYears(3),
                    'is_active' => 1,
                ]);
                $certObj->id = $vaultCert['id'] ?? 1;
            }
            return $certObj;
        }

        // Fallback to local DB reference if available
        return SignatureCerts::where('user_id', $userId)
            ->where('is_active', 1)
            ->latest('id')
            ->first();
    }

    /**
     * Sign data using Vault certificate (Frontdoor Proxy)
     */
    public function signData(
        User $user,
        $data,
        ?string $type,
        int $id,
        string $algorithm = self::DEFAULT_ALGORITHM,
        ?string $password = null,
    ): array {
        try {
            if (!is_string($data)) {
                $data = json_encode($data);
            }

            $payload = [
                'user_id' => $user->id,
                'data' => $data,
                'type' => $type ?? 'digital_signature',
                'origin_id' => $id,
                'algorithm' => $algorithm,
                'password' => $password,
                'signer_name' => optional($user->karyawan)->full_nama ?? $user->name,
                'signer_role' => optional(optional($user->karyawan)->jabatan?->first())->nama ?? 'Pengguna',
            ];

            $res = $this->docstoreSyncService->signVaultData($payload);

            if ($res['status'] ?? false) {
                $certObj = $this->getActiveCertificate($user->id);
                $certId = $certObj ? $certObj->id : 1;

                // Save local signature log for audit trail in Office DB
                SignatureLogs::firstOrCreate(
                    ['data_hash' => $res['data_hash']],
                    [
                        'data' => $data,
                        'signature' => $res['signature'],
                        'algorithm' => $algorithm,
                        'sign_type' => $type ?? 'digital_signature',
                        'sign_id' => $id,
                        'user_id' => $user->id,
                        'certificate_id' => $res['certificate_id'] ?? $certId,
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                        'user_agent' => request()->userAgent() ?? 'Office Frontdoor Proxy',
                    ]
                );

                return [
                    'status' => true,
                    'message' => 'Data berhasil ditandatangani oleh Vault Docstore.',
                    'signature' => $res['signature'],
                    'data_hash' => $res['data_hash'],
                    'algorithm' => $algorithm,
                    'certificate_id' => $res['certificate_id'] ?? $certId,
                    'signed_at' => $res['signed_at'] ?? now()->toIso8601String(),
                    'ip_address' => request()->ip() ?? '127.0.0.1'
                ];
            }

            return [
                'status' => false,
                'message' => "Gagal menandatangani data di Docstore Vault: " . ($res['message'] ?? 'Unknown error')
            ];
        } catch (Throwable $e) {
            Log::error('Error in DigitalSignatureService::signData: ' . $e->getMessage(), ['userId' => $user->id]);
            return [
                'status' => false,
                'message' => "Gagal menandatangani data. " . $e->getMessage()
            ];
        }
    }

    public function renewGenerate(
        User $user,
        string $existingPrivateKeyPath,
        string $password,
        int $expiryDays = self::DEFAULT_EXPIRY_DAYS
    ) {
        return $this->generate(
            user: $user,
            nama: optional($user->karyawan)->nama ?? $user->name,
            org: 'RSBA',
            org_unit: 'Kepegawaian',
            email: $user->email,
            password: $password,
            exp: $expiryDays
        );
    }

    public function verifyDataSignature(): bool
    {
        return true;
    }

    public function verifyFileSignature(): array
    {
        return [];
    }

    public function getCertificateInfo(): array
    {
        return [];
    }

    public function validatePassword(): bool
    {
        return true;
    }
}
