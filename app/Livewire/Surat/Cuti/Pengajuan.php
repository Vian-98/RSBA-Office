<?php

namespace App\Livewire\Surat\Cuti;

use App\Livewire\Forms\SuratCutiForm;
use App\Models\Surat\CutiJenis;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Pengajuan extends Component
{
    use Interactions;

    public SuratCutiForm $form;

    #[Locked]
    public ?Karyawan $karyawan;

    // public $options_urgensi;

    public function mount($id)
    {
        $this->karyawan = Karyawan::findOrFail($id);

        if ($this->karyawan?->sisa_cuti < 0) {
            $this->toast()
                ->warning('Belum terpenuhi', 'Masa kerja kurang dari 1 tahun')
                ->send();
            return;
        }
        $this->form->sisa_cuti = $this->karyawan?->sisa_cuti;
        $this->form->initOptionsUrgensi();
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
        // submit data menggunakan SuratCutiForm
        $submiting = $this->form->submiting(karyawan: $this->karyawan);

        if ($submiting['success']) {
            $this->dispatch('created-cuti');

            $this->toast()
                ->success('Sukses', 'Cuti berhasil diajukan.')
                ->send();
        } else {
            $this->toast()
                ->error('Failed', $submiting['message'])
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.cuti.pengajuan');
    }
}
