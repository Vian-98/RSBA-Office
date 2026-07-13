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
class Edit extends Component
{
    use Interactions;

    public ?Jabatan $jabatan;

    public $nama;
    public $atasan;
    public $kode_surat;
    public $bagian;
    public $tunjangan_jabatan;
    public $atasan_options;
    public $bagian_options;

    public $rules = [
        'nama' => 'required|string',
        'tunjangan_jabatan' => 'nullable|numeric|min:0',
    ];

    function mount($id)
    {
        $this->jabatan = Jabatan::findOrFail($id);
        if ($this->jabatan) {
            $this->nama = $this->jabatan->nama;
            $this->atasan = $this->jabatan->parent_id;
            $this->kode_surat = $this->jabatan->kode_surat;
            $this->bagian = $this->jabatan->bagian_id;
            $this->tunjangan_jabatan = $this->jabatan->tunjangan_jabatan;
        }

        $this->atasan_options = Jabatan::select('nama', 'id')->get();
        $this->bagian_options = Bagian::select('nama', 'id')->get();
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->jabatan->nama = $this->nama;
            $this->jabatan->kode_surat = $this->kode_surat;
            $this->jabatan->parent_id = $this->atasan;
            $this->jabatan->bagian_id = $this->bagian;
            $this->jabatan->tunjangan_jabatan = $this->tunjangan_jabatan ?: 0;
            $this->jabatan->save();

            DB::commit();

            $this->dispatch('jabatan-updated');
            $this->toast()
                ->success('Sukses', 'Jabatan berhasil diperbaharui.')
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
        return view('livewire.master.jabatan.edit');
    }
}
