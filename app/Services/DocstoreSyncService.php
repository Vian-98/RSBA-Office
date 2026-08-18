<?php

namespace App\Services;

use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratCuti;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DocstoreSyncService
{
    protected string $baseUrl;
    protected string $apiUrl;
    protected string $apiToken;
    protected string $clientId;
    protected string $clientSecret;
    protected string $hmacSecret;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl      = rtrim(env('DOCSTORE_BASE_URL', 'http://localhost:8000'), '/');
        $this->apiUrl       = rtrim(env('DOCSTORE_API_URL', 'http://localhost:8000/api'), '/');
        $this->apiToken     = env('DOCSTORE_API_TOKEN', '');
        $this->clientId     = env('DOCSTORE_OAUTH_CLIENT_ID', '');
        $this->clientSecret = env('DOCSTORE_OAUTH_CLIENT_SECRET', '');
        $this->hmacSecret   = env('DOCSTORE_HMAC_SECRET', '');
        $this->verifySsl    = filter_var(env('DOCSTORE_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Dapatkan OAuth2 M2M Access Token (Client Credentials Grant) dari Docstore dengan caching dinamis.
     */
    public function getM2mToken(): ?string
    {
        if (!empty($this->apiToken)) {
            return $this->apiToken;
        }

        if (empty($this->clientId) || empty($this->clientSecret)) {
            Log::warning('Docstore OAuth M2M Client ID / Secret belum dikonfigurasi di .env');
            return null;
        }

        $cacheKey = 'docstore_m2m_token_' . md5($this->clientId);

        return Cache::remember($cacheKey, now()->addHours(6), function () {
            try {
                $oauthUrl = $this->baseUrl . '/oauth/token';

                $response = Http::withOptions(['verify' => $this->verifySsl])
                    ->asForm()
                    ->timeout(10)
                    ->post($oauthUrl, [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'scope'         => 'docstore:sync docstore:read',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }

                Log::error('Gagal mendapatkan Docstore M2M Token: ' . $response->body(), [
                    'status' => $response->status()
                ]);
                return null;
            } catch (\Throwable $e) {
                Log::error('Koneksi OAuth Docstore gagal: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Sync Surat SP3 ke docstore (bank surat).
     * Dipanggil setiap kali ada perubahan status pada SP3.
     */
    public function syncSp3(SuratSp3 $surat): bool
    {
        // Reload fresh model dengan relasi
        $surat = SuratSp3::with(['approvals.users.karyawan', 'details'])->findOrFail($surat->id);

        $content = [
            'no'         => $surat->no,
            'tahun'      => $surat->tahun,
            'tgl'        => $surat->tgl,
            'rekanan'    => $surat->rekanan,
            'bayar'      => $surat->bayar,
            'keterangan' => $surat->keterangan,
            'disetujui'  => optional(\App\Models\Sdm\Karyawan::find($surat->disetujui))->nama ?? 'Sistem',
            'jabatan'    => $surat->jabatan,
            'items'      => $surat->details->map(fn($det) => [
                'keterangan' => $det->keterangan,
                'nominal'    => $det->nominal,
            ])->toArray(),
        ];

        $signatures = $this->buildSp3Signatures($surat);

        $payload = [
            'document_type'   => 'sp3',
            'document_id'     => $surat->id,
            'document_number' => $surat->no,
            'status'          => is_object($surat->status) ? $surat->status->value : $surat->status,
            'content'         => $content,
            'signatures'      => $signatures,
        ];

        $result = $this->sendToDocstore($payload);

        // Simpan docstore_key ke record surat jika berhasil dan key diterima
        if ($result['success'] && !empty($result['docstore_key'])) {
            $surat->updateQuietly([
                'docstore_key'       => $result['docstore_key'],
                'docstore_synced_at' => now(),
            ]);
        }

        return $result['success'];
    }

    /**
     * Sync Surat Cuti ke docstore (bank surat).
     * Dipanggil setiap kali ada perubahan status pada Cuti.
     */
    public function syncCuti(SuratCuti $surat): bool
    {
        // Reload fresh model dengan relasi
        $surat = SuratCuti::with(['karyawan.jabatan', 'jenis', 'approvals.karyawan.jabatan'])->findOrFail($surat->id);

        $karyawan = $surat->karyawan;
        $content  = [
            'no_surat'          => $surat->no_surat,
            'tgl_surat'         => $surat->tgl_surat,
            'tgl_mulai'         => $surat->tgl_mulai,
            'tgl_akhir'         => $surat->tgl_akhir,
            'tgl_cuti'          => $surat->tgl_cuti,
            'lama_cuti'         => $surat->lama_cuti,
            'urgensi'           => $surat->urgensi,
            'keterangan'        => $surat->keterangan,
            'alamat'            => $surat->alamat,
            'jenis_cuti'        => optional($surat->jenis)->nama,
            'karyawan_name'     => optional($karyawan)->nama,
            'karyawan_nip'      => optional($karyawan)->nip,
            'karyawan_hp'       => optional($karyawan)->hp,
            'karyawan_jabatan'  => optional(optional($karyawan)->jabatan?->first())->nama,
        ];

        $signatures = $this->buildCutiSignatures($surat);

        $payload = [
            'document_type'   => 'cuti',
            'document_id'     => $surat->id,
            'document_number' => $surat->no_surat,
            'status'          => is_object($surat->status) ? $surat->status->value : $surat->status,
            'content'         => $content,
            'signatures'      => $signatures,
        ];

        $result = $this->sendToDocstore($payload);

        // Simpan docstore_key ke record surat jika berhasil dan key diterima
        if ($result['success'] && !empty($result['docstore_key'])) {
            $surat->updateQuietly([
                'docstore_key'       => $result['docstore_key'],
                'docstore_synced_at' => now(),
            ]);
        }

        return $result['success'];
    }

    /**
     * Sync dokumen Tanda Tangan Digital (PDF) ke docstore (bank surat).
     */
    public function syncDigitalSignatureDoc(
        \App\Models\DigitalSignatureDocument $doc,
        string $pdfBase64,
        array $signatureData = []
    ): bool {
        // Extract stamp metadata if JSON encoded
        $stampMeta = json_decode($doc->keterangan ?? '', true) ?: [];

        $content = [
            'title'                        => $doc->title,
            'document_number'              => $doc->document_number,
            'file_name'                    => $doc->file_name,
            'file_size_bytes'              => $doc->file_size,
            'byte_counter_hash'            => $doc->byte_counter_hash,
            'original_byte_counter_hash'   => $signatureData['original_byte_counter_hash'] ?? $signatureData['original_data'] ?? null,
            'keterangan'                   => $doc->keterangan,
            'uploader_name'                => optional($doc->user)->name ?? 'User',
            'stamp_x'                      => $signatureData['stamp_x'] ?? ($stampMeta['stamp_x'] ?? 70),
            'stamp_y'                      => $signatureData['stamp_y'] ?? ($stampMeta['stamp_y'] ?? 75),
            'stamp_scale'                  => $signatureData['stamp_scale'] ?? ($stampMeta['stamp_scale'] ?? 100),
            'stamp_position'               => $signatureData['stamp_position'] ?? ($stampMeta['stamp_position'] ?? 'c_right'),
            'pdf_base64'                   => $pdfBase64,
        ];

        $signatures = [
            [
                'signature_hash' => $doc->signature_hash,
                'original_data'  => $signatureData['original_data'] ?? $doc->byte_counter_hash,
                'signature'      => $signatureData['signature'] ?? 'MOCK_SIGNATURE',
                'data_hash'      => $signatureData['data_hash'] ?? $doc->byte_counter_hash,
                'algorithm'      => 'sha256',
                'public_key'     => $signatureData['public_key'] ?? 'MOCK_PUBLIC_KEY',
                'signer_name'    => optional($doc->user)->name ?? 'Signer',
                'signer_role'    => 'Penandatangan Digital',
                'status'         => 'APPROVED',
                'signed_at'      => now()->toIso8601String(),
            ]
        ];

        $payload = [
            'document_type'   => 'digital_signature',
            'document_id'     => $doc->id,
            'document_number' => $doc->document_number,
            'status'          => 'APPROVED',
            'content'         => $content,
            'signatures'      => $signatures,
        ];

        $result = $this->sendToDocstore($payload);

        if ($result['success'] && !empty($result['docstore_key'])) {
            $doc->update([
                'docstore_key' => $result['docstore_key'],
                'status'       => 'synced',
            ]);
            return true;
        }

        return false;
    }


    /**
     * Ambil data surat dari docstore berdasarkan docstore_key.
     * Digunakan oleh PrintCuti dan PrintSp3 untuk menarik data cetak dari bank surat.
     * Data di-cache selama 5 menit untuk mengurangi beban request ke docstore.
     *
     * @return array|null — null jika tidak ditemukan atau error
     */
    public function fetchFromDocstore(string $docstoreKey): ?array
    {
        $cacheKey = 'docstore_doc_' . $docstoreKey;

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($docstoreKey) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->timeout(10)
                    ->get($this->apiUrl . '/documents/' . $docstoreKey);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('Docstore fetch failed', [
                    'docstore_key' => $docstoreKey,
                    'status'       => $response->status(),
                    'body'         => $response->body(),
                ]);
                return null;
            } catch (\Throwable $e) {
                Log::error('Docstore fetch error: ' . $e->getMessage(), [
                    'docstore_key' => $docstoreKey,
                ]);
                return null;
            }
        });
    }

    /**
     * Invalidasi cache untuk docstore_key tertentu.
     * Dipanggil setelah sync berhasil agar data print selalu fresh.
     */
    public function invalidateCache(string $docstoreKey): void
    {
        Cache::forget('docstore_doc_' . $docstoreKey);
    }

    /**
     * List semua surat dari docstore (untuk halaman audit/laporan).
     */
    public function listFromDocstore(
        string $type = 'all',
        string $status = 'all',
        int $page = 1,
        int $perPage = 25,
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        try {
            $params = [
                'type'     => $type,
                'status'   => $status,
                'page'     => $page,
                'per_page' => $perPage,
            ];
            if ($search) $params['search'] = $search;
            if ($dateFrom) $params['date_from'] = $dateFrom;
            if ($dateTo) $params['date_to'] = $dateTo;

            $response = Http::withToken($this->apiToken)
                ->timeout(15)
                ->get($this->apiUrl . '/documents', $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Docstore list failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return ['success' => false, 'data' => [], 'meta' => []];
        } catch (\Throwable $e) {
            Log::error('Docstore list error: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'meta' => []];
        }
    }

    // =============================================
    // Private Helpers
    // =============================================

    /**
     * Kirim payload ke docstore dengan OAuth2 M2M Token dan HMAC Anti-Replay signing.
     * @return array ['success' => bool, 'docstore_key' => ?string]
     */
    protected function sendToDocstore(array $payload): array
    {
        try {
            $token = $this->getM2mToken();
            $jsonPayload = json_encode($payload);
            $timestamp = time();

            // Hitung HMAC dari timestamp . '.' . jsonPayload
            $headers = [
                'Content-Type' => 'application/json',
                'X-Timestamp'  => (string)$timestamp,
            ];
            if (!empty($this->hmacSecret)) {
                $payloadToSign = $timestamp . '.' . $jsonPayload;
                $headers['X-Payload-Signature'] = hash_hmac('sha256', $payloadToSign, $this->hmacSecret);
            }

            $request = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->timeout(15);

            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->withBody($jsonPayload, 'application/json')
                ->post($this->apiUrl . '/documents');

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success'      => true,
                    'docstore_key' => $body['docstore_key'] ?? ($body['data']['docstore_key'] ?? null),
                ];
            }

            Log::error('Docstore sync failed: ' . $response->body(), [
                'status'  => $response->status(),
                'payload' => $payload,
            ]);
            return ['success' => false, 'docstore_key' => null];

        } catch (\Throwable $e) {
            Log::error('Docstore sync connection error: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);
            return ['success' => false, 'docstore_key' => null];
        }
    }

    /**
     * Bangun array signatures untuk SP3
     */
    protected function buildSp3Signatures(SuratSp3 $surat): array
    {
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
                // Sertakan approval tanpa signature (status pending/rejected tanpa tanda tangan)
                $signatures[] = [
                    'signature_hash' => 'pending_' . md5($surat->id . '_' . ($approval->disetujui ?? 0)),
                    'original_data'  => 'PENDING_APPROVAL',
                    'signature'      => 'PENDING_APPROVAL',
                    'data_hash'      => null,
                    'algorithm'      => 'sha256',
                    'public_key'     => 'PENDING',
                    'signer_name'    => optional($approval->users?->karyawan)->full_nama ?? optional($approval->users)->name ?? 'Pejabat',
                    'signer_role'    => $approval->jabatan ?? null,
                    'status'         => is_object($approval->status) ? $approval->status->value : ($approval->status ?? 'pending'),
                    'signed_at'      => $approval->approved_at ?? null,
                ];
                continue;
            }

            $log = SignatureLogs::where('sign_type', 'persetujuan_sp3')
                ->where('sign_id', $surat->id)
                ->where('user_id', $approval->disetujui)
                ->orderBy('id', 'desc')
                ->first();

            $certs = null;
            if ($log) {
                $certs = $log->certificate_id
                    ? SignatureCerts::find($log->certificate_id)
                    : SignatureCerts::where('user_id', $approval->disetujui)->latest('id')->first();
            }

            $originalData = $log ? $log->data : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $signature    = $log ? $log->signature : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $publicKey    = '';
            if ($certs) {
                $publicKey = $certs->public_key;
            } else {
                $fallbackCert = SignatureCerts::where('user_id', 1)->first();
                $publicKey    = $fallbackCert ? $fallbackCert->public_key : 'MOCK_PUBLIC_KEY';
            }

            $signatures[] = [
                'signature_hash' => $approval->signature_hash,
                'original_data'  => $originalData,
                'signature'      => $signature,
                'data_hash'      => $log ? $log->data_hash : null,
                'algorithm'      => $log ? ($log->algorithm ?? 'sha256') : 'sha256',
                'public_key'     => $publicKey,
                'signer_name'    => optional($approval->users?->karyawan)->full_nama ?? optional($approval->users)->name ?? 'Sistem',
                'signer_role'    => $approval->jabatan,
                'status'         => is_object($approval->status) ? $approval->status->value : $approval->status,
                'signed_at'      => $approval->approved_at ?? now()->toIso8601String(),
            ];
        }
        return $signatures;
    }

    /**
     * Bangun array signatures untuk Cuti
     */
    protected function buildCutiSignatures(SuratCuti $surat): array
    {
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
                // Sertakan approval tanpa signature (status pending/rejected tanpa tanda tangan)
                $signatures[] = [
                    'signature_hash' => 'pending_' . md5($surat->id . '_' . ($approval->disetujui_oleh ?? 0)),
                    'original_data'  => 'PENDING_APPROVAL',
                    'signature'      => 'PENDING_APPROVAL',
                    'data_hash'      => null,
                    'algorithm'      => 'sha256',
                    'public_key'     => 'PENDING',
                    'signer_name'    => optional($approval->karyawan)->full_nama ?? optional($approval->karyawan)->nama ?? 'Pejabat',
                    'signer_role'    => optional(optional($approval->karyawan)->jabatan?->first())->nama,
                    'status'         => is_object($approval->status) ? $approval->status->value : ($approval->status ?? 'pending'),
                    'signed_at'      => $approval->approved_at ?? null,
                ];
                continue;
            }

            $user = User::where('karyawan_id', $approval->disetujui_oleh)->first();

            $log = SignatureLogs::where('sign_type', 'surat_cuti_approval')
                ->where('sign_id', $surat->id)
                ->when($user, fn($q) => $q->where('user_id', $user->id))
                ->orderBy('id', 'desc')
                ->first();

            $certs = null;
            if ($user && $log) {
                $certs = $log->certificate_id
                    ? SignatureCerts::find($log->certificate_id)
                    : SignatureCerts::where('user_id', $user->id)->latest('id')->first();
            }

            $originalData = $log ? $log->data : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $signature    = $log ? $log->signature : 'MOCK_SIGNATURE_' . $approval->signature_hash;
            $publicKey    = '';
            if ($certs) {
                $publicKey = $certs->public_key;
            } else {
                $fallbackCert = SignatureCerts::where('user_id', 1)->first();
                $publicKey    = $fallbackCert ? $fallbackCert->public_key : 'MOCK_PUBLIC_KEY';
            }

            $signatures[] = [
                'signature_hash' => $approval->signature_hash,
                'original_data'  => $originalData,
                'signature'      => $signature,
                'data_hash'      => $log ? $log->data_hash : null,
                'algorithm'      => $log ? ($log->algorithm ?? 'sha256') : 'sha256',
                'public_key'     => $publicKey,
                'signer_name'    => optional($approval->karyawan)->full_nama ?? optional($approval->karyawan)->nama ?? 'Sistem',
                'signer_role'    => optional(optional($approval->karyawan)->jabatan?->first())->nama,
                'status'         => is_object($approval->status) ? $approval->status->value : $approval->status,
                'signed_at'      => $approval->approved_at ?? now()->toIso8601String(),
            ];
        }
        return $signatures;
    }

    /**
     * Helper sentral untuk memanggil endpoint Vault API di docstore
     */
    public function callVaultApi(string $method, string $endpointPath, array $payload = []): array
    {
        $token = $this->getM2mToken();
        if (!$token) {
            return ['status' => false, 'message' => 'Gagal mendapatkan OAuth2 M2M Token dari Docstore'];
        }

        try {
            $jsonPayload = json_encode($payload);
            $timestamp = time();

            $headers = [
                'Content-Type' => 'application/json',
                'X-Timestamp'  => (string)$timestamp,
            ];

            if (!empty($this->hmacSecret)) {
                $payloadToSign = $timestamp . '.' . $jsonPayload;
                $headers['X-Payload-Signature'] = hash_hmac('sha256', $payloadToSign, $this->hmacSecret);
            }

            $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpointPath, '/');
            $request = Http::withOptions(['verify' => $this->verifySsl])
                ->withToken($token)
                ->withHeaders($headers)
                ->timeout(10);

            if (strtoupper($method) === 'GET') {
                $response = $request->get($url, $payload);
            } else {
                $response = $request->withBody($jsonPayload, 'application/json')->post($url);
            }

            if ($response->status() === 401) {
                Cache::forget('docstore_m2m_token_' . md5($this->clientId));
                $newToken = $this->getM2mToken();

                if ($newToken) {
                    $request = Http::withOptions(['verify' => $this->verifySsl])
                        ->withToken($newToken)
                        ->withHeaders($headers)
                        ->timeout(10);
                    if (strtoupper($method) === 'GET') {
                        $response = $request->get($url, $payload);
                    } else {
                        $response = $request->withBody($jsonPayload, 'application/json')->post($url);
                    }
                }
            }

            return $response->json() ?? ['status' => false, 'message' => 'Response kosong dari Docstore'];
        } catch (\Throwable $e) {
            Log::error("Error callVaultApi ({$endpointPath}): " . $e->getMessage());
            return ['status' => false, 'message' => 'Koneksi ke Vault Docstore gagal: ' . $e->getMessage()];
        }
    }

    public function generateVaultCertificate(array $payload): array
    {
        return $this->callVaultApi('POST', 'vault/certificates/generate', $payload);
    }

    public function getActiveVaultCertificate(int $userId): ?array
    {
        $res = $this->callVaultApi('GET', "vault/certificates/{$userId}/active");
        return ($res['status'] ?? false) ? ($res['certificate'] ?? null) : null;
    }

    public function signVaultData(array $payload): array
    {
        return $this->callVaultApi('POST', 'vault/signatures/sign', $payload);
    }
}

