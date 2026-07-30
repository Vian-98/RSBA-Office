<?php

namespace App\Livewire\Karyawan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;


#[Lazy]
class Resign extends Component
{

    use Interactions;

    public ?Karyawan $karyawan;

    public $resign, $keterangan, $tgl_resign;
    public $resign_options = [
        ['id' => 1, 'label' => 'Resign / Mengundurkan Diri'],
        ['id' => 2, 'label' => 'Diberhentikan'],
        ['id' => 4, 'label' => 'Habis Kontrak'],
    ];

    public $rules = [
        'resign' => 'required',
        'tgl_resign' => 'required'
    ];

    function mount($id)
    {
        $this->karyawan = Karyawan::findOrFail($id);
    }

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->karyawan->resign = $this->resign;
            $this->karyawan->resign_at = $this->tgl_resign;
            $this->karyawan->save();

            DB::commit();

            $this->dispatch('karyawan-resign-updated');

            $this->toast()
                ->success('Berhasil', 'Karyawan berhasil diupdate.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.karyawan.resign');
    }
}
