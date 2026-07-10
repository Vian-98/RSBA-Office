<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\BagianKoordinator;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?int $recordId = null;

    public $bagian_id;
    public $karyawan_id;
    public $aktif;

    public function mount($id)
    {
        $this->recordId = $id;
        $record = BagianKoordinator::findOrFail($id);
        $this->bagian_id = $record->bagian_id;
        $this->karyawan_id = $record->karyawan_id;
        $this->aktif = $record->aktif;
    }

    public function rules()
    {
        return [
            'bagian_id' => 'required|exists:bagian,id',
            'karyawan_id' => 'required|exists:sdm_karyawan,id',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        $this->aktif = filter_var($this->aktif, FILTER_VALIDATE_BOOLEAN);
        $this->validate();

        $record = BagianKoordinator::findOrFail($this->recordId);

        if ($record->bagian_id != $this->bagian_id || $record->karyawan_id != $this->karyawan_id) {
            $exists = BagianKoordinator::where('bagian_id', $this->bagian_id)
                ->where('karyawan_id', $this->karyawan_id)
                ->exists();

            if ($exists) {
                $this->toast()->error('Error', 'Karyawan tersebut sudah ditugaskan sebagai koordinator di bagian ini.')->send();
                return;
            }
        }

        try {
            $record->update([
                'bagian_id' => $this->bagian_id,
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
                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $karyawan->user->id);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $karyawan->user->id);
            } elseif ($karyawan && $karyawan->user && !$this->aktif) {
                if (!$karyawan->bagianKoordinasi()->exists()) {
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

            $this->dispatch('bagian-koordinator-updated');
            $this->dispatch('close-modal', id: 'edit-bagian-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Bagian berhasil diperbarui.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian-koordinator.edit', [
            'bagianOptions' => Bagian::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
        ]);
    }
}
