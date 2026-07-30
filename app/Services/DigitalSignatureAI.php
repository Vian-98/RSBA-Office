<?php

namespace App\Services;

use App\Models\SignatureCerts;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class DigitalSignatureAI
{
    private const DEFAULT_KEY_SIZE = 2048;
    private const DEFAULT_ALGORITHM = 'sha256';
    private const DEFAULT_EXPIRY_DAYS = 1095; // 3 years
    private const TEMP_DIR_PREFIX = 'app/temp/';

    /**
     * Generate digital signature certificate for user
     */
    public function generateCertificate(
        User $user,
        string $password,
        string $organization,
        string $organizationalUnit,
        int $expiryDays = self::DEFAULT_EXPIRY_DAYS
    ): array {
        $tempDir = $this->createTempDirectory();

        try {
            $subject = $this->buildCertificateSubject($user, $organization, $organizationalUnit);
            $paths = $this->generateCertificateFiles($tempDir, $subject, $password, $expiryDays);
            $storagePaths = $this->storeCertificateFiles($user->id, $paths);
            $certData = $this->extractCertificateData($storagePaths['p12'], $password);
            $certificate = $this->saveCertificateRecord($user->id, $certData);

            return $this->successResponse('Digital signature berhasil digenerate', [
                'certificate' => $certificate,
                'paths' => $storagePaths
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal generate digital signature', $e->getMessage());
        } finally {
            $this->cleanupDirectory($tempDir);
        }
    }

    /**
     * Sign data and create digital signature
     */
    public function signData(
        string $data,
        int $userId,
        string $password,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): array {
        try {
            $certificate = $this->getActiveCertificate($userId);
            $privateKey = $this->extractPrivateKey($certificate->p12_path, $password);
            $signature = $this->createSignature($data, $privateKey, $algorithm);

            openssl_free_key($privateKey);

            return $this->successResponse('Data berhasil ditandatangani', [
                'signature' => base64_encode($signature),
                'data_hash' => hash($algorithm, $data),
                'algorithm' => $algorithm,
                'certificate' => $this->getCertificateInfo($certificate)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menandatangani data', $e->getMessage());
        }
    }

    /**
     * Sign file and create digital signature
     */
    public function signFile(
        string $filePath,
        int $userId,
        string $password,
        string $algorithm = self::DEFAULT_ALGORITHM,
        ?string $disk = null
    ): array {
        try {
            $fileContent = $this->readFile($filePath, $disk);
            $fileInfo = $this->getFileInfo($filePath, $fileContent);

            $signResult = $this->signData($fileContent, $userId, $password, $algorithm);

            if (!$signResult['success']) {
                throw new Exception($signResult['message']);
            }

            return $this->successResponse('File berhasil ditandatangani', array_merge(
                ['file' => $fileInfo],
                $signResult['data']
            ));
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menandatangani file', $e->getMessage());
        }
    }

    /**
     * Verify digital signature
     */
    public function verifySignature(
        string $data,
        string $signatureBase64,
        int $userId,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): array {
        try {
            $certificate = $this->getCertificateByUserId($userId);
            $publicKey = $this->extractPublicKey($certificate->public_key);
            $signature = base64_decode($signatureBase64);

            $verificationResult = openssl_verify($data, $signature, $publicKey, $algorithm);
            openssl_free_key($publicKey);

            $isValid = $verificationResult === 1;
            $message = $this->getVerificationMessage($verificationResult);

            if ($verificationResult === -1) {
                throw new Exception($message);
            }

            return $this->successResponse($message, [
                'is_valid' => $isValid,
                'data_hash' => hash($algorithm, $data),
                'algorithm' => $algorithm,
                'certificate' => $this->getCertificateInfo($certificate)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal memverifikasi signature', $e->getMessage());
        }
    }

    /**
     * Verify file signature
     */
    public function verifyFileSignature(
        string $filePath,
        string $signatureBase64,
        int $userId,
        string $algorithm = self::DEFAULT_ALGORITHM,
        ?string $disk = null
    ): array {
        try {
            $fileContent = $this->readFile($filePath, $disk);
            $fileInfo = $this->getFileInfo($filePath, $fileContent);

            $verifyResult = $this->verifySignature($fileContent, $signatureBase64, $userId, $algorithm);

            if (!$verifyResult['success']) {
                throw new Exception($verifyResult['message']);
            }

            return $this->successResponse($verifyResult['message'], array_merge(
                ['file' => $fileInfo],
                $verifyResult['data']
            ));
        } catch (Exception $e) {
            return $this->errorResponse('Gagal memverifikasi signature file', $e->getMessage());
        }
    }

    /**
     * Read and parse P12 certificate
     */
    public function readCertificate(
        string $p12Path,
        string $password,
        string $disk = 'certs'
    ): array {
        try {
            $p12Content = $this->readP12File($p12Path, $disk);
            $certs = $this->parseP12Content($p12Content, $password);
            $certInfo = $this->parseCertificateInfo($certs['cert']);

            return $this->successResponse('Certificate berhasil dibaca', [
                'certificate' => $this->formatCertificateInfo($certInfo),
                'has_private_key' => isset($certs['pkey']),
                'public_key' => $certs['cert'] ?? null,
                'raw_info' => $certInfo
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal membaca certificate', $e->getMessage());
        }
    }

    /**
     * Validate certificate status
     */
    public function validateCertificate(int $certificateId): array
    {
        try {
            $certificate = $this->findCertificate($certificateId);
            $certInfo = json_decode($certificate->cert_info, true);
            $validation = $this->performCertificateValidation($certificate);

            return $this->successResponse('Validasi certificate berhasil', [
                'certificate_id' => $certificate->id,
                'user_id' => $certificate->user_id,
                'validation' => $validation,
                'info' => $this->formatStoredCertificateInfo($certificate, $certInfo)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal validasi certificate', $e->getMessage());
        }
    }

    /**
     * Create hash from data
     */
    public function createHash(string $data, string $algorithm = self::DEFAULT_ALGORITHM): array
    {
        try {
            $this->validateHashAlgorithm($algorithm);

            return $this->successResponse('Hash berhasil dibuat', [
                'hash' => hash($algorithm, $data),
                'algorithm' => $algorithm,
                'data_length' => strlen($data)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal membuat hash', $e->getMessage());
        }
    }

    /**
     * Compare two hashes securely
     */
    public function compareHashes(string $hash1, string $hash2): array
    {
        $isMatch = hash_equals($hash1, $hash2);

        return $this->successResponse(
            $isMatch ? 'Hash cocok' : 'Hash tidak cocok',
            ['is_match' => $isMatch]
        );
    }

    /**
     * Get active certificate for user
     */
    public function getActiveCertificate(int $userId): SignatureCerts
    {
        $certificate = SignatureCerts::where('user_id', $userId)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (!$certificate) {
            throw new Exception('Tidak ada certificate aktif untuk user ini');
        }

        return $certificate;
    }

    /**
     * Check if user has certificate
     */
    public function hasCertificate(int $userId): bool
    {
        return SignatureCerts::where('user_id', $userId)->exists();
    }

    /**
     * Revoke certificate
     */
    public function revokeCertificate(int $certificateId): bool
    {
        try {
            $certificate = $this->findCertificate($certificateId);

            if (Storage::disk('certs')->exists($certificate->p12_path)) {
                Storage::disk('certs')->delete($certificate->p12_path);
            }

            return $certificate->delete();
        } catch (Exception $e) {
            return false;
        }
    }

    // ==================== Private Helper Methods ====================

    private function buildCertificateSubject(User $user, string $org, string $orgUnit): string
    {
        return sprintf(
            "/C=ID/ST=Lampung/L=Bandar Lampung/O=%s/OU=%s/CN=%s/emailAddress=%s",
            $org,
            $orgUnit,
            $user->name,
            $user->email
        );
    }

    private function createTempDirectory(): string
    {
        $tempDir = storage_path(self::TEMP_DIR_PREFIX . Str::random(16));

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        return $tempDir;
    }

    private function generateCertificateFiles(
        string $tempDir,
        string $subject,
        string $password,
        int $expiryDays
    ): array {
        $paths = $this->defineCertificatePaths($tempDir);

        $this->executeOpenSSLCommands($paths, $subject, $password, $expiryDays);

        if (!file_exists($paths['p12'])) {
            throw new Exception('Gagal generate PKCS#12 file');
        }

        return $paths;
    }

    private function defineCertificatePaths(string $tempDir): array
    {
        return [
            'privateKey' => "{$tempDir}/private.key",
            'csr' => "{$tempDir}/request.csr",
            'cert' => "{$tempDir}/certificate.crt",
            'p12' => "{$tempDir}/certificate.p12"
        ];
    }

    private function executeOpenSSLCommands(
        array $paths,
        string $subject,
        string $password,
        int $expiryDays
    ): void {
        // Generate private key
        shell_exec("openssl genrsa -out {$paths['privateKey']} " . self::DEFAULT_KEY_SIZE);

        // Generate CSR
        shell_exec("openssl req -new -key {$paths['privateKey']} -out {$paths['csr']} -subj '{$subject}'");

        // Self-sign certificate
        shell_exec("openssl x509 -req -days {$expiryDays} -in {$paths['csr']} -signkey {$paths['privateKey']} -out {$paths['cert']}");

        // Create PKCS#12
        shell_exec("openssl pkcs12 -export -out {$paths['p12']} -inkey {$paths['privateKey']} -in {$paths['cert']} -password pass:{$password}");
    }

    private function storeCertificateFiles(int $userId, array $paths): array
    {
        $basePath = (string) $userId;
        $p12Path = "{$basePath}/p12/certificate-" . time() . '.p12';

        Storage::disk('certs')->put("{$basePath}/private.key", file_get_contents($paths['privateKey']));
        Storage::disk('certs')->put("{$basePath}/certificate.crt", file_get_contents($paths['cert']));
        Storage::disk('certs')->put($p12Path, file_get_contents($paths['p12']));

        return [
            'privateKey' => "{$basePath}/private.key",
            'cert' => "{$basePath}/certificate.crt",
            'p12' => $p12Path
        ];
    }

    private function extractCertificateData(string $p12Path, string $password): array
    {
        $p12Content = $this->readP12File($p12Path, 'certs');
        $certs = $this->parseP12Content($p12Content, $password);
        $certInfo = $this->parseCertificateInfo($certs['cert']);

        return [
            'public_key' => $certs['cert'],
            'cert_info' => json_encode($certInfo),
            'p12_path' => $p12Path,
            'expired_at' => date('Y-m-d H:i:s', $certInfo['validTo_time_t'])
        ];
    }

    private function saveCertificateRecord(int $userId, array $certData): SignatureCerts
    {
        return SignatureCerts::create([
            'user_id' => $userId,
            'public_key' => $certData['public_key'],
            'cert_info' => $certData['cert_info'],
            'p12_path' => $certData['p12_path'],
            'expired_at' => $certData['expired_at']
        ]);
    }

    private function extractPrivateKey(string $p12Path, string $password)
    {
        $p12Content = $this->readP12File($p12Path, 'certs');
        $certs = $this->parseP12Content($p12Content, $password);

        $privateKey = openssl_pkey_get_private($certs['pkey']);

        if (!$privateKey) {
            throw new Exception('Gagal mendapatkan private key');
        }

        return $privateKey;
    }

    private function extractPublicKey(string $publicKeyPem)
    {
        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if (!$publicKey) {
            throw new Exception('Gagal mendapatkan public key');
        }

        return $publicKey;
    }

    private function createSignature(string $data, $privateKey, string $algorithm): string
    {
        $signature = '';
        $success = openssl_sign($data, $signature, $privateKey, $algorithm);

        if (!$success) {
            throw new Exception('Gagal membuat signature');
        }

        return $signature;
    }

    private function readP12File(string $path, string $disk): string
    {
        $content = Storage::disk($disk)->get($path);

        if (!$content) {
            throw new Exception('Gagal membaca P12 file');
        }

        return $content;
    }

    private function parseP12Content(string $content, string $password): array
    {
        $certs = [];

        if (!openssl_pkcs12_read($content, $certs, $password)) {
            throw new Exception('Gagal parse P12 file. Password salah?');
        }

        return $certs;
    }

    private function parseCertificateInfo(string $cert): array
    {
        $certInfo = openssl_x509_parse($cert);

        if (!$certInfo) {
            throw new Exception('Gagal parse certificate info');
        }

        return $certInfo;
    }

    private function readFile(string $filePath, ?string $disk): string
    {
        if ($disk) {
            if (!Storage::disk($disk)->exists($filePath)) {
                throw new Exception('File tidak ditemukan');
            }
            return Storage::disk($disk)->get($filePath);
        }

        if (!file_exists($filePath)) {
            throw new Exception('File tidak ditemukan');
        }

        return file_get_contents($filePath);
    }

    private function getFileInfo(string $filePath, string $content): array
    {
        return [
            'name' => basename($filePath),
            'size' => strlen($content),
            'path' => $filePath
        ];
    }

    private function getCertificateByUserId(int $userId): SignatureCerts
    {
        $certificate = SignatureCerts::where('user_id', $userId)->latest()->first();

        if (!$certificate) {
            throw new Exception('Certificate tidak ditemukan');
        }

        return $certificate;
    }

    private function findCertificate(int $certificateId): SignatureCerts
    {
        $certificate = SignatureCerts::find($certificateId);

        if (!$certificate) {
            throw new Exception('Certificate tidak ditemukan');
        }

        return $certificate;
    }

    private function getCertificateInfo(SignatureCerts $certificate): array
    {
        $certInfo = json_decode($certificate->cert_info);

        return [
            'id' => $certificate->id,
            'expired_at' => $certificate->expired_at,
            'is_expired' => now()->gt($certificate->expired_at),
            'subject' => $certInfo->subject ?? null
        ];
    }

    private function formatCertificateInfo(array $certInfo): array
    {
        return [
            'subject' => $certInfo['subject'] ?? null,
            'issuer' => $certInfo['issuer'] ?? null,
            'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t'] ?? 0),
            'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t'] ?? 0),
            'serial_number' => $certInfo['serialNumber'] ?? null,
            'is_expired' => ($certInfo['validTo_time_t'] ?? 0) < time(),
            'purposes' => $certInfo['purposes'] ?? null
        ];
    }

    private function formatStoredCertificateInfo(SignatureCerts $certificate, array $certInfo): array
    {
        return [
            'subject' => $certInfo['subject'] ?? null,
            'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t'] ?? 0),
            'valid_to' => $certificate->expired_at,
            'serial_number' => $certInfo['serialNumber'] ?? null
        ];
    }

    private function performCertificateValidation(SignatureCerts $certificate): array
    {
        $fileExists = Storage::disk('certs')->exists($certificate->p12_path);
        $isExpired = now()->gt($certificate->expired_at);
        $daysUntilExpiry = now()->diffInDays($certificate->expired_at, false);

        return [
            'file_exists' => $fileExists,
            'is_expired' => $isExpired,
            'days_until_expiry' => $daysUntilExpiry,
            'status' => $fileExists && !$isExpired ? 'valid' : 'invalid'
        ];
    }

    private function getVerificationMessage(int $result): string
    {
        return match ($result) {
            1 => 'Signature valid',
            0 => 'Signature tidak valid',
            default => 'Error saat verifikasi signature'
        };
    }

    private function validateHashAlgorithm(string $algorithm): void
    {
        if (!in_array($algorithm, hash_algos())) {
            throw new Exception("Hash algorithm tidak didukung: {$algorithm}");
        }
    }

    private function cleanupDirectory(string $directory): void
    {
        if (File::exists($directory)) {
            File::deleteDirectory($directory);
        }
    }

    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    private function errorResponse(string $message, string $error = ''): array
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($error) {
            $response['error'] = $error;
        }

        return $response;
    }
}
