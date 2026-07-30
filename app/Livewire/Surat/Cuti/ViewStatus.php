<?php

namespace App\Livewire\Surat\Cuti;

use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class ViewStatus extends Component
{
    #[Locked]
    public ?SuratCuti $suratCuti;

    #[Locked]
    public Collection $suratCutiApproval;

    public function mount(?SuratCuti $surat)
    {
        $this->suratCuti = $surat;
        $this->suratCutiApproval  = SuratCutiApproval::with(['karyawan'])->where(
            'surat_cuti_id',
            $surat->id
        )->get();
    }


    public function render()
    {
        return view('livewire.surat.cuti.view-status');
    }
}
