<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Models\Sdm\JadwalKerjaLog;
use Livewire\Component;
use Livewire\Attributes\On;

class Riwayat extends Component
{
    public $jadwalKerjaId;
    public $logs = [];

    #[On('load-riwayat')]
    public function loadRiwayat($jadwalKerjaId)
    {
        $this->jadwalKerjaId = $jadwalKerjaId;
        $this->logs = JadwalKerjaLog::with(['karyawan', 'shiftLama', 'shiftBaru', 'pembuat', 'detail'])
            ->where('jadwal_kerja_id', $this->jadwalKerjaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.kepegawaian.jadwal-kerja.riwayat');
    }
}
