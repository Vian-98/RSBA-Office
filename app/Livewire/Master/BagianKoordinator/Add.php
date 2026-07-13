<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $ruangan_id;
    public $karyawan_id;
    public $aktif = true;

    protected $rules = [
        'ruangan_id' => 'required|exists:ruangan,id',
        'karyawan_id' => 'required|exists:sdm_karyawan,id',
        'aktif' => 'boolean'
    ];

    public function submit()
    {
        $this->aktif = filter_var($this->aktif, FILTER_VALIDATE_BOOLEAN);
        $this->validate();

        $exists = RuanganKoordinator::where('ruangan_id', $this->ruangan_id)
            ->where('karyawan_id', $this->karyawan_id)
            ->exists();

        if ($exists) {
            $this->toast()->error('Error', 'Karyawan tersebut sudah ditugaskan sebagai koordinator di ruangan ini.')->send();
            return;
        }

        try {
            RuanganKoordinator::create([
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
                
                // Set the karyawan's ruangan_id to match their coordinated ruangan to ensure proper scoping!
                $karyawan->update(['ruangan_id' => $this->ruangan_id]);

                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $karyawan->user->id);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $karyawan->user->id);
            }

            $this->dispatch('new-ruangan-koordinator-created');
            $this->dispatch('close-modal', id: 'new-ruangan-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Ruangan berhasil ditambahkan.')->send();
            
            $this->reset(['ruangan_id', 'karyawan_id']);
            $this->aktif = true;
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian-koordinator.add', [
            'ruanganOptions' => Ruangan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
        ]);
    }
}
