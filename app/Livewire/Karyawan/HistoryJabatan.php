<?php

namespace App\Livewire\Karyawan;

use Livewire\Component;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;

#[Lazy]
class HistoryJabatan extends Component
{
    public ?Karyawan $karyawan = null;

    public function mount($karyawanId = null)
    {
        if ($karyawanId) {
            $this->karyawan = Karyawan::find($karyawanId);
        }
    }

    public function render()
    {
        return view('livewire.karyawan.history-jabatan', ['karyawan' => $this->karyawan]);
    }
}
