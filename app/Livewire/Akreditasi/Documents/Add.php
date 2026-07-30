<?php

namespace App\Livewire\Akreditasi\Documents;

use Exception;
use Throwable;
use App\Models\Akreditasi\AkreDocuments;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\Akreditasi\AkreFiles;
use App\Models\Akreditasi\AkreElement;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use WithFileUploads;
    use Interactions;

    public $element_id;

    public $pdf_file;
    public ?string $nama;

    public function mount($element_id)
    {
        $this->element_id = $element_id;
    }

    public function rules(): array
    {
        return [
            'pdf_file' => "required|mimes:pdf|max:25600", //25MB
            'nama' => 'required|string|max:255'
        ];
    }

    public function submit(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();
            // Cek apakah file valid
            if (!$this->pdf_file || !$this->pdf_file->isValid()) {
                $this->addError('pdf_file', 'File tidak valid atau gagal diupload');
                return;
            }

            $filename = $this->pdf_file->getClientOriginalName();

            // generate folder
            $strukturs = AkreElement::join('akre_bab_elements', 'akre_elements.akre_bab_id', '=', 'akre_bab_elements.id')
                ->join('akre_chapter', 'akre_bab_elements.chapter_id', '=', 'akre_chapter.id')
                ->join('akre_kegiatan', 'akre_chapter.kegiatan_id', '=', 'akre_kegiatan.id')
                ->where('akre_elements.id', $this->element_id)
                ->select('akre_kegiatan.folder_path', 'akre_chapter.singkatan', 'akre_bab_elements.nama', 'akre_elements.nomor')
                ->first();

            $folder = "{$strukturs->folder_path}/{$strukturs->singkatan}/{$strukturs->nama}/{$strukturs->nomor}";

            // check folder 
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // store file ke folder 
            $path = $this->pdf_file->storeAs($folder, $filename, 'public');

            // simpan data
            AkreFiles::create([
                'element_id' => $this->element_id,
                'nama' => $this->nama,
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $this->pdf_file->getMimeType(),
                'uploaded_by' => auth()->user()->id,
            ]);

            $this->dispatch('uploaded-files-element');
            $this->resetForm();

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'File berhasil ditambahkan.')
                ->send();
        } catch (Exception $e) {
            DB::rollback();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function resetForm()
    {
        $this->reset(['pdf_file', 'nama']);
    }


    public function submitDocument()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Cek apakah file valid
            if (!$this->pdf_file || !$this->pdf_file->isValid()) {
                $this->addError('pdf_file', 'File tidak valid atau gagal diupload');
                return;
            }

            // $filename = $this->pdf_file->getClientOriginalName();
            $filename = $this->pdf_file->hashName();

            $kegiatan = AkreElement::join('akre_bab_elements', 'akre_elements.akre_bab_id', '=', 'akre_bab_elements.id')
                ->join('akre_chapter', 'akre_bab_elements.chapter_id', '=', 'akre_chapter.id')
                ->join('akre_kegiatan', 'akre_chapter.kegiatan_id', '=', 'akre_kegiatan.id')
                ->where('akre_elements.id', $this->element_id)
                ->select('akre_kegiatan.folder_path')
                ->first();

            // simpan file pada folder kegiatan/documents
            $folder = "{$kegiatan->folder_path}/documents";

            // check folder 
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // store file ke folder 
            $path = $this->pdf_file->storeAs($folder, $filename, 'public');

            // Create Document
            $document = AkreDocuments::create([
                'nama' => $this->nama,
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $this->pdf_file->getMimeType(),
                'uploaded_by' => auth()->user()->id
            ]);

            // Attach document pada table AkreElementDocuments
            $element = AkreElement::find($this->element_id);
            $element->documents()->attach($document->id, [
                'is_original' => true
            ]);
            DB::commit();

            $this->dispatch('uploaded-files-element');
            $this->resetForm();

            $this->toast()
                ->success('Berhasil', 'File document tersimpan.')
                ->send();
        } catch (Throwable $e) {

            DB::rollBack();
            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.add');
    }
}
