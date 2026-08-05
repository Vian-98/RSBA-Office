<?php

namespace App\Livewire\Surat\Cuti;

use App\Livewire\Forms\SuratCutiForm;
use App\Models\Surat\CutiJenis;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;
    public SuratCutiForm $form;

    #[Locked]
    public ?Karyawan $karyawan;

    public function mount()
    {
        $this->form->initOptionsUrgensi();
    }

    public function updateKaryawan($value)
    {
        $this->karyawan = Karyawan::findOrFail($value);
        $this->form->jenis_cuti = '';

        if ($this->karyawan?->sisa_cuti < 0) {
            $this->form->sisa_cuti = 0;
            $this->toast()
                ->warning('Belum terpenuhi', 'Masa kerja kurang dari 1 tahun')
                ->send();
            return;
        }
        $this->form->sisa_cuti = $this->karyawan?->sisa_cuti;
    }

    public function updatedFormJenisCuti($value)
    {
        $this->form->tgl_cuti = [];
        $this->form->lama_cuti = 0;
        if (!$this->karyawan) {
            $this->form->sisa_cuti = 0;
            return;
        }
        $sisa = $this->karyawan->getSisaCutiUntukJenis((int)$value);
        $this->form->sisa_cuti = $sisa < 0 ? 0 : $sisa;
    }

    // simpan data
    function submit()
    {
        if ((int)$this->form->jenis_cuti === 3 && !empty($this->form->tgl_cuti)) {
            $dates = (array)$this->form->tgl_cuti;
            $startDateStr = $dates[0];
            $startDate = \Carbon\Carbon::parse($startDateStr);
            $list = [];
            for ($i = 0; $i < 90; $i++) {
                $list[] = $startDate->copy()->addDays($i)->toDateString();
            }
            $this->form->tgl_cuti = $list;
            $this->form->lama_cuti = 90;
        }

        $this->validate();

        $submitting = $this->form->submiting(karyawan: $this->karyawan);

        if ($submitting['success']) {
            $this->dispatch('created-cuti');

            $this->toast()
                ->success('Sukses', 'Surat cuti berhasil dibuat.')
                ->send();
        } else {
            $this->toast()
                ->error('Failed', $submitting['message'])
                ->send();
        }
    }

    public function render()
    {
        $this->authorize('view-cuti');

        return view('livewire.surat.cuti.add');
    }
}
