<?php

namespace App\Livewire\Akreditasi\Documents;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class Pencarian extends Component
{
    #[Locked]
    public $kegiatan_id;

    public $chapter_id;
    public $sub_id;
    public $element_id;

    public function mount($kegiatanId, $chapterId)
    {
        $this->kegiatan_id = $kegiatanId;
        $this->chapter_id = $chapterId;
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.pencarian');
    }
}
