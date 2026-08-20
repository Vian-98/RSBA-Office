<?php

namespace App\Services\Docstore;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocstoreClient
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
        $this->baseUrl      = rtrim(env('DOCSTORE_BASE_URL', 'http://127.0.0.1:8000'), '/');
        $this->apiUrl       = rtrim(env('DOCSTORE_API_URL', 'http://127.0.0.1:8000/api'), '/');
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
                        'scope'         => '*',
                    ]);

                if ($response->successful()) {
                    $token = $response->json('access_token');
                    Log::info('Docstore M2M Token successfully retrieved and cached.');
                    return $token;
                }

                Log::error('Gagal mendapatkan Docstore M2M OAuth Token', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return null;
            } catch (\Throwable $e) {
                Log::error('Exception saat request Docstore M2M OAuth Token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Buat HMAC signature header untuk proteksi anti-tampering dan anti-replay.
     */
    public function buildHmacHeaders(string $method, string $path, string $rawBody = ''): array
    {
        $timestamp = (string) time();
        $token = $this->getM2mToken();

        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-Timestamp'  => $timestamp,
        ];

        if (!empty($token)) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        if (!empty($this->hmacSecret)) {
            $payloadToSign = $timestamp . '.' . $rawBody;
            $signature = hash_hmac('sha256', $payloadToSign, $this->hmacSecret);
            $headers['X-Payload-Signature'] = $signature;
        }

        return $headers;
    }

    /**
     * Kirim dokumen ke API Docstore (POST /api/documents).
     */
    public function postDocument(array $payload): ?array
    {
        try {
            $path = '/api/documents';
            $url  = $this->apiUrl . '/documents';
            $rawBody = json_encode($payload);

            $headers  = $this->buildHmacHeaders('POST', $path, $rawBody);
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->withBody($rawBody, 'application/json')
                ->timeout(15)
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Docstore POST successful', [
                    'document_number' => $payload['document_number'] ?? '-',
                    'docstore_key'    => $data['docstore_key'] ?? ($data['data']['docstore_key'] ?? null)
                ]);
                return $data;
            }

            Log::error('Docstore POST failed', [
                'status'  => $response->status(),
                'payload' => $payload,
                'body'    => $response->body()
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Exception saat sync ke Docstore: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Ambil data dokumen dari Docstore berdasarkan docstore_key.
     */
    public function fetchDocument(string $docstoreKey): ?array
    {
        $cacheKey = "docstore_doc_{$docstoreKey}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($docstoreKey) {
            try {
                $path = "/api/documents/{$docstoreKey}";
                $url  = $this->apiUrl . "/documents/{$docstoreKey}";

                $headers  = $this->buildHmacHeaders('GET', $path);
                $response = Http::withOptions(['verify' => $this->verifySsl])
                    ->withHeaders($headers)
                    ->timeout(10)
                    ->get($url);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning("Gagal mengambil data dari Docstore [key: {$docstoreKey}]", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            } catch (\Throwable $e) {
                Log::error("Exception fetch docstore [key: {$docstoreKey}]: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Ambil daftar dokumen dari Docstore untuk audit.
     */
    public function listDocuments(array $params = []): ?array
    {
        try {
            $path = '/api/documents';
            $url  = $this->apiUrl . '/documents';

            $headers  = $this->buildHmacHeaders('GET', $path);
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->timeout(10)
                ->get($url, array_filter($params));

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Gagal mengambil daftar dokumen dari Docstore', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Exception list docstore: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hapus cache dokumen lokal saat ada perubahan.
     */
    public function invalidateCache(string $docstoreKey): void
    {
        Cache::forget("docstore_doc_{$docstoreKey}");
    }

    // =========================================================================
    // VAULT DIGITAL SIGNATURE METHODS (Office Frontdoor → Docstore Vault)
    // =========================================================================

    /**
     * Generate sertifikat tanda tangan digital baru di Vault Docstore.
     */
    public function generateVaultCertificate(array $payload): ?array
    {
        try {
            $path = '/api/vault/certificates/generate';
            $url  = $this->apiUrl . '/vault/certificates/generate';

            $headers  = $this->buildHmacHeaders('POST', $path, $payload);
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->timeout(15)
                ->post($url, $payload);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Exception generateVaultCertificate: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Dapatkan sertifikat aktif user dari Vault Docstore.
     */
    public function getActiveVaultCertificate(int $userId): ?array
    {
        try {
            $path = "/api/vault/certificates/{$userId}/active";
            $url  = $this->apiUrl . "/vault/certificates/{$userId}/active";

            $headers  = $this->buildHmacHeaders('GET', $path);
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                $json = $response->json();
                return $json['certificate'] ?? $json['data'] ?? null;
            }
            return null;
        } catch (\Throwable $e) {
            Log::error("Exception getActiveVaultCertificate [user: {$userId}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tanda tangani data secara kriptografis menggunakan kunci privat di Vault Docstore.
     */
    public function signVaultData(array $payload): ?array
    {
        try {
            $path = '/api/vault/signatures/sign';
            $url  = $this->apiUrl . '/vault/signatures/sign';

            $headers  = $this->buildHmacHeaders('POST', $path, $payload);
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withHeaders($headers)
                ->timeout(15)
                ->post($url, $payload);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Exception signVaultData: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
