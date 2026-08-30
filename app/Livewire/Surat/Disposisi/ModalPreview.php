<?php

namespace App\Livewire\Surat\Disposisi;

use App\Models\Surat\SuratDisposisi;
use Livewire\Attributes\On;
use Livewire\Component;

class ModalPreview extends Component
{
    public bool $modalPreview = false;
    public ?SuratDisposisi $selectedDisposisi = null;

    #[On('open-preview-disposisi')]
    public function loadPreview(int $id): void
    {
        $this->selectedDisposisi = SuratDisposisi::with(['details', 'direktur'])->find($id);
        if ($this->selectedDisposisi) {
            $this->modalPreview = true;
        }
    }

    public function render()
    {
        return view('livewire.surat.disposisi.modal-preview');
    }
}
