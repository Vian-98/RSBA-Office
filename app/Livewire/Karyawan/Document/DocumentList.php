<?php

namespace App\Livewire\Karyawan\Document;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use App\Models\Sdm\KaryawanDocument;
use Illuminate\Support\Facades\Storage;
use TallStackUi\Traits\Interactions;

#[Lazy]
class DocumentList extends Component
{
    use Interactions;

    public $documents;
    public int $karyawanId;
    public ?KaryawanDocument $documentsSelected;

    function mount($id)
    {
        $this->karyawanId = $id;
    }


    function view($id)
    {
        $this->documentsSelected = KaryawanDocument::findOrFail($id);
        $this->dispatch('open-modal', id: 'view-document-karyawan');
    }

    function delete($id)
    {
        $this->dialog()
            ->question('Hapus document ?', 'File yang dihapus tidak dapat dikembalikan kembali.')
            ->confirm('Hapus', 'confirmDeleteDocs', $id)
            ->cancel('Batal', 'canceledDeleteDocs', $id)
            ->send();
    }

    function confirmDeleteDocs($id): void
    {
        $document = KaryawanDocument::findOrFail($id);
        DB::beginTransaction();
        try {

            $filePath = $document->filename;

            $fileExist = Storage::disk('public')->exists($filePath);

            if ($fileExist) {
                // delete file : public/storage/karyawan/docs/filename
                Storage::disk('public')->delete($filePath);

                // delete record
                $document->delete();


                // event
                $this->dispatch('document-karyawan-deleted');
                $this->toast()
                    ->success('Berhasil', 'File document berhasil dihapus.')
                    ->send();
            } else {
                DB::rollBack();
                $this->toast()
                    ->error('Failed', 'File tidak ditemukan.');
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Failed', $e->getMessage())
                ->send();
        }
    }

    function canceledDeleteDocs(): void
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus file document dibatalkan.')
            ->send();
    }

    public function placeholder()
    {
        return view('components.skeleton', ['paragraf' => 2, 'footer' => 0]);
    }

    protected $listeners = [
        'document-karyawan-created' => '$refresh',
        'document-karyawan-deleted' => '$refresh',
    ];

    public function render()
    {
        $this->documents = KaryawanDocument::where('karyawan_id', $this->karyawanId)->get();

        return view('livewire.karyawan.document.document-list');
    }
}
