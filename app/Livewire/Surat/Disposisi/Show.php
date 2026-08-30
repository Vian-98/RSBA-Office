<?php

namespace App\Livewire\Surat\Disposisi;

use App\Models\Surat\SuratDisposisi;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Detail Surat Disposisi')]
class Show extends Component
{
    public int $disposisiId;
    public ?SuratDisposisi $disposisi = null;

    public function mount(int $id): void
    {
        $this->disposisiId = $id;
        $this->disposisi = SuratDisposisi::with(['details.jabatan', 'details.karyawan', 'creator', 'direktur'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.surat.disposisi.show');
    }
}
