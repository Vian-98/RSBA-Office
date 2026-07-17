<?php

namespace App\Livewire\Karyawan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use App\Livewire\Forms\KaryawanForm;
use App\Models\Sdm\KaryawanJabatan;
use Livewire\Attributes\Lazy;
use Illuminate\Validation\Rule;
use TallStackUi\Traits\Interactions;

#[Lazy]
class EditKedinasan extends Component
{
    use Interactions;

    public KaryawanForm $form;

    public $status_options;
    public $status_init;
    public $jabatan_options;
    public $jabatan_init;

    public $dinas_options = [
        ['id' => 'resign', 'label' => 'Resign / Mengundurkan Diri'],
        ['id' => 'dipecat', 'label' => 'Dipecat'],
        ['id' => 'end_kontrak', 'label' => 'Habis Kontrak'],
    ];
    public $dinas_init;
    public $ruangan_init;
    public $dinas;
    public $tgl_dinas;


    public function rules(): array
    {
        return [
            'form.status' => 'required',
            'form.jabatan' => 'required',
            'form.tgl_status' => Rule::requiredIf(fn() => $this->form->status != $this->status_init),
            'form.tgl_jabatan' => Rule::requiredIf(fn() => $this->form->jabatan != $this->jabatan_init),
            'form.tgl_dinas' => Rule::requiredIf(fn() => $this->form->dinas != $this->dinas_init)
        ];
    }

    public function mount($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $this->form->mount($karyawan); //new instance form

        $this->form->setKedinasan($karyawan);

        $this->status_options = StatusKaryawan::options();
        $this->status_init = $karyawan->status;

        $this->jabatan_options = Jabatan::all();
        $this->jabatan_init = $karyawan->jabatan[0]->id ?? '';
        $this->ruangan_init = $karyawan->ruangan_id;
    }

    public function update()
    {
        $this->validate($this->rules());

        if ($this->form->tgl_status && ($this->form->status != $this->status_init)) {
            $this->updateStatus();
            $this->status_init = $this->form->status;
        }

        // update jabatan
        if ($this->form->tgl_jabatan && ($this->form->jabatan != $this->jabatan_init)) {
            $this->updateJabatan();
            $this->jabatan_init = $this->form->jabatan;
        }

        // update ruangan dan kategori kerja
        $data = [];
        if ($this->form->ruangan !== $this->ruangan_init) {
            $data['ruangan_id'] = empty($this->form->ruangan) ? null : $this->form->ruangan;
            $this->ruangan_init = $this->form->ruangan;
        }
        
        $data['kategori_kerja'] = $this->form->kategori_kerja;
        
        if (count($data) > 0) {
            Karyawan::where('id', $this->form->karyawan->id)->update($data);
        }

        $this->toast()
            ->success('Sukses', 'Update data kedinasan berhasil.')
            ->send();
    }

    function updateStatus()
    {

        try {
            $data = [
                'status' => $this->form->status
            ];

            // update
            $this->form->karyawan->update($data);

            // event
            $this->dispatch('status-updated');

            // toast
            $this->toast()
                ->success('Sukses', 'Update data kedinasan berhasil.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', 'Error : ', $th->getMessage())
                ->send();
        }
    }

    public function updateJabatan()
    {
        $latestJabatan = $this->form->karyawan->jabatan?->first();
        try {
            // update tgl_berakhir jabatan terakhir
            if (!empty($latestJabatan)) {
                KaryawanJabatan::where('id', $latestJabatan->pivot->id)
                    ->update(['tgl_berakhir' => $this->form->tgl_jabatan]);
            }

            $data = [
                'jabatan_id' => $this->form->jabatan,
                'karyawan_id' => $this->form->karyawan->id,
                'tgl_mulai' => $this->form->tgl_jabatan
            ];

            // insert data new jabatan
            KaryawanJabatan::create($data);

            $this->dispatch('new-jabatan-created'); //dispatch event

            $this->toast()
                ->success('Berhasil', 'Jabatan baru berhasil disimpan.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', 'Error : ', $th->getMessage())
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.karyawan.edit-kedinasan');
    }
}
