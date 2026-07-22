<?php

namespace App\Livewire\Surat\Cuti;

use App\Models\Surat\SuratCuti;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Milon\Barcode\DNS2D;

class PrintCuti extends Component
{
    #[Locked]
    public SuratCuti $suratCuti;

    public function mount(SuratCuti $suratCuti)
    {
        $this->suratCuti = SuratCuti::findOrFail($suratCuti->id);
    }

    #[On('surat-cuti-manual-approved')]
    #[On('surat-cuti-approved')]
    public function refreshData()
    {
        $this->suratCuti = SuratCuti::find($this->suratCuti->id);

        $this->dispatch('trigger-print');
    }

    #[Computed]
    public function karyawan()
    {
        return $this->suratCuti->karyawan;
    }

    #[Computed]
    public function approvals()
    {
        // parent_id pengaju
        // $jabatanId  = $this->karyawan->jabatan?->first()?->id;

        // Approvers
        return $this->suratCuti->approvals->map(
            function ($approval) {
                $jabatan = $approval->karyawan?->jabatan?->first();
                // dd($jabatan->id, $atasanId);

                return [
                    'nama'        => $approval->karyawan?->full_nama,
                    'jabatan'     => $jabatan?->nama,
                    'status'      => $approval->status->nama(),
                    'signature'   => $approval?->signature_hash,
                ];
            }

        )->toArray();
    }

    #[Computed]
    public function generateBarcode($key)
    {
        // $key = $this->approvals();
        if (!$key) {
            return;
        }
        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($key, 'QRCODE');
    }

    public function render()
    {
        if ($this->suratCuti && $this->suratCuti->exists) {
            $this->suratCuti = $this->suratCuti->fresh();
        }
        return view('livewire.surat.cuti.print-cuti');
    }
}
