<?php

namespace App\Livewire\Akreditasi\Documents;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use App\Models\Akreditasi\AkreElement;

#[Lazy]
class AddLinked extends Component
{
    use WithFileUploads;

    public ?int $element_id;

    public ?int $kegiatan_id;

    public $pdf_file;
    public ?string $nama;


    public $chapter_id;
    public $sub_id;
    public $element_id_link;

    public function mount($element_id)
    {
        $this->element_id = $element_id;

        $kegiatan =  AkreElement::join('akre_bab_elements', 'akre_elements.akre_bab_id', '=', 'akre_bab_elements.id')
            ->join('akre_chapter', 'akre_bab_elements.chapter_id', '=', 'akre_chapter.id')
            ->join('akre_kegiatan', 'akre_chapter.kegiatan_id', '=', 'akre_kegiatan.id')
            ->where('akre_elements.id', $element_id)
            ->select('akre_kegiatan.id')
            ->first();

        if ($kegiatan) {
            $this->kegiatan_id = $kegiatan->id;
        }
    }

    public function rules(): array
    {
        return [
            'pdf_file' => "required|mimes:pdf|max:25600", //25MB
            'nama' => 'required|string|max:255'
        ];
    }

    public function submit()
    {
        $this->validate();
    }


    public function reseForm()
    {
        $this->reset(['nama', 'pdf_file']);
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.add-linked');
    }
}
