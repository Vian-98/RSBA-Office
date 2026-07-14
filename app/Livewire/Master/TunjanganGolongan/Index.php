<?php

namespace App\Livewire\Master\TunjanganGolongan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Master Tunjangan Golongan & Config Gaji')]
class Index extends Component
{
    use Interactions;

    // Golongan allowances map (key: golongan_grade, value: tunjangan_golongan)
    public array $allowances = [];

    public function mount()
    {
        // Load Golongan allowances
        $golongans = DB::table('sdm_payroll_golongans')->orderBy('golongan', 'asc')->get();
        foreach ($golongans as $gol) {
            $this->allowances[$gol->golongan] = (int) $gol->tunjangan_golongan;
        }
    }

    public function saveAllowances()
    {
        DB::beginTransaction();
        try {
            foreach ($this->allowances as $gol => $val) {
                DB::table('sdm_payroll_golongans')
                    ->where('golongan', $gol)
                    ->update([
                        'tunjangan_golongan' => (double) $val,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            $this->toast()
                ->success('Berhasil !', 'Tunjangan golongan berhasil diperbarui.')
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
        $golongansList = DB::table('sdm_payroll_golongans')->orderBy('golongan', 'asc')->get();
        return view('livewire.master.tunjangan-golongan.index', [
            'golongansList' => $golongansList
        ]);
    }
}
