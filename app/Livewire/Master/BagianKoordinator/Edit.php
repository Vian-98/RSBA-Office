<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?int $recordId = null;

    public $ruangan_id;
    public $karyawan_id;
    public $aktif;

    public function mount($id)
    {
        $this->recordId = $id;
        $record = RuanganKoordinator::findOrFail($id);
        $this->ruangan_id = $record->ruangan_id;
        $this->karyawan_id = $record->karyawan_id;
        $this->aktif = $record->aktif;
    }

    public function rules()
    {
        return [
            'ruangan_id' => 'required|exists:ruangan,id',
            'karyawan_id' => 'required|exists:sdm_karyawan,id',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        $this->aktif = filter_var($this->aktif, FILTER_VALIDATE_BOOLEAN);
        $this->validate();

        $record = RuanganKoordinator::findOrFail($this->recordId);

        if ($record->ruangan_id != $this->ruangan_id || $record->karyawan_id != $this->karyawan_id) {
            $exists = RuanganKoordinator::where('ruangan_id', $this->ruangan_id)
                ->where('karyawan_id', $this->karyawan_id)
                ->exists();

            if ($exists) {
                $this->toast()->error('Error', 'Karyawan tersebut sudah ditugaskan sebagai koordinator di ruangan ini.')->send();
                return;
            }
        }

        try {
            $record->update([
                'ruangan_id' => $this->ruangan_id,
                'karyawan_id' => $this->karyawan_id,
                'aktif' => $this->aktif,
            ]);

            $karyawan = \App\Models\Sdm\Karyawan::with('user')->find($this->karyawan_id);
            if ($karyawan && $karyawan->user && $this->aktif) {
                $permissions = [
                    'view-kepegawaian-jadwal-kerja',
                    'add-kepegawaian-jadwal-kerja',
                    'edit-kepegawaian-jadwal-kerja',
                    'delete-kepegawaian-jadwal-kerja',
                ];
                $karyawan->user->givePermissionTo($permissions);
                
                // Update their ruangan_id to match the coordinated room!
                $karyawan->update(['ruangan_id' => $this->ruangan_id]);

                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $karyawan->user->id);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $karyawan->user->id);
            } elseif ($karyawan && $karyawan->user && !$this->aktif) {
                if (!$karyawan->ruanganKoordinasi()->exists()) {
                    $permissions = [
                        'view-kepegawaian-jadwal-kerja',
                        'add-kepegawaian-jadwal-kerja',
                        'edit-kepegawaian-jadwal-kerja',
                        'delete-kepegawaian-jadwal-kerja',
                    ];
                    foreach ($permissions as $perm) {
                        if ($karyawan->user->hasPermissionTo($perm)) {
                            $karyawan->user->revokePermissionTo($perm);
                        }
                    }
                }
                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $karyawan->user->id);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $karyawan->user->id);
            }

            $this->dispatch('ruangan-koordinator-updated');
            $this->dispatch('close-modal', id: 'edit-ruangan-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Ruangan berhasil diperbarui.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian-koordinator.edit', [
            'ruanganOptions' => Ruangan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
        ]);
    }
}
