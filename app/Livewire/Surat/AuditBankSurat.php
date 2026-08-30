<?php

namespace App\Livewire\Surat;

use App\Services\DocstoreSyncService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class AuditBankSurat extends Component
{
    /**
     * Filter type surat
     * @var string 'all'|'cuti'|'sp3'
     */
    #[Url]
    public string $filterType = 'all';

    /**
     * Filter status
     * @var string 'all'|'approved'|'pending'|'rejected'|'manual'
     */
    #[Url]
    public string $filterStatus = 'all';

    /** @var string Kata pencarian nomor surat / docstore_key */
    #[Url]
    public string $search = '';

    /** @var string|null Dari tanggal (YYYY-MM-DD) */
    #[Url]
    public ?string $dateFrom = null;

    /** @var string|null Sampai tanggal (YYYY-MM-DD) */
    #[Url]
    public ?string $dateTo = null;

    /** @var int Halaman saat ini */
    public int $page = 1;

    /** @var int Jumlah item per halaman */
    public int $perPage = 20;

    /** @var bool Apakah sedang loading data */
    public bool $loading = false;

    /** @var string|null Pesan error jika koneksi ke docstore gagal */
    public ?string $errorMsg = null;

    /** @var array|null Data dari docstore */
    public ?array $docstoreResult = null;

    /** @var bool Apakah data sudah berhasil dimuat */
    public bool $dataLoaded = false;

    public function mount(): void
    {
        $this->loadData();
    }

    /**
     * Load data dari docstore setiap kali filter berubah.
     */
    public function loadData(): void
    {
        $this->loading = true;
        $this->errorMsg = null;

        try {
            $syncService = app(DocstoreSyncService::class);
            $result = $syncService->listFromDocstore(
                type: $this->filterType,
                status: $this->filterStatus,
                page: $this->page,
                perPage: $this->perPage,
                search: $this->search ?: null,
                dateFrom: $this->dateFrom ?: null,
                dateTo: $this->dateTo ?: null,
            );

            if ($result['success'] ?? false) {
                $this->docstoreResult = $result;
                $this->dataLoaded = true;
            } else {
                $this->errorMsg = 'Gagal memuat data dari bank surat. Pastikan server docstore aktif.';
                $this->docstoreResult = null;
            }
        } catch (\Throwable $e) {
            $this->errorMsg = 'Koneksi ke bank surat gagal: ' . $e->getMessage();
            $this->docstoreResult = null;
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Reset ke halaman 1 saat filter berubah.
     */
    public function updatedFilterType(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    public function updatedFilterStatus(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    public function updatedDateFrom(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    public function updatedDateTo(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    /** Pindah ke halaman sebelumnya */
    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadData();
        }
    }

    /** Pindah ke halaman berikutnya */
    public function nextPage(): void
    {
        $meta = $this->docstoreResult['meta'] ?? [];
        if ($this->page < ($meta['last_page'] ?? 1)) {
            $this->page++;
            $this->loadData();
        }
    }

    /** Refresh manual */
    public function refresh(): void
    {
        $this->loadData();
    }

    #[Computed]
    public function documents(): array
    {
        return $this->docstoreResult['data'] ?? [];
    }

    #[Computed]
    public function paginationMeta(): array
    {
        return $this->docstoreResult['meta'] ?? [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $this->perPage,
            'total' => 0,
        ];
    }

    /**
     * Hitung ringkasan statistik dari data yang ditampilkan
     */
    #[Computed]
    public function stats(): array
    {
        $docs = $this->docstoreResult['data'] ?? [];
        return [
            'total'    => $this->docstoreResult['meta']['total'] ?? 0,
            'approved' => collect($docs)->where('status', 'approved')->count(),
            'pending'  => collect($docs)->whereIn('status', ['pending', 'proses'])->count(),
            'rejected' => collect($docs)->where('status', 'rejected')->count(),
            'manual'   => collect($docs)->where('status', 'manual')->count(),
        ];
    }

    public function getVerifyUrl(string $docstoreKey): string
    {
        $verifyBaseUrl = config('services.docstore.verify_app_url', env('VERIFY_APP_URL', 'https://verify.makroboi.site'));
        return rtrim($verifyBaseUrl, '/') . '/?key=' . $docstoreKey;
    }

    public function render()
    {
        return view('livewire.surat.audit-bank-surat')
            ->title('Audit Bank Surat — Docstore');
    }
}
