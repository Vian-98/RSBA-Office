<?php

namespace App\Services;

use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Models\Surat\SuratPerintahTugas;
use App\Services\Docstore\DocstoreClient;
use App\Services\Docstore\Contracts\DocumentSynchronizerInterface;
use App\Services\Docstore\Synchronizers\CutiSynchronizer;
use App\Services\Docstore\Synchronizers\Sp3Synchronizer;
use App\Services\Docstore\Synchronizers\BalasanPklSynchronizer;
use App\Services\Docstore\Synchronizers\BalasanPenelitianSynchronizer;
use App\Services\Docstore\Synchronizers\PerintahTugasSynchronizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * DocstoreSyncService
 *
 * Facade / Orchestrator untuk sinkronisasi seluruh jenis surat ke Bank Surat (Docstore Vault).
 * Komunikasi HTTP didelegasikan ke DocstoreClient, dan pemetaan payload didelegasikan
 * ke Synchronizer masing-masing jenis surat (Strategy Pattern).
 */
class DocstoreSyncService
{
    protected DocstoreClient $client;
    protected array $synchronizers = [];

    public function __construct(DocstoreClient $client)
    {
        $this->client = $client;
        $this->registerSynchronizers();
    }

    /**
     * Daftarkan synchronizer untuk setiap tipe surat.
     */
    protected function registerSynchronizers(): void
    {
        $this->synchronizers = [
            'cuti'               => new CutiSynchronizer($this->client),
            'sp3'                => new Sp3Synchronizer($this->client),
            'balasan_pkl'        => new BalasanPklSynchronizer($this->client),
            'balasan_penelitian' => new BalasanPenelitianSynchronizer($this->client),
            'perintah_tugas'     => new PerintahTugasSynchronizer($this->client),
        ];
    }

    /**
     * Dapatkan instance client HTTP Docstore.
     */
    public function getClient(): DocstoreClient
    {
        return $this->client;
    }

    /**
     * Dapatkan token M2M OAuth2.
     */
    public function getM2mToken(): ?string
    {
        return $this->client->getM2mToken();
    }

    /**
     * Sinkronisasi dokumen model secara dinamis berdasarkan tipe model.
     */
    public function syncDocument(Model $model): bool
    {
        foreach ($this->synchronizers as $synchronizer) {
            /** @var DocumentSynchronizerInterface $synchronizer */
            if ($synchronizer->supports($model)) {
                return $synchronizer->sync($model);
            }
        }

        Log::warning('Tidak ada synchronizer Docstore yang mendukung model: ' . get_class($model));
        return false;
    }

    /**
     * Sinkronisasi Surat Cuti.
     */
    public function syncCuti(SuratCuti $suratCuti): bool
    {
        return $this->synchronizers['cuti']->sync($suratCuti);
    }

    /**
     * Sinkronisasi Surat SP3.
     */
    public function syncSp3(SuratSp3 $suratSp3): bool
    {
        return $this->synchronizers['sp3']->sync($suratSp3);
    }

    /**
     * Sinkronisasi Surat Balasan PKL.
     */
    public function syncBalasanPkl(SuratBalasanPkl $suratBalasanPkl): bool
    {
        return $this->synchronizers['balasan_pkl']->sync($suratBalasanPkl);
    }

    /**
     * Sinkronisasi Surat Balasan Penelitian.
     */
    public function syncBalasanPenelitian(SuratBalasanPenelitian $suratBalasanPenelitian): bool
    {
        return $this->synchronizers['balasan_penelitian']->sync($suratBalasanPenelitian);
    }

    /**
     * Sinkronisasi Surat Perintah Tugas.
     */
    public function syncPerintahTugas(SuratPerintahTugas $suratPerintahTugas): bool
    {
        return $this->synchronizers['perintah_tugas']->sync($suratPerintahTugas);
    }

    /**
     * Sinkronisasi Dokumen Tanda Tangan Digital langsung ke Bank Surat Docstore.
     */
    public function syncDigitalSignatureDoc(Model $doc, string $pdfBase64, array $signatureData): bool
    {
        $payload = [
            'document_type'   => $doc->document_type ?? 'digital_signature',
            'document_id'     => $doc->id,
            'document_number' => $doc->document_number,
            'status'          => $doc->status ?? 'signed',
            'content'         => [
                'title'             => $doc->title,
                'file_name'         => $doc->file_name,
                'file_size'         => $doc->file_size,
                'byte_counter_hash' => $doc->byte_counter_hash,
                'keterangan'        => $doc->keterangan,
                'pdf_base64'        => $pdfBase64,
            ],
            'signatures'      => [
                [
                    'signature'      => $signatureData['signature'] ?? '',
                    'original_data'  => $signatureData['original_data'] ?? '',
                    'public_key'     => $signatureData['public_key'] ?? '',
                    'signer_name'    => auth()->user()?->name ?? 'User',
                    'signer_role'    => auth()->user()?->jabatan?->nama ?? 'Pegawai',
                    'status'         => 'VALID',
                    'signed_at'      => now()->toIso8601String(),
                    'signature_hash' => $doc->signature_hash,
                ]
            ],
        ];

        $response = $this->client->postDocument($payload);

        if ($response && ($response['success'] ?? false)) {
            $docstoreKey = $response['docstore_key'] ?? ($response['data']['docstore_key'] ?? null);
            if ($docstoreKey) {
                $doc->update(['docstore_key' => $docstoreKey]);
            }
            return true;
        }

        return false;
    }

    /**
     * Ambil data dokumen dari Docstore berdasarkan key (Source of Truth).
     */
    public function fetchFromDocstore(string $docstoreKey): ?array
    {
        return $this->client->fetchDocument($docstoreKey);
    }

    /**
     * Ambil daftar dokumen dari Docstore untuk keperluan audit.
     */
    public function listFromDocstore(
        string $type = 'all',
        string $status = 'all',
        int $page = 1,
        int $perPage = 20,
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): ?array {
        $params = [
            'type'      => $type !== 'all' ? $type : null,
            'status'    => $status !== 'all' ? $status : null,
            'page'      => $page,
            'per_page'  => $perPage,
            'search'    => $search,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];

        return $this->client->listDocuments($params);
    }

    /**
     * Invalidate cache dokumen docstore.
     */
    public function invalidateCache(string $docstoreKey): void
    {
        $this->client->invalidateCache($docstoreKey);
    }

    // =========================================================================
    // VAULT DIGITAL SIGNATURE PROXY
    // =========================================================================

    public function generateVaultCertificate(array $payload): ?array
    {
        return $this->client->generateVaultCertificate($payload);
    }

    public function getActiveVaultCertificate(int $userId): ?array
    {
        return $this->client->getActiveVaultCertificate($userId);
    }

    public function signVaultData(array $payload): ?array
    {
        return $this->client->signVaultData($payload);
    }
}
