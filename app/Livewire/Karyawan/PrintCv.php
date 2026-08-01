<?php

namespace App\Livewire\Karyawan;

use Livewire\Component;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanDocument;
use App\Models\Sdm\KaryawanPendidikan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class PrintCv extends Component
{
    public ?Karyawan $karyawan = null;

    public function mount($karyawanId = null)
    {
        if ($karyawanId) {
            $this->karyawan = Karyawan::find($karyawanId);
        }
    }

    #[Computed]
    public function pendidikans()
    {
        return KaryawanPendidikan::where('karyawan_id', $this->karyawan->id)
            ->orderBy('tahun_lulus', 'DESC')
            ->get();
    }

    #[Computed]
    public function sertifikasi()
    {
        return KaryawanDocument::where('karyawan_id', $this->karyawan->id)
            ->where('jenis', 'sertifikat')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function render()
    {
        return view('livewire.karyawan.print-cv');
    }
}
