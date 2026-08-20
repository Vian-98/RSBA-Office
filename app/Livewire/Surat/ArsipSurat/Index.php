<?php

namespace App\Livewire\Surat\ArsipSurat;

use App\Models\Surat\SuratKategoriArsip;
use App\Services\DocstoreSyncService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Str;

#[Title('Arsip Surat - Bank Surat Docstore')]
#[Lazy]
class Index extends Component
{
    use WithPagination, Interactions;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
                <div class="space-y-2">
                    <div class="h-5 w-48 bg-slate-200 rounded-lg"></div>
                    <div class="h-3 w-80 bg-slate-100 rounded-lg"></div>
                </div>
                <div class="h-9 w-40 bg-indigo-100 rounded-xl"></div>
            </div>
            <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center space-y-3">
                <div class="inline-block p-4 rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Memuat data Arsip Surat dari Bank Surat Docstore...</p>
                <p class="text-xs text-slate-400">Sinkronisasi data real-time via API Docstore</p>
            </div>
        </div>
        HTML;
    }

    /** @var string Filter kategori/jenis arsip ('all' atau kode kategori) */
    #[Url]
    public string $filterType = 'all';

    /** @var string Filter status ('all', 'approved', 'manual', 'pending', 'rejected') */
    #[Url]
    public string $filterStatus = 'all';

    /** @var string Keyword pencarian */
    #[Url]
    public string $search = '';

    /** @var string|null Tanggal mulai */
    #[Url]
    public ?string $dateFrom = null;

    /** @var string|null Tanggal selesai */
    #[Url]
    public ?string $dateTo = null;

    /** @var int Halaman pagination */
    public int $page = 1;

    /** @var int Item per halaman */
    public int $perPage = 15;

    // --- State Modal Tambah Kategori Arsip ---
    public bool $modalKategori = false;
    public string $newNama = '';
    public string $newKode = '';
    public string $newDeskripsi = '';
    public string $newIcon = 'file-text';

    // --- State Drawer Detail Dokumen ---
    public bool $modalDetail = false;
    public ?array $selectedDoc = null;
    public bool $loadingDetail = false;

    protected $queryString = [
        'filterType'   => ['except' => 'all'],
        'filterStatus' => ['except' => 'all'],
        'search'       => ['except' => ''],
        'dateFrom'     => ['except' => null],
        'dateTo'       => ['except' => null],
        'page'         => ['except' => 1],
    ];

    public function updatedFilterType(): void
    {
        $this->page = 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->page = 1;
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedDateFrom(): void
    {
        $this->page = 1;
    }

    public function updatedDateTo(): void
    {
        $this->page = 1;
    }

    public function resetFilters(): void
    {
        $this->reset(['filterType', 'filterStatus', 'search', 'dateFrom', 'dateTo', 'page']);
    }

    public function openModalKategori(): void
    {
        $this->reset(['newNama', 'newKode', 'newDeskripsi', 'newIcon']);
        $this->newIcon = 'file-text';
        $this->modalKategori = true;
    }

    public function updatedNewNama($val): void
    {
        if (empty($this->newKode)) {
            $this->newKode = Str::slug($val, '_');
        }
    }

    public function simpanKategori(): void
    {
        $this->newKode = Str::slug($this->newKode ?: $this->newNama, '_');

        $this->validate([
            'newNama' => 'required|string|max:100',
            'newKode' => 'required|string|max:50|unique:surat_kategori_arsip,kode',
            'newDeskripsi' => 'nullable|string|max:255',
            'newIcon' => 'required|string|max:50',
        ], [
            'newNama.required' => 'Nama jenis/kategori arsip wajib diisi.',
            'newKode.required' => 'Kode kategori wajib diisi.',
            'newKode.unique'   => 'Kode kategori ini sudah digunakan.',
        ]);

        SuratKategoriArsip::create([
            'kode'        => $this->newKode,
            'nama'        => $this->newNama,
            'deskripsi'   => $this->newDeskripsi,
            'icon'        => $this->newIcon,
            'is_system'   => false,
            'is_active'   => true,
        ]);

        $this->modalKategori = false;
        $this->reset(['newNama', 'newKode', 'newDeskripsi', 'newIcon']);
        $this->toast()->success('Berhasil', "Jenis arsip '{$this->newNama}' berhasil ditambahkan.")->send();
    }

    public function showDetail(string $docstoreKey): void
    {
        $this->loadingDetail = true;
        $this->modalDetail = true;
        $this->selectedDoc = null;

        try {
            $syncService = app(DocstoreSyncService::class);
            $docData = $syncService->fetchFromDocstore($docstoreKey);
            
            if ($docData && ($docData['success'] ?? false)) {
                $this->selectedDoc = $docData;
            } else {
                $this->toast()->error('Gagal', 'Dokumen tidak ditemukan di bank surat (docstore).')->send();
                $this->modalDetail = false;
            }
        } catch (\Throwable $e) {
            $this->toast()->error('Error', 'Koneksi ke bank surat gagal: ' . $e->getMessage())->send();
            $this->modalDetail = false;
        } finally {
            $this->loadingDetail = false;
        }
    }

    public function render()
    {
        // 1. Ambil daftar kategori arsip aktif dari DB office
        $kategoriList = SuratKategoriArsip::where('is_active', true)->orderBy('is_system', 'desc')->get();

        // 2. Ambil data arsip dari bank surat docstore via service
        $docstoreData = null;
        $errorMessage = null;

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

            if ($result && ($result['success'] ?? false)) {
                $docstoreData = $result;
            } else {
                $errorMessage = 'Gagal terhubung ke bank surat Docstore (API Offline/Error).';
            }
        } catch (\Throwable $e) {
            $errorMessage = 'Gagal terhubung ke bank surat Docstore: ' . $e->getMessage();
        }

        return view('livewire.surat.arsip-surat.index', [
            'kategoriList' => $kategoriList,
            'docstoreData' => $docstoreData,
            'errorMessage' => $errorMessage,
        ]);
    }
}
