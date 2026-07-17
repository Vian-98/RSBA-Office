<?php

namespace App\Livewire\Maintenance\Work;

use Throwable;
use Exception;
use App\Livewire\Forms\Asset\AssetBarangForm;
use App\Livewire\Forms\Distribusi\TransaksiForm as DistribusiTransaksiForm;
use App\Livewire\Forms\Asset\MaintcWorkForm as MaintcWorkForm;
use App\Livewire\Forms\Pembelian\PermintaanForm as PermintaanBeliForm;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Computed;
use App\Models\Assets\AssetBarang;
use App\Models\Gudang\Distribusi;
use Illuminate\Support\Facades\DB;
use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Maintenance\Work as MaintenanceWork;
use App\Models\Maintenance\WorkParts;
use TallStackUi\Traits\Interactions;
use App\Models\Master\Barang as MasterBarang;
use Livewire\Attributes\Isolate;

#[Lazy]
#[Isolate]
class Add extends Component
{
    use Interactions;
    use WithFileUploads;

    public ?MaintenanceWork $maintenanceWork;

    public MaintcWorkForm $formWork;
    // public AssetBarangForm $formAsset;
    public DistribusiTransaksiForm $formDistribusiTrans;
    public PermintaanBeliForm $formPermintaanBeli;
    public AssetBarangForm $formAssetBarang;

    #[Locked]
    public int $assetId; //Is Main Asset Id

    #[Locked]
    public ?int $workId;

    public $status;
    public ?string $keterangan = null;


    public array $optionsSetelahPerbaikan = [
        ['value' => 'baik', 'label' => 'Baik'],
        ['value' => 'rusak', 'label' => 'Rusak'],
    ];

    // Pilihan Parts yang akan diganti
    private array $optionsPenggantian = [
        [
            'value' => 'baru',
            'label' => 'Baru',
            'kode' => 'Baru',
            'description' => 'Penambahan part baru.',
            'kategori_id' => 'baru'
        ],
        [
            'value' => 'bhp',
            'label' => 'BHP',
            'kode' => 'BHP',
            'description' => 'Penggunaan BHP.',
            'kategori_id' => 'bhp'
        ],
    ];

    public $penggantian;
    public $partDigantiKategoriId;
    public $partDigantiNama;
    public $partDigantiAssetKode;

    public array $optionsParts = [
        ['value' => 'sparepart', 'label' => 'Sparepart'],
        ['value' => 'non_sparepart', 'label' => 'Non Sparepart'],
    ];

    public function rules(): array
    {
        return [
            'status' => 'required',
            'keterangan' => $this->status === 'rusak' ? 'required' : 'nullable',
        ];
    }

    public function mount($assetId, $workId)
    {
        $this->assetId = $assetId;

        $this->workId = $workId;

        $this->maintenanceWork = MaintenanceWork::with('asset')->findOrFail($this->workId);
    }

    // Dokumentasi Files Variable
    public ?array $dokumentasis = [];
    public $backup = []; // Temporary storage for uploaded multiple files

    // Dokumentasi Files 
    public function updatingDokumentasis(): void
    {
        // 2. Store the uploaded files in the temporary property
        $this->dokumentasis = $this->dokumentasis ?? [];
    }

    public function updatedDokumentasis(): void
    {
        if (empty($this->dokumentasis)) {
            $this->dokumentasis = [];
            return;
        }

        $file = Arr::flatten(array_merge($this->backup, [$this->dokumentasis]));
        $this->dokumentasis = collect($file)
            ->unique(
                fn(UploadedFile $item) => $item->getClientOriginalName()
            )->toArray();
    }


    // delete Dokumentasi files
    public function deleteUpload(array $content): void
    {
        if (! $this->dokumentasis) {
            return;
        }

        $files = Arr::wrap($this->dokumentasis);

        /** @var UploadedFile $file */
        $file = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() === $content['temporary_name'])->first();

        // 1. Here we delete the file. Even if we have a error here, we simply
        // ignore it because as long as the file is not persisted, it is
        // temporary and will be deleted at some point if there is a failure here.
        rescue(fn() => $file->delete(), report: false);

        $collect = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() !== $content['temporary_name']);

        // 2. We guarantee restore of remaining files regardless of upload
        // type, whether you are dealing with multiple or single uploads
        $this->dokumentasis = is_array($this->dokumentasis) ? $collect->toArray() : $collect->first();
    }


    #[Computed]
    public function itemComponents(): array
    {
        $assets =  AssetBarang::with(['barang'])
            ->select('id', 'kode', 'barang_id')
            ->whereNotNull('kode')
            ->where('main_asset_id', $this->assetId)
            ->where('status', 'diperbaiki')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->map(
                fn($item) => [
                    'value' => $item->id,
                    'label' => $item->barang->nama,
                    'kode' => $item->kode,
                    'description' => $item->kode,
                    'kategori_id' => $item->barang->kategori_id
                ]
            )
            ->toArray();
        return array_merge($this->optionsPenggantian, $assets);
    }


    #[Computed]
    public function permintaan()
    {
        return MaintenanceRequest::with(['jadwal', 'jadwal.work'])->where('asset_id', $this->assetId)->latest()->first();
    }


    public function getStokBarang($id): ?object
    {
        $barang = MasterBarang::with('satuan', 'stoks')
            ->where('id', $id)
            ->orWhere('sku', $id)
            ->withSum('stoks', 'stok')
            ->first();

        if ($barang) {
            $items = (object) [
                'id' => $barang->id,
                'sku' => $barang->sku,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan->nama,
                'stok' => $barang->stoks_sum_stok,
            ];
            return $items;
        }

        return null;
    }

    // Array temp to save multiple input
    public ?array $komponens = [];
    public ?array $komponens_diajukan = [];

    public function submit()
    {
        DB::beginTransaction();
        try {
            $this->validateSubmission();

            $dokumentasi = $this->storeDokumentasi();

            $this->updateMainAssetStatus();
            $this->updateAllComponentsStatus();

            $totalBiaya = 0;

            // Process available components
            if (!empty($this->komponens)) {
                $totalBiaya += $this->processAvailableComponents($dokumentasi);
            }

            // Process requested components (no stock)
            if (!empty($this->komponens_diajukan)) {
                $this->processRequestedComponents();
            }

            // Update all components status if no specific components were processed
            if (empty($this->komponens_diajukan) && empty($this->komponens)) {
                $this->updateAllComponentsStatus();
            }

            // End Work
            $this->formWork->endWork(
                maintenanceWork: $this->maintenanceWork,
                keterangan: $this->keterangan,
                dokumentasi: $dokumentasi,
                total_biaya: $totalBiaya
            );

            // Log sistem: Selesai Pekerjaan
            $reqId = $this->maintenanceWork->jadwal?->maintc_request_id ?? $this->maintenanceWork->jadwal()->first()?->maintc_request_id;
            if ($reqId) {
                \App\Models\Maintenance\TicketComment::create([
                    'request_id' => $reqId,
                    'user_id'    => auth()->id(),
                    'body'       => 'Pekerjaan diselesaikan oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name) . ($this->keterangan ? ' dengan catatan: ' . $this->keterangan : '') . ($totalBiaya > 0 ? ' (Biaya: Rp ' . number_format($totalBiaya, 2, ',', '.') . ')' : ''),
                    'type'       => 'log',
                ]);
            }

            DB::commit();

            $this->dispatch('close-modal', id: 'modal-maintenance-work-add');
            $this->dispatch('maintenance-work-finished');

            $this->toast()
                ->success('Berhasil', "Work Order <b>#{$this->maintenanceWork->id}</b> berhasil diselesaikan.")
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Terjadi Kesalahan', "<i>{$e->getMessage()}</i> <br> Line: {$e->getLine()} File: {$e->getFile()}")
                ->send();
        }
    }

    private function validateSubmission(): void
    {
        $this->validate([
            'status' => 'required',
            'keterangan' => $this->status === 'rusak' ? 'required' : 'nullable'
        ]);
    }

    private function storeDokumentasi(): array
    {
        if (empty($this->dokumentasis)) {
            return [];
        }

        return collect($this->dokumentasis)->map(function ($file) {
            return $file->store('maintenanceWorks/dokumentasi', 'public');
        })->toArray();
    }

    private function updateMainAssetStatus(): void
    {
        $this->maintenanceWork->asset->update(['status' => $this->status]);
    }

    private function processAvailableComponents(array $dokumentasi): float
    {
        $komponens = $this->komponens;

        // Create distribution transaction
        $distribusi = $this->prosesTransaksiDistribusi($komponens);

        // Process asset recording and work parts for asset items
        if (!empty($distribusi['distrb_asset_id'])) {
            $assetItems = $this->filterItemsByType($komponens, 'asset');
            $this->prosesPencatatanAsset($assetItems, $distribusi['distrb_asset_id']);

            $this->insertWorkParts(
                items: $assetItems,
                status: 'distributed',
                transId: $distribusi['distrb_asset_id']->id
            );
        }

        // Insert work parts for BHP items
        if (!empty($distribusi['distrb_bhp_id'])) {
            $bhpItems = $this->filterItemsByType($komponens, 'bhp');
            $this->insertWorkParts(
                items: $bhpItems,
                status: 'distributed',
                transId: $distribusi['distrb_bhp_id']->id
            );
        }

        $totalBiaya = $this->hitungTotalDistribusi($distribusi);

        return $totalBiaya;
    }

    private function processRequestedComponents(): void
    {
        $requestId = $this->prosesPengajuanPembelian($this->komponens_diajukan);

        $this->insertWorkParts(
            items: $this->komponens_diajukan,
            status: 'requested',
            transId: $requestId
        );
    }

    private function updateAllComponentsStatus(): void
    {
        $this->maintenanceWork->asset->components?->each(function ($komponen) {
            $komponen->update(['status' => $this->status]);
        });
    }

    private function filterItemsByType(array $items, string $type): array
    {
        return array_filter($items, function ($item) use ($type) {
            if ($type === 'asset') {
                return $item['penggantian'] !== 'bhp';
            }
            return $item['penggantian'] === 'bhp';
        });
    }

    private function prosesTransaksiDistribusi(array $items): array
    {
        if (empty($items)) {
            throw new Exception("Tidak ada item untuk didistribusikan");
        }

        $bhpItems = [];
        $assetItems = [];

        // Separate BHP and asset items
        foreach ($items as $komponen) {
            $itemData = [
                'id' => $komponen['barang_id'],
                'jumlah' => $komponen['qty']
            ];

            if ($komponen['penggantian'] === 'bhp') {
                $bhpItems[] = $itemData;
            } else {
                $assetItems[] = $itemData;
            }
        }

        $distribusiId = [];

        // Create BHP distribution transaction
        if (!empty($bhpItems)) {
            $distribusiId['distrb_bhp_id'] = $this->createDistribusiTransaction(
                $bhpItems,
                'keluar',
                "Transaksi pemakaian BHP saat maintenance asset #{$this->maintenanceWork->asset->kode}"
            );
        }

        // Create asset distribution transaction
        if (!empty($assetItems)) {
            $distribusiId['distrb_asset_id'] = $this->createDistribusiTransaction(
                $assetItems,
                'asset',
                "Transaksi untuk penggantian part asset #{$this->maintenanceWork->asset->kode}"
            );
        }

        return $distribusiId;
    }

    private function createDistribusiTransaction(array $items, string $sebagai, string $keterangan): object
    {
        $this->formDistribusiTrans->fill([
            'tgl_distribusi' => now()->toDateString(),
            'ruangan' => $this->maintenanceWork->asset->ruangan_id,
            'penerima' => auth()->id(),
            'sebagai' => $sebagai,
            'keterangan' => $keterangan,
            'cartItems' => $items
        ]);

        $this->formDistribusiTrans->validate();
        $this->formDistribusiTrans->simpan();

        $lastDistribusi = $this->formDistribusiTrans->lastDisribusi;
        $this->formDistribusiTrans->reset();
        return $lastDistribusi;
    }

    private function hitungTotalDistribusi(array $distribusi): float
    {
        $total = 0;

        $distributionTypes = ['distrb_asset_id', 'distrb_bhp_id'];

        foreach ($distributionTypes as $type) {
            if (!empty($distribusi[$type])) {
                foreach ($distribusi[$type]->details as $detail) {
                    $total += ($detail->jml * $detail->stoks->harga_satuan);
                }
            }
        }
        return $total;
    }

    private function prosesPengajuanPembelian(array $items): int
    {
        if (empty($items)) {
            throw new Exception("Tidak ada item untuk diajukan");
        }

        // Map items for procurement request
        $items_diajukan = array_map(function ($item) {
            return [
                'id' => $item['barang_id'],
                'jumlah' => $item['qty'],
                'specs' => []
            ];
        }, $items);

        // Set form properties
        $this->formPermintaanBeli->fill([
            'keterangan' => "Pembelian part untuk keperluan maintenance asset #{$this->maintenanceWork->asset->kode} [Work Order: {$this->maintenanceWork->id}]",
            'items' => $items_diajukan
        ]);

        // Process procurement request
        $this->formPermintaanBeli->validate();
        $this->formPermintaanBeli->simpan();

        $requestId = $this->formPermintaanBeli->lastRequestId;
        $this->formPermintaanBeli->reset();

        // TODO: Send notification

        return $requestId;
    }

    private function prosesPencatatanAsset(array $items, object $distribusi): void
    {
        // Set distributed items as components
        foreach ($distribusi->details as $det) {
            AssetBarang::where('distribusi_det_id', $det->id)
                ->update([
                    'jenis' => 'component',
                    'main_asset_id' => $this->assetId,
                    'level' => 1
                ]);
        }

        // Process each item
        foreach ($items as $item) {
            if ($item['penggantian'] === 'baru') {
                $this->recordNewAsset($item);
            } else {
                $this->replaceAsset($item);
            }
        }
    }

    private function recordNewAsset(array $item): void
    {
        $assetNew = AssetBarang::where('barang_id', $item['barang_id'])
            ->whereNull('kode')
            ->where('main_asset_id', $this->assetId)
            ->get();


        foreach ($assetNew as $asset) {
            $this->formAssetBarang->fill([
                'main' => $this->assetId,
                'tgl_catat' => now()->toDateString(),
                'status' => 'baik',
                'keterangan' => "Penambahan asset baru saat maintenance."
            ]);

            // $this->formAssetBarang->validate();
            $this->formAssetBarang->catatAsset($asset);
            $this->formAssetBarang->reset();
        }
    }

    private function replaceAsset(array $item): void
    {
        // Update old asset status
        $assetOld = AssetBarang::findOrFail($item['penggantian']);
        $assetOld->update([
            'status' => !empty($item['alasan']) ? $item['alasan'] : 'baik'
        ]);

        // Get new assets to record
        $assetNew = AssetBarang::where('barang_id', $item['barang_id'])
            ->where('main_asset_id', $this->assetId)
            ->where('id', '!=', $item['penggantian'])
            ->whereNull('kode')
            ->get();

        // Record each new asset
        foreach ($assetNew as $asset) {
            $this->formAssetBarang->fill([
                'main' => $this->assetId,
                'tgl_catat' => now()->toDateString(),
                'status' => 'baik',
                'keterangan' => "Untuk penggantian asset kode: #{$assetOld->kode} saat maintenance."
            ]);

            // $this->formAssetBarang->validate();
            $this->formAssetBarang->catatAsset($asset);
            $this->formAssetBarang->reset();
        }
    }

    private function insertWorkParts(?array $items, string $status, int $transId): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $forType = $this->determineForType($item['penggantian']);

            // Prepare base data
            $data = [
                'maintc_work_id' => $this->workId,
                'barang_id' => $item['barang_id'],
                'for' => $forType,
                'old_asset_id' => ($forType === 'replace') ? $item['penggantian'] : null,
                'qty' => $item['qty'],
                'status' => $status
            ];

            // Add status-specific data
            if ($status === 'distributed') {
                // Get asset with dengan barang id & transaksi id

                // TODO: fix on new asset dan harga satuan berdasarkan data Distribusi
                $data['new_asset_id'] = $item['new_asset_id'] ?? null;
                $data['distribusi_id'] = $transId;
                $data['harga_satuan'] = $item['harga_satuan'] ?? 0;
            } else {
                $data['harga_satuan'] = 0;
                $data['request_id'] = $transId;
            }

            WorkParts::create($data);
        }
    }

    private function determineForType($penggantian): string
    {
        if (is_int($penggantian)) {
            return 'replace';
        }
        return ($penggantian === 'baru') ? 'new' : 'bhp';
    }

    public function render()
    {
        return view('livewire.maintenance.work.add', [
            'routeNewPart' => route('api.barang.by_kategori', ['kategori' => $this->partDigantiKategoriId]),
        ]);
    }
}
