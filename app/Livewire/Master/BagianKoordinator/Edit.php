<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
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
    public $user_id;
    public $aktif;

    public function mount($id)
    {
        $this->recordId  = $id;
        $record          = RuanganKoordinator::findOrFail($id);
        $this->ruangan_id  = $record->ruangan_id;
        $this->karyawan_id = $record->karyawan_id;
        $this->user_id     = $record->user_id;
        $this->aktif       = $record->aktif;
    }

    /**
     * Jika karyawan diganti, auto-suggest user baru
     */
    public function updatedKaryawanId($value)
    {
        if ($value) {
            $user = User::where('karyawan_id', $value)->first();
            $this->user_id = $user?->id;
        }
    }

    public function rules()
    {
        return [
            'ruangan_id'  => 'required|exists:ruangan,id',
            'karyawan_id' => 'required|exists:sdm_karyawan,id',
            'user_id'     => 'nullable|exists:users,id',
            'aktif'       => 'boolean',
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
            // Hapus cache untuk user lama jika user berubah
            $oldUserId = $record->user_id;

            $record->update([
                'ruangan_id'  => $this->ruangan_id,
                'karyawan_id' => $this->karyawan_id,
                'user_id'     => $this->user_id ?: null,
                'aktif'       => $this->aktif,
            ]);

            // Bersihkan cache sidebar untuk user lama dan baru
            foreach (array_unique(array_filter([$oldUserId, $this->user_id])) as $uid) {
                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $uid);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $uid);
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
            'ruanganOptions'  => Ruangan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'userOptions'     => User::with('karyawan')->get()->map(fn($u) => ['value' => $u->id, 'label' => $u->email . ($u->karyawan ? ' — ' . $u->karyawan->nama : '')])->toArray(),
        ]);
    }
}
