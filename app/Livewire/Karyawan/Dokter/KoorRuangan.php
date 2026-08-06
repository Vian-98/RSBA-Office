<?php

namespace App\Livewire\Karyawan\Dokter;

use App\Models\Ruangan;
use App\Models\Sdm\Dokter;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Throwable;

class KoorRuangan extends Component
{
    use Interactions;

    public ?int $karyawanId = null;
    public array $selectedRuangan = [];
    public $allRuangan = [];
    public $karyawanInfo = null;
    public $spesialisInfo = null;

    public function mount(?int $karyawanId = null, ?int $dokterId = null)
    {
        if ($karyawanId || $dokterId) {
            $this->loadData($karyawanId, $dokterId);
        }
    }

    #[On('load-koor-ruangan')]
    public function onLoadKoorRuangan(?int $karyawanId = null, ?int $dokterId = null)
    {
        $this->loadData($karyawanId, $dokterId);
    }

    public function loadData(?int $karyawanId = null, ?int $dokterId = null)
    {
        if ($dokterId && !$karyawanId) {
            $dokter = Dokter::find($dokterId);
            $karyawanId = $dokter?->karyawan_id;
        }

        if (!$karyawanId) return;

        $this->karyawanId = $karyawanId;
        $this->karyawanInfo = Karyawan::with('user')->find($karyawanId);

        $dokter = Dokter::with('spesialis')->where('karyawan_id', $karyawanId)->first();
        $this->spesialisInfo = $dokter?->spesialis?->nama;

        // Ambil semua ruangan aktif
        $this->allRuangan = Ruangan::where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Ambil ruangan yang sudah di-assign ke karyawan ini (aktif)
        $this->selectedRuangan = DB::table('sdm_ruangan_koordinator')
            ->where('karyawan_id', $karyawanId)
            ->where('aktif', true)
            ->pluck('ruangan_id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function save()
    {
        if (!$this->karyawanId) return;

        $karyawan = Karyawan::with('user')->find($this->karyawanId);
        if (!$karyawan) return;

        $user = $karyawan->user ?? null;
        $userId = $user?->id;

        DB::beginTransaction();
        try {
            // Soft-disable semua assignment lama
            DB::table('sdm_ruangan_koordinator')
                ->where('karyawan_id', $this->karyawanId)
                ->update(['aktif' => false, 'updated_at' => now()]);

            // Insert/re-enable yang dipilih
            foreach ($this->selectedRuangan as $ruanganId) {
                DB::table('sdm_ruangan_koordinator')->updateOrInsert(
                    [
                        'ruangan_id'  => (int) $ruanganId,
                        'karyawan_id' => $this->karyawanId,
                    ],
                    [
                        'user_id'    => $userId,
                        'aktif'      => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::commit();

            if ($user) {
                $user->syncRoleFromJabatan();
                cache()->forget('user-permissions:view:' . $user->id);
            }
            $this->toast()
                ->success('Berhasil', "Ruangan untuk <b>{$karyawan->full_nama}</b> berhasil diupdate.")
                ->send();

            $this->dispatch('koor-ruangan-updated');
            $this->dispatch('ruangan-koordinator-updated');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.karyawan.dokter.koor-ruangan');
    }
}
