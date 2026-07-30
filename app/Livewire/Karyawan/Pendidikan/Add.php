<?php

namespace App\Livewire\Karyawan\Pendidikan;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Enums\TingkatPendidikan;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanPendidikan;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public ?Karyawan $karyawan;
    public $tingkatPendidikanOpts;

    public string $tingkat, $nama, $instansi, $gelar = '', $setting_gelar = '';
    public $tahun_lulus;

    public $rules = [
        'tingkat' => 'required',
        'nama' => 'required',
        'tahun_lulus' => 'required',
        'instansi' => 'required',
    ];

    function mount($karyawan)
    {
        $this->karyawan = $karyawan;
        $this->tingkatPendidikanOpts = TingkatPendidikan::options();
    }

    function submit()
    {

        $this->validate();

        $data = [
            'karyawan_id' => $this->karyawan->id,
            'nama' => $this->nama,
            'tahun_lulus' => $this->tahun_lulus,
            'instansi' => $this->instansi,
            'gelar' => $this->gelar,
            'tingkat' => $this->tingkat,
            'set_gelar' => $this->setting_gelar
        ];

        DB::beginTransaction();
        try {
            KaryawanPendidikan::create($data);

            DB::commit();

            $this->dispatch('pendidikan-karyawan-created');
            $this->toast()
                ->success('Sukses', 'Data pendidikan berhasil disimpan.')
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
        return view('livewire.karyawan.pendidikan.add');
    }
}
