<?php

namespace App\Livewire\Gaji\Rekap\Concerns;

use Illuminate\Support\Facades\DB;

trait HasPayrollParametersModal
{
    // Payroll Configuration Parameters
    public $config_umk;
    public $config_potongan_telat;
    public $config_toleransi_telat;

    // Dynamic 25% UMK allocations list
    public array $allocations = [];

    public function mountHasPayrollParametersModal(): void
    {
        // Load payroll parameters from DB
        $this->config_umk = DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value') ?: 3000000;
        $this->config_potongan_telat = DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_kejadian')->value('value');
        if (is_null($this->config_potongan_telat)) {
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'potongan_telat_per_kejadian'], ['value' => '50000', 'created_at' => now(), 'updated_at' => now()]);
            $this->config_potongan_telat = 50000;
        }

        $this->config_toleransi_telat = DB::table('sdm_payroll_settings')->where('key', 'toleransi_telat_menit')->value('value');
        if (is_null($this->config_toleransi_telat)) {
            DB::table('sdm_payroll_settings')->insert(['key' => 'toleransi_telat_menit', 'value' => '0', 'created_at' => now(), 'updated_at' => now()]);
            $this->config_toleransi_telat = 0;
        }

        // Load 25% UMK allocations list
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
    }

    public function addAllocation(): void
    {
        $this->allocations[] = [
            'id' => null,
            'nama' => '',
            'persen' => 0.0,
            'is_absensi' => false,
        ];
    }

    public function removeAllocation(int $index): void
    {
        if (isset($this->allocations[$index])) {
            unset($this->allocations[$index]);
            $this->allocations = array_values($this->allocations);
        }
    }

    public function saveParameters(): void
    {
        if (is_string($this->config_umk)) {
            $this->config_umk = str_replace('.', '', $this->config_umk);
        }
        if (is_string($this->config_potongan_telat)) {
            $this->config_potongan_telat = str_replace('.', '', $this->config_potongan_telat);
        }

        $this->validate([
            'config_umk' => 'required|numeric|min:0',
            'config_potongan_telat' => 'required|numeric|min:0',
            'config_toleransi_telat' => 'required|integer|min:0',
            'allocations.*.nama' => 'required|string|max:255',
            'allocations.*.persen' => 'required|numeric|min:0|max:100',
        ], [
            'config_umk.required' => 'UMK wajib diisi.',
            'config_umk.numeric' => 'UMK harus berupa angka.',
            'config_umk.min' => 'UMK tidak boleh kurang dari 0.',
            'config_potongan_telat.required' => 'Potongan telat wajib diisi.',
            'config_potongan_telat.numeric' => 'Potongan telat harus berupa angka.',
            'config_potongan_telat.min' => 'Potongan telat tidak boleh kurang dari 0.',
            'config_toleransi_telat.required' => 'Toleransi keterlambatan wajib diisi.',
            'config_toleransi_telat.integer' => 'Toleransi keterlambatan harus berupa bilangan bulat.',
            'config_toleransi_telat.min' => 'Toleransi keterlambatan tidak boleh kurang dari 0.',
            'allocations.*.nama.required' => 'Nama alokasi tunjangan wajib diisi.',
            'allocations.*.persen.required' => 'Persentase wajib diisi.',
        ]);

        $totalPersen = 0.0;
        foreach ($this->allocations as $alloc) {
            $totalPersen += (double) $alloc['persen'];
        }

        if ($totalPersen !== 100.0) {
            $this->toast()->error('Gagal !', 'Total persentase alokasi tunjangan harus tepat 100% (saat ini ' . $totalPersen . '%).')->send();
            return;
        }

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'umk'], ['value' => $this->config_umk, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'potongan_telat_per_kejadian'], ['value' => $this->config_potongan_telat, 'updated_at' => now()]);
            DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'toleransi_telat_menit'], ['value' => $this->config_toleransi_telat, 'updated_at' => now()]);

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

            DB::table('sdm_payroll_allowance_allocations')
                ->whereNotIn('id', $keptIds)
                ->delete();

            DB::commit();

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

            $this->dispatch('close-modal', id: 'modal-payroll-parameters');
            $this->toast()->success('Berhasil !', 'Parameter payroll & alokasi tunjangan berhasil disimpan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }
}
