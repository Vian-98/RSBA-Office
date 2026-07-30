<?php

namespace App\Livewire\Akreditasi\Documents;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use App\Models\Akreditasi\AkreDocuments;
use TallStackUi\Traits\Interactions;

#[Lazy]
class AttachTo extends Component
{
    use Interactions;

    #[Locked]
    public $kegiatan_id;

    #[Locked]
    public ?AkreDocuments $documents;

    #[Locked]
    public $source_element_id;

    #[Locked]
    public ?string $sumber_element;

    // Chapter, bab, dan ep tujuan
    public $chapter_id;
    public $sub_id;
    public $element_id;

    public function mount($kegiatan_id, $docSelectedId)
    {
        $this->kegiatan_id = $kegiatan_id;

        // Documents Yang akan di attach
        $this->documents = AkreDocuments::with(['originalElement.bab.chapter'])->findOrFail($docSelectedId);

        // Dapatkan Original Document darimana
        $sumber = $this->documents->original_element_document;
        $this->source_element_id = $sumber?->id;
        $this->sumber_element = $sumber ?  sprintf(
            '%s > %s > %s',
            $sumber->bab->chapter->singkatan,
            $sumber->bab->nama,
            $sumber->nomor
        )
            : "Dihapus";
    }

    public function rules(): array
    {
        return [
            'chapter_id' => 'required',
            'sub_id' => 'required',
            'element_id' => 'required'
        ];
    }

    public function submit()
    {
        DB::beginTransaction();
        try {
            $this->validate();

            if (!$this->source_element_id) {
                $this->toast()
                    ->error('Tidak Berhasil Ditambahkan', 'Document telah dihapus dari element asal.')
                    ->send();

                return;
            }

            // Check element sudah memelikki document yg sama ?
            if ($this->documents->elements()->where('element_id', $this->element_id)->exists()) {
                $this->toast()
                    ->warning('Tidak Berhasil Ditambahkan', 'Document sudah terlampir pada element.')
                    ->send();

                return;
            }

            // Attach Document ke ke element dengan [source_element, dan bukan original]
            $this->documents->elements()->attach(
                $this->element_id, //attach ke element
                [
                    'source_element_id' => $this->source_element_id,
                    'is_original' => false
                ]
            );

            DB::commit();

            $this->dispatch('document-attached');

            $this->toast()
                ->success('Berhasil', 'Document berhasil tambahkan.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage() . " [Line: {$e->getLine()}]")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.attach-to');
    }
}
