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
    protected string $clientId;
    protected string $clientSecret;
    protected string $hmacSecret;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl      = config('services.docstore.base_url', env('DOCSTORE_BASE_URL', 'http://localhost:8000'));
        $this->apiUrl       = config('services.docstore.api_url', env('DOCSTORE_API_URL', 'http://localhost:8000/api'));
        $this->clientId     = config('services.docstore.client_id', env('DOCSTORE_OAUTH_CLIENT_ID', ''));
        $this->clientSecret = config('services.docstore.client_secret', env('DOCSTORE_OAUTH_CLIENT_SECRET', ''));
        $this->hmacSecret   = config('services.docstore.hmac_secret', env('DOCSTORE_HMAC_SECRET', ''));
        $this->verifySsl    = config('services.docstore.verify_ssl', env('DOCSTORE_VERIFY_SSL', false));
    }

    /**
     * Dapatkan OAuth2 M2M Token via Client Credentials Grant.
     * Caching token dinamis berbasis expires_in server: max(60, expiresIn - 300)
     */
    public function getM2mToken(): ?string
    {
        $cacheKey = 'docstore_m2m_token_' . md5($this->clientId);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->asForm()
                ->timeout(10)
                ->post(rtrim($this->baseUrl, '/') . '/oauth/token', [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => 'docstore:sync docstore:read',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                $expiresIn = $data['expires_in'] ?? 3600;

                if ($token) {
                    $ttlSeconds = max(60, (int)$expiresIn - 300);
                    Cache::put($cacheKey, $token, $ttlSeconds);
                    return $token;
                }
            }

            Log::error('Gagal mendapatkan OAuth2 token dari Docstore', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Error koneksi OAuth2 token ke Docstore: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync Surat SP3 ke docstore (bank surat).
     */
    public function syncSp3(SuratSp3 $surat): bool
    {
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
     */
    public function syncCuti(SuratCuti $surat): bool
    {
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
        $content = [
            'title'             => $doc->title,
            'document_number'   => $doc->document_number,
            'file_name'         => $doc->file_name,
            'file_size_bytes'   => $doc->file_size,
            'byte_counter_hash' => $doc->byte_counter_hash,
            'keterangan'        => $doc->keterangan,
            'uploader_name'     => optional($doc->user)->name ?? 'User',
            'pdf_base64'        => $pdfBase64,
        ];

        $signatures = [
            [
                'signature_hash' => $doc->signature_hash,
                'original_data'  => $signatureData['original_data'] ?? $doc->byte_counter_hash,
                'signature'      => $signatureData['signature'] ?? 'MOCK_SIGNATURE',
                'data_hash'      => $doc->byte_counter_hash,
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
     */
    public function fetchFromDocstore(string $docstoreKey): ?array
    {
        $cacheKey = 'docstore_doc_' . $docstoreKey;

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
        }

        try {
            $token = $this->getM2mToken();
            $url = rtrim($this->apiUrl, '/') . '/documents/' . $docstoreKey;

            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->when($token, fn($q) => $q->withToken($token))
                ->timeout(10)
                ->get($url);

            // Handle token expired (401) -> Retry 1x dengan token baru
            if ($response->status() === 401) {
                Cache::forget('docstore_m2m_token_' . md5($this->clientId));
                $newToken = $this->getM2mToken();

                if ($newToken) {
                    $response = Http::withOptions(['verify' => $this->verifySsl])
                        ->withToken($newToken)
                        ->timeout(10)
                        ->get($url);
                }
            }

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json) && ($json['success'] ?? false)) {
                    Cache::put($cacheKey, $json, now()->addMinutes(5));
                    return $json;
                }
            }

            Log::warning('Docstore fetch failed', [
                'docstore_key' => $docstoreKey,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Docstore fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Invalidate cache dokumen lokal docstore jika ada perubahan/approval baru.
     */
    public function invalidateCache(?string $docstoreKey): void
    {
        if (!empty($docstoreKey)) {
            Cache::forget('docstore_doc_' . $docstoreKey);
        }
    }

    /**
     * Ambil daftar surat dari docstore (untuk Audit Bank Surat).
     */
    public function listFromDocstore(
        string $type = 'all',
        string $status = 'all',
        int $page = 1,
        int $perPage = 20,
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        try {
            $token = $this->getM2mToken();
            $queryParams = array_filter([
                'type'      => $type !== 'all' ? $type : null,
                'status'    => $status !== 'all' ? $status : null,
                'page'      => $page,
                'per_page'  => $perPage,
                'search'    => $search,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]);

            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->when($token, fn($q) => $q->withToken($token))
                ->timeout(10)
                ->get(rtrim($this->apiUrl, '/') . '/documents', $queryParams);

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

    /**
     * Kirim payload ke docstore dengan OAuth2 Token & HMAC Anti-Replay.
     */
    protected function sendToDocstore(array $payload): array
    {
        try {
            $token = $this->getM2mToken();
            if (!$token) {
                Log::error('Docstore sync dibatalkan: Gagal memperoleh OAuth2 token');
                return ['success' => false, 'docstore_key' => null];
            }

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

            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->withToken($token)
                ->withHeaders($headers)
                ->timeout(10)
                ->withBody($jsonPayload, 'application/json')
                ->post(rtrim($this->apiUrl, '/') . '/documents');

            // Handle token expiry (401) -> Retry 1x dengan token baru
            if ($response->status() === 401) {
                Cache::forget('docstore_m2m_token_' . md5($this->clientId));
                $newToken = $this->getM2mToken();

                if ($newToken) {
                    $response = Http::withOptions(['verify' => $this->verifySsl])
                        ->withToken($newToken)
                        ->withHeaders($headers)
                        ->timeout(10)
                        ->withBody($jsonPayload, 'application/json')
                        ->post(rtrim($this->apiUrl, '/') . '/documents');
                }
            }

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success'      => true,
                    'docstore_key' => $body['docstore_key'] ?? null,
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

    protected function buildSp3Signatures(SuratSp3 $surat): array
    {
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
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

    protected function buildCutiSignatures(SuratCuti $surat): array
    {
        $signatures = [];
        foreach ($surat->approvals as $approval) {
            if (!$approval->signature_hash) {
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
}
