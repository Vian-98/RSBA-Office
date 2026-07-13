<?php

namespace App\Livewire\Master\Jabatan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Jabatan;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama;
    public $atasan;
    public $kode_surat;
    public $bagian;
    public $tunjangan_jabatan = 0;
    public $atasan_options;
    public $bagian_options;

    protected $rules = [
        'nama' => 'required|string',
        'tunjangan_jabatan' => 'nullable|numeric|min:0',
    ];

    function mount()
    {
        $this->atasan_options = Jabatan::select('nama', 'id')->get();
        $this->bagian_options = Bagian::select('nama', 'id')->get();
    }

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $this->nama,
                'kode_surat' => $this->kode_surat ?? null,
                'parent_id' => $this->atasan ?? null,
                'bagian_id' => $this->bagian ?? null,
                'tunjangan_jabatan' => $this->tunjangan_jabatan ?: 0,
            ];
            Jabatan::create($data);

            DB::commit();
            // dispatch
            $this->dispatch('new-jabatan-created');

            $this->toast()
                ->success('Berhasil', 'Jabatan baru berhasil dibuat.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Failed', 'Error' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.jabatan.add');
    }
}
