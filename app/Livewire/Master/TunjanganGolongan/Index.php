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

    // Config settings
    public $umk;

    // Dynamic 25% UMK allocations list
    public array $allocations = [];

    // Golongan allowances map (key: golongan_grade, value: tunjangan_golongan)
    public array $allowances = [];

    public function mount()
    {
        // 1. Load UMK setting
        $this->umk = DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value') ?: 3000000;

        // 2. Load allocations list
        $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
        foreach ($dbAllocations as $alloc) {
            $this->allocations[] = [
                'id' => $alloc->id,
                'nama' => $alloc->nama,
                'persen' => (double) $alloc->persen,
                'is_absensi' => (bool) $alloc->is_absensi,
            ];
        }

        // Fallback default allocations if empty
        if (empty($this->allocations)) {
            $this->allocations = [
                ['id' => null, 'nama' => 'Tunjangan Tetap', 'persen' => 80.0, 'is_absensi' => false],
                ['id' => null, 'nama' => 'Tunjangan Absensi', 'persen' => 20.0, 'is_absensi' => true],
            ];
        }

        // 3. Load Golongan allowances
        $golongans = DB::table('sdm_payroll_golongans')->orderBy('golongan', 'asc')->get();
        foreach ($golongans as $gol) {
            $this->allowances[$gol->golongan] = (int) $gol->tunjangan_golongan;
        }
    }

    public function addAllocation()
    {
        $this->allocations[] = [
            'id' => null,
            'nama' => '',
            'persen' => 0.0,
            'is_absensi' => false,
        ];
    }

    public function removeAllocation(int $index)
    {
        if (isset($this->allocations[$index])) {
            unset($this->allocations[$index]);
            $this->allocations = array_values($this->allocations);
        }
    }

    public function saveSettings()
    {
        $this->validate([
            'umk' => 'required|numeric|min:0',
            'allocations.*.nama' => 'required|string|max:255',
            'allocations.*.persen' => 'required|numeric|min:0|max:100',
        ], [
            'allocations.*.nama.required' => 'Nama alokasi tunjangan wajib diisi.',
            'allocations.*.persen.required' => 'Persentase wajib diisi.',
        ]);

        // Validate total percentage sum equals 100%
        $totalPersen = 0.0;
        foreach ($this->allocations as $alloc) {
            $totalPersen += (double) $alloc['persen'];
        }

        if ($totalPersen !== 100.0) {
            $this->toast()
                ->error('Gagal !', 'Total persentase alokasi tunjangan harus tepat 100% (saat ini ' . $totalPersen . '%).')
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // Save UMK setting
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'umk'], ['value' => $this->umk, 'updated_at' => now()]);

            $keptIds = [];
            foreach ($this->allocations as $alloc) {
                if (!empty($alloc['id'])) {
                    DB::table('sdm_payroll_allowance_allocations')
                        ->where('id', $alloc['id'])
                        ->update([
                            'nama' => $alloc['nama'],
                            'persen' => $alloc['persen'],
                            'is_absensi' => $alloc['is_absensi'] ? 1 : 0,
                            'updated_at' => now(),
                        ]);
                    $keptIds[] = $alloc['id'];
                } else {
                    $newId = DB::table('sdm_payroll_allowance_allocations')->insertGetId([
                        'nama' => $alloc['nama'],
                        'persen' => $alloc['persen'],
                        'is_absensi' => $alloc['is_absensi'] ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $keptIds[] = $newId;
                }
            }

            // Delete removed allocations
            DB::table('sdm_payroll_allowance_allocations')
                ->whereNotIn('id', $keptIds)
                ->delete();

            DB::commit();

            // Reload configurations to fetch fresh IDs
            $this->allocations = [];
            $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
            foreach ($dbAllocations as $alloc) {
                $this->allocations[] = [
                    'id' => $alloc->id,
                    'nama' => $alloc->nama,
                    'persen' => (double) $alloc->persen,
                    'is_absensi' => (bool) $alloc->is_absensi,
                ];
            }

            $this->toast()
                ->success('Berhasil !', 'Pengaturan global & alokasi UMK berhasil disimpan.')
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
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
