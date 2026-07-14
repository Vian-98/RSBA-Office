<?php

namespace App\Livewire\Profile;

use App\Models\Sdm\JadwalKerjaDetail;
use App\Enums\StatusJadwalKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Jadwal Kerja Saya')]
class JadwalTugasSaya extends Component
{
    use Interactions;
    
    public $bulan;
    public $tahun;

    public function mount()
    {
        $this->bulan = date('n');
        $this->tahun = date('Y');
    }

    public function render()
    {
        $karyawanId = Auth::user()->karyawan_id;
        
        $details = [];
        if ($karyawanId) {
            // Kita join dengan jadwalKerja untuk memfilter bulan, tahun dan pastikan status published/locked
            // StatusJadwalKerja Enum tidak perlu di value() kalau di Laravel 11/12 bisa langsung di query tapi mari asumsikan kita get value
            $statusPublished = StatusJadwalKerja::PUBLISHED->value ?? 'published';
            $statusLocked = StatusJadwalKerja::LOCKED->value ?? 'locked';
            
            $details = JadwalKerjaDetail::with(['shift', 'jadwalKerja.ruangan'])
                ->where('karyawan_id', $karyawanId)
                ->whereHas('jadwalKerja', function($q) use ($statusPublished, $statusLocked) {
                    $q->where('bulan', $this->bulan)
                      ->where('tahun', $this->tahun)
                      ->whereIn('status', [$statusPublished, $statusLocked]);
                })
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        $bulanOptions = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => date('F', mktime(0, 0, 0, $m, 1))
        ])->toArray();

        $tahunOptions = collect(range(date('Y') + 1, 2008))->map(fn($y) => [
            'value' => $y,
            'label' => (string) $y
        ])->toArray();

        return view('livewire.profile.jadwal-tugas-saya', [
            'details' => $details,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
        ]);
    }
}
