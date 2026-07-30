<?php

namespace App\Livewire\Profile;

use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Home extends Component
{
    public ?Karyawan $karyawan;

    public function mount($id = null)
    {
        $karyawanId = $id ?? Auth::user()?->karyawan_id;
        $this->karyawan = Karyawan::findOrFail($karyawanId);
    }

    public function render()
    {
        $karyawan = $this->karyawan;

        // Recent cuti (last 3)
        $recentCuti = SuratCuti::where('karyawan_id', $karyawan->id)
            ->latest()
            ->limit(5)
            ->get();

        // Upcoming / pending cuti
        $pendingCuti = SuratCuti::where('karyawan_id', $karyawan->id)
            ->whereIn('status', ['waiting', 'pending'])
            ->count();

        // Masa kerja
        $tglMasuk = Carbon::parse($karyawan->tgl_masuk);
        $now = Carbon::now();
        $diff = $tglMasuk->diff($now);

        // Sisa cuti
        $sisaCuti = $karyawan->sisa_cuti;

        // Anniversary info
        $anniversaryThisYear = $tglMasuk->copy()->year($now->year);
        if ($now->gte($anniversaryThisYear)) {
            $nextAnniversary = $anniversaryThisYear->copy()->addYear();
        } else {
            $nextAnniversary = $anniversaryThisYear;
        }
        $daysToAnniversary = (int) $now->diffInDays($nextAnniversary, false);

        return view('livewire.profile.home', compact(
            'recentCuti',
            'pendingCuti',
            'diff',
            'sisaCuti',
            'daysToAnniversary',
            'nextAnniversary'
        ));
    }
}
