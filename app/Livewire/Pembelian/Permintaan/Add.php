<?php

namespace App\Livewire\Pembelian\Permintaan;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Arr;
use App\Models\Master\Barang;
use Livewire\Attributes\Lazy;
use Illuminate\Http\UploadedFile;
use TallStackUi\Traits\Interactions;
use App\Livewire\Forms\Pembelian\PermintaanForm;
use Livewire\WithFileUploads;

#[Lazy]
class Add extends Component
{
    use Interactions;
    use WithFileUploads;

    public PermintaanForm $form;

    public array $priorityOptions = [
        ['value' => 'normal', 'label' => 'Normal'],
        ['value' => 'penting', 'label' => 'Penting'],
        ['value' => 'darurat', 'label' => 'Darurat'],
    ];

    // ========================Lampirans=============================
    public $temp = []; // Temporary storage for uploaded multiple files

    public function updatingFormLampirans(): void
    {
        // 2. Store the uploaded files in the temporary property
        $this->temp = $this->form->lampirans;
    }

    // update lampiran files
    public function updatedFormlampirans(): void
    {
        if (!$this->form->lampirans) {
            return;
        }

        // 3. Merge the newly uploaded files with the saved ones
        $file = Arr::flatten(array_merge($this->temp, [$this->form->lampirans]));

        // 4. Finishing by removing the duplicates
        $this->form->lampirans = collect($file)->unique(fn(UploadedFile $item) => $item->getClientOriginalName())->toArray();
    }

    // delete lampiran files
    public function deleteUpload(array $content): void
    {
        if (! $this->form->lampirans) {
            return;
        }

        $files = Arr::wrap($this->form->lampirans);

        /** @var UploadedFile $file */
        $file = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() === $content['temporary_name'])->first();

        // 1. Here we delete the file. Even if we have a error here, we simply
        // ignore it because as long as the file is not persisted, it is
        // temporary and will be deleted at some point if there is a failure here.
        rescue(fn() => $file->delete(), report: false);

        $collect = collect($files)->filter(fn(UploadedFile $item) => $item->getFilename() !== $content['temporary_name']);

        // 2. We guarantee restore of remaining files regardless of upload
        // type, whether you are dealing with multiple or single uploads
        $this->form->lampirans = is_array($this->form->lampirans) ? $collect->toArray() : $collect->first();
    }
    //=====================End Lampiran===================================


    public function getBarang($id): ?object
    {
        $barang = Barang::with('satuan', 'kategori', 'latestStok')
            ->where('id', $id)
            ->orWhere('sku', $id)
            ->first();

        if ($barang) {
            $items = (object) [
                'id' => $barang->id,
                'bhp' => $barang->bhp == 1 ? true : false,
                'sku' => $barang->sku,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan->nama,
                'harga_beli_latest' => (int) $barang->latestStok?->harga_satuan ?? 0,
                'kategori' => $barang->kategori->nama
            ];
            return $items;
        }
        return null;
    }

    public function submit(): void
    {

        try {

            $this->validate();

            $this->form->simpan();

            $this->form->reset();

            $this->dispatch('new-request-pembelian-created');

            $this->toast()
                ->success('Berhasil', 'Pengajuan anda telah dikirim.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Terjadi Kesalahan', "<i>{$e->getMessage()}</i> <br>Silahkan Coba Lagi.")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.add');
    }
}
