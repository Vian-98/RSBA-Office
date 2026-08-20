<?php

namespace App\Livewire\Master\TunjanganGolongan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use App\Models\Gaji\PayrollGolonganMatrix;

#[Title('Master Tunjangan Golongan & Config Gaji')]
class Index extends Component
{
    use Interactions;

    // Tab navigation state: 'tunjangan' or 'matrix'
    public string $activeTab = 'tunjangan';

    // Golongan allowances map (key: golongan_grade, value: tunjangan_golongan)
    public array $allowances = [];

    // Matrix fields
    public array $matrix = [];
    public array $kelompoks = [];
    public array $masaKerjas = [];

    // Modal/Form additions
    public $newMasaKerja = '';
    public $newKelompok = '';

    public function mount()
    {
        // Load Golongan allowances
        $golongans = DB::table('sdm_payroll_golongans')->orderBy('golongan', 'asc')->get();
        foreach ($golongans as $gol) {
            $this->allowances[$gol->golongan] = (int) $gol->tunjangan_golongan;
        }

        // Load Matrix structures
        $this->loadMatrix();
    }

    public function loadMatrix()
    {
        $records = DB::table('sdm_payroll_golongan_matrix')
            ->orderBy('urutan_kelompok', 'asc')
            ->orderBy('masa_kerja_min', 'asc')
            ->get();

        $this->kelompoks = $records->pluck('kelompok_pendidikan')->unique()->values()->toArray();
        
        $mks = $records->pluck('masa_kerja_min')->unique()->values()->toArray();
        sort($mks);
        $this->masaKerjas = $mks;

        $this->matrix = [];
        foreach ($records as $r) {
            $this->matrix[$r->kelompok_pendidikan][$r->masa_kerja_min] = (int) $r->golongan;
        }
    }

    public function saveAllowances()
    {
        DB::beginTransaction();
        try {
            $oldAllowances = DB::table('sdm_payroll_golongans')->get()->pluck('tunjangan_golongan', 'golongan')->toArray();

            foreach ($this->allowances as $gol => $val) {
                $cleanedVal = is_string($val) ? str_replace('.', '', $val) : $val;
                $oldVal = $oldAllowances[$gol] ?? 0.0;
                if ((double)$oldVal !== (double)$cleanedVal) {
                    DB::table('sdm_payroll_golongan_logs')->insert([
                        'tipe' => 'Tunjangan',
                        'kunci' => "Nominal Golongan {$gol}",
                        'nilai_lama' => 'Rp ' . number_format($oldVal, 0, ',', '.'),
                        'nilai_baru' => 'Rp ' . number_format($cleanedVal, 0, ',', '.'),
                        'user_id' => auth()->id() ?? 1,
                        'created_at' => now(),
                    ]);

                    DB::table('sdm_payroll_golongans')
                        ->where('golongan', $gol)
                        ->update([
                            'tunjangan_golongan' => (double) $cleanedVal,
                            'updated_at' => now()
                        ]);
                }
            }

            DB::commit();

            // Clear notifications cache for all admins/SDM staff
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

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

    public function saveMatrix()
    {
        DB::beginTransaction();
        try {
            $oldMatrix = DB::table('sdm_payroll_golongan_matrix')->get();
            $oldMap = [];
            foreach ($oldMatrix as $om) {
                $oldMap[$om->kelompok_pendidikan][$om->masa_kerja_min] = $om->golongan;
            }

            foreach ($this->matrix as $kelompok => $cols) {
                $urutan = DB::table('sdm_payroll_golongan_matrix')
                    ->where('kelompok_pendidikan', $kelompok)
                    ->value('urutan_kelompok') ?: 99;

                foreach ($cols as $masaKerja => $golongan) {
                    $golVal = max(1, min(15, (int) $golongan)); // Limit to Grade 1 - 15
                    $oldVal = $oldMap[$kelompok][$masaKerja] ?? null;

                    if ($oldVal === null || (int)$oldVal !== $golVal) {
                        DB::table('sdm_payroll_golongan_logs')->insert([
                            'tipe' => 'Matrix',
                            'kunci' => "{$kelompok} (Masa Kerja {$masaKerja} Th)",
                            'nilai_lama' => $oldVal !== null ? "Grade {$oldVal}" : 'N/A',
                            'nilai_baru' => "Grade {$golVal}",
                            'user_id' => auth()->id() ?? 1,
                            'created_at' => now(),
                        ]);

                        DB::table('sdm_payroll_golongan_matrix')->updateOrInsert(
                            [
                                'kelompok_pendidikan' => $kelompok,
                                'masa_kerja_min' => (int) $masaKerja
                            ],
                            [
                                'golongan' => $golVal,
                                'urutan_kelompok' => $urutan,
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            DB::commit();

            // Clear cache
            PayrollGolonganMatrix::flushCache();

            // Clear notifications cache for admins
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

            $this->toast()
                ->success('Berhasil !', 'Matrix golongan berhasil diperbarui.')
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function addColumn()
    {
        $this->validate([
            'newMasaKerja' => 'required|integer|min:0|max:100'
        ], [
            'newMasaKerja.required' => 'Masa kerja harus diisi.',
            'newMasaKerja.integer' => 'Masa kerja harus berupa angka.',
            'newMasaKerja.min' => 'Masa kerja minimal 0 tahun.'
        ]);

        $mk = (int) $this->newMasaKerja;

        // Check if exists
        $exists = DB::table('sdm_payroll_golongan_matrix')
            ->where('masa_kerja_min', $mk)
            ->exists();

        if ($exists) {
            $this->toast()->error('Gagal', 'Masa kerja tersebut sudah terdaftar pada matrix.')->send();
            return;
        }

        // Insert column for all groups
        $kelompoks = DB::table('sdm_payroll_golongan_matrix')
            ->select('kelompok_pendidikan', 'urutan_kelompok')
            ->distinct()
            ->get();

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_golongan_logs')->insert([
                'tipe' => 'Matrix (Struktur)',
                'kunci' => "Tambah Kolom Masa Kerja",
                'nilai_lama' => null,
                'nilai_baru' => "{$mk} Th",
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
            ]);

            foreach ($kelompoks as $k) {
                DB::table('sdm_payroll_golongan_matrix')->insert([
                    'kelompok_pendidikan' => $k->kelompok_pendidikan,
                    'masa_kerja_min' => $mk,
                    'golongan' => 15, // Default grade
                    'urutan_kelompok' => $k->urutan_kelompok,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            DB::commit();

            PayrollGolonganMatrix::flushCache();

            // Clear notifications cache for admins
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

            $this->newMasaKerja = '';
            $this->loadMatrix();

            $this->toast()->success('Berhasil', 'Kolom masa kerja ' . $mk . ' tahun berhasil ditambahkan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function removeColumn($mk)
    {
        $mk = (int) $mk;
        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_golongan_logs')->insert([
                'tipe' => 'Matrix (Struktur)',
                'kunci' => "Hapus Kolom Masa Kerja",
                'nilai_lama' => "{$mk} Th",
                'nilai_baru' => 'Dihapus',
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
            ]);

            DB::table('sdm_payroll_golongan_matrix')
                ->where('masa_kerja_min', $mk)
                ->delete();
            DB::commit();

            PayrollGolonganMatrix::flushCache();

            // Clear notifications cache for admins
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

            $this->loadMatrix();

            $this->toast()->success('Berhasil', 'Kolom masa kerja ' . $mk . ' tahun dihapus dari matrix.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function addRow()
    {
        $this->validate([
            'newKelompok' => 'required|string|max:30'
        ], [
            'newKelompok.required' => 'Nama kelompok pendidikan harus diisi.',
            'newKelompok.max' => 'Nama kelompok maksimal 30 karakter.'
        ]);

        $kelompok = trim($this->newKelompok);

        // Check if exists
        $exists = DB::table('sdm_payroll_golongan_matrix')
            ->where('kelompok_pendidikan', $kelompok)
            ->exists();

        if ($exists) {
            $this->toast()->error('Gagal', 'Kelompok pendidikan tersebut sudah terdaftar pada matrix.')->send();
            return;
        }

        $maxUrutan = DB::table('sdm_payroll_golongan_matrix')->max('urutan_kelompok') ?: 0;
        $newUrutan = $maxUrutan + 1;

        $columns = DB::table('sdm_payroll_golongan_matrix')
            ->select('masa_kerja_min')
            ->distinct()
            ->pluck('masa_kerja_min')
            ->toArray();

        if (empty($columns)) {
            $columns = [0];
        }

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_golongan_logs')->insert([
                'tipe' => 'Matrix (Struktur)',
                'kunci' => "Tambah Baris Pendidikan",
                'nilai_lama' => null,
                'nilai_baru' => $kelompok,
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
            ]);

            foreach ($columns as $mk) {
                DB::table('sdm_payroll_golongan_matrix')->insert([
                    'kelompok_pendidikan' => $kelompok,
                    'masa_kerja_min' => $mk,
                    'golongan' => 15, // Default grade
                    'urutan_kelompok' => $newUrutan,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            DB::commit();

            PayrollGolonganMatrix::flushCache();

            // Clear notifications cache for admins
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

            $this->newKelompok = '';
            $this->loadMatrix();

            $this->toast()->success('Berhasil', 'Kelompok pendidikan "' . $kelompok . '" berhasil ditambahkan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function removeRow($kelompok)
    {
        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_golongan_logs')->insert([
                'tipe' => 'Matrix (Struktur)',
                'kunci' => "Hapus Baris Pendidikan",
                'nilai_lama' => $kelompok,
                'nilai_baru' => 'Dihapus',
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
            ]);

            DB::table('sdm_payroll_golongan_matrix')
                ->where('kelompok_pendidikan', $kelompok)
                ->delete();
            DB::commit();

            PayrollGolonganMatrix::flushCache();

            // Clear notifications cache for admins
            try {
                $usersToNotify = \App\Models\User::permission('manage-kepegawaian-master-tunjangan-golongan')->get();
                foreach ($usersToNotify as $u) {
                    \Illuminate\Support\Facades\Cache::forget('notif_user_' . $u->id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget('notif_user_' . auth()->id());
            }

            $this->dispatch('notification-updated');

            $this->loadMatrix();

            $this->toast()->success('Berhasil', 'Kelompok pendidikan "' . $kelompok . '" dihapus dari matrix.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $e->getMessage())->send();
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
