<?php

namespace App\Livewire\Maintenance\Permintaan;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use App\Models\Assets\AssetBarang;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;
    use WithFileUploads;

    public ?AssetBarang $assetBarang;

    public $target_asset_id;
    public array $targetAssetOptions = [];

    public $priority = 'normal';
    public array $priorityPermintaan = [
        ['value' => 'normal', 'label' => 'Normal'],
        ['value' => 'penting', 'label' => 'Urgent'],
        ['value' => 'darurat', 'label' => 'Emergency'],
    ];

    public $is_normal = true;
    public $note = '';
    public $ket_priority = '';

    // lampiran variable
    public $lampirans = [];
    public $backup = []; // Temporary storage for uploaded multiple files

    public function updatingLampirans(): void
    {
        // 2. Store the uploaded files in the temporary property
        $this->backup = $this->lampirans;
    }

    // update lampiran files
    public function updatedlampirans(): void
    {
        if (!$this->lampirans) {
            return;
        }

        // 3. Merge the newly uploaded files with the saved ones
        $file = Arr::flatten(array_merge($this->backup, [$this->lampirans]));

        // 4. Finishing by removing the duplicates
        $this->lampirans = collect($file)->unique(fn(UploadedFile $item) => $item->getClientOriginalName())->toArray();
    }

    // delete lampiran files
    public function deleteUpload(array $content): void
    {
        if (! $this->lampirans) {
            return;
        }

        $files = Arr::wrap($this->lampirans);

        /** @var UploadedFile $file */
        $file = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() === $content['temporary_name'])->first();

        // 1. Here we delete the file. Even if we have a error here, we simply
        // ignore it because as long as the file is not persisted, it is
        // temporary and will be deleted at some point if there is a failure here.
        rescue(fn() => $file->delete(), report: false);

        $collect = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() !== $content['temporary_name']);

        // 2. We guarantee restore of remaining files regardless of upload
        // type, whether you are dealing with multiple or single uploads
        $this->lampirans = is_array($this->lampirans) ? $collect->toArray() : $collect->first();
    }


    //Validation rules
    public function rules(): array
    {
        return  [
            'target_asset_id' => 'required',
            'priority' => 'required',
            'ket_priority' => $this->is_normal ? 'nullable' : 'required|string|max:255',
            'note' => 'required|string|max:255',
        ];
    }

    //Submit Form
    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {

            // Check if there are any uploaded files
            $lampirans = collect($this->lampirans)->map(function ($file) {
                // Store each file and return the path
                return $file->store('maintenanceReqs/lampiran', 'public');
            })->toArray();

            $targetAsset = AssetBarang::findOrFail($this->target_asset_id);

            // Check if asset is already under active maintenance
            $hasActiveTicket = MaintenanceRequest::where('asset_id', $targetAsset->id)
                ->active()
                ->exists();

            if ($hasActiveTicket || $targetAsset->status === 'diperbaiki') {
                DB::rollBack();
                $this->toast()->error('Gagal Mengajukan', 'Aset ' . ($targetAsset->barang->nama ?? 'Aset') . ' sedang dalam perbaikan/maintenance aktif. Selesaikan perbaikan yang ada terlebih dahulu.')->send();
                return;
            }

            // Perpared data for submission
            $data = [
                'asset_id' => $targetAsset->id,
                'user_req_id' => auth()->id(),
                'priority' => $this->priority,
                'ket_priority' => $this->ket_priority,
                'note' => $this->note,
                'lampiran' => $lampirans,
            ];

            // Create a new maintenance request
            $maintReq = $targetAsset->maintenanceRequests()->create($data);

            // Log sistem
            $itemName = $targetAsset->id === $this->assetBarang->id 
                ? 'Asset Utama (' . ($targetAsset->barang->nama ?? '-') . ')'
                : 'Komponen (' . ($targetAsset->barang->nama ?? '-') . ')';

            \App\Models\Maintenance\TicketComment::create([
                'request_id' => $maintReq->id,
                'user_id'    => auth()->id(),
                'body'       => 'Tiket dibuat oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name ?? 'User') . ' untuk perbaikan ' . $itemName,
                'type'       => 'log',
            ]);

            // update target asset status
            $targetAsset->update([
                'status' => 'diperbaiki',
            ]);

            if ($targetAsset->id === $this->assetBarang->id) {
                // Update components status if main asset is being repaired
                $this->assetBarang->components->each(function ($component) {
                    $component->update([
                        'status' => 'diperbaiki',
                    ]);
                });
            } else {
                // Update main asset status as well so ticket status is visible
                $this->assetBarang->update([
                    'status' => 'diperbaiki',
                ]);
            }
            // Commit the transaction
            DB::commit();
            $this->dispatch('maintenance-request-created');

            $this->toast()
                ->success('Berhasil', 'Permintaan berhasil dikirim')
                ->send();
        } catch (Throwable $e) {
            # code...
            DB::rollBack();
            $this->toast()
                ->error(
                    'Silakan coba lagi.',
                    'Terjadi kesalahan saat menyimpan permintaan' . $e->getMessage()
                )
                ->send();
        }
    }

    public function updatedPriority()
    {
        $this->is_normal = $this->priority === 'normal';
    }

    public function mount($id): void
    {
        $this->assetBarang = AssetBarang::with(['barang', 'ruangan', 'components.barang'])
            ->findOrFail($id);

        $this->target_asset_id = $this->assetBarang->id;

        $options = [
            [
                'value' => $this->assetBarang->id,
                'label' => 'Asset Utama: ' . ($this->assetBarang->barang->nama ?? '-') . ' (' . ($this->assetBarang->kode ?? 'Belum ada kode') . ')',
            ]
        ];

        foreach ($this->assetBarang->components as $komponen) {
            $options[] = [
                'value' => $komponen->id,
                'label' => 'Komponen: ' . ($komponen->barang->nama ?? '-') . ' (' . ($komponen->kode ?? '-') . ')',
            ];
        }

        $this->targetAssetOptions = $options;
    }

    public function render()
    {
        return view('livewire.maintenance.permintaan.add');
    }
}
