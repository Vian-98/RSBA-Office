<?php

namespace App\Livewire\Master\TunjanganJabatan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Konfigurasi Tunjangan Jabatan')]
class Index extends Component
{
    use Interactions;

    public string $search = '';

    // Model allowances map (key: jabatan_id, value: nominal)
    public array $allowances = [];

    public function mount()
    {
        $jabs = DB::table('sdm_jabatan')->get();
        foreach ($jabs as $jab) {
            $this->allowances[$jab->id] = (int) $jab->tunjangan_jabatan;
        }
    }

    public function save()
    {
        DB::beginTransaction();
        try {
            foreach ($this->allowances as $id => $val) {
                DB::table('sdm_jabatan')
                    ->where('id', $id)
                    ->update([
                        'tunjangan_jabatan' => (double) $val,
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();

            $this->toast()
                ->success('Berhasil !', 'Tunjangan jabatan berhasil disimpan.')
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        $query = DB::table('sdm_jabatan')->orderBy('nama', 'asc');
        if (!empty($this->search)) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }
        $jabatans = $query->get();

        return view('livewire.master.tunjangan-jabatan.index', [
            'jabatans' => $jabatans,
        ]);
    }
}
