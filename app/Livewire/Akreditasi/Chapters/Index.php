<?php

namespace App\Livewire\Akreditasi\Chapters;

use App\Models\Akreditasi\AkreChapter;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use App\Models\Akreditasi\AkreKegiatan;
use Livewire\Attributes\Computed;

#[Title('Standar Akreditasi')]
#[Lazy]
class Index extends Component
{
    public AkreKegiatan $akreKegiatan;
    public ?int $kegiatanId;
    public ?string $folderKegiatan;

    // public ?AkreChapter $chapters;
    public $selectedChapter = null;
    public $namaSelectedChapter = null;

    public function mount($uuid)
    {
        $kegiatan =   AkreKegiatan::where('uuid', $uuid)->firstOrFail();
        if (!$kegiatan) {
            return 403;
        }
        $this->akreKegiatan = $kegiatan;
        $this->kegiatanId = $kegiatan->id;
        $this->folderKegiatan = $kegiatan->folder_path;
    }

    #[Computed]
    public function chapters()
    {
        return AkreChapter::where('kegiatan_id', $this->kegiatanId)
            ->orderBy('singkatan')
            ->get();
    }

    public function updatedSelectedChapter($value)
    {
        $chapter = AkreChapter::where('id', $value)->first();
        $this->namaSelectedChapter = "{$chapter->nama} ({$chapter->singkatan}) ";
    }

    public function toElement($id)
    {
        return $this->redirect(route('kepegawaian.akreditasi.chapter.elements', $id), navigate: true);
    }

    public function render()
    {
        return view('livewire.akreditasi.chapters.index');
    }
}
