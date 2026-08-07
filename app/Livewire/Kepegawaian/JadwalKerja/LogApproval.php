<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Models\Sdm\JadwalApprovalLog;
use App\Models\Sdm\JadwalKerja;
use Livewire\Component;
use Livewire\Attributes\On;

class LogApproval extends Component
{
    public ?int $jadwalKerjaId = null;
    public ?JadwalKerja $jadwalKerja = null;

    public function mount(?int $jadwalKerjaId = null): void
    {
        if ($jadwalKerjaId) {
            $this->loadLog($jadwalKerjaId);
        }
    }

    #[On('load-log-approval')]
    public function loadLog(int $jadwalKerjaId): void
    {
        $this->jadwalKerjaId = $jadwalKerjaId;
        $this->jadwalKerja = JadwalKerja::with([
            'ruangan',
            'pembuat',
        ])->find($jadwalKerjaId);
    }

    public function getLogs()
    {
        if (!$this->jadwalKerjaId) {
            return collect();
        }

        return JadwalApprovalLog::with(['user', 'karyawan'])
            ->where('jadwal_kerja_id', $this->jadwalKerjaId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.kepegawaian.jadwal-kerja.log-approval', [
            'logs' => $this->getLogs(),
        ]);
    }
}
