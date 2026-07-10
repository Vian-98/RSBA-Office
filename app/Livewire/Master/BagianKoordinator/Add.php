<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\BagianKoordinator;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $bagian_id;
    public $karyawan_id;
    public $aktif = true;

    protected $rules = [
        'bagian_id' => 'required|exists:bagian,id',
        'karyawan_id' => 'required|exists:sdm_karyawan,id',
        'aktif' => 'boolean'
    ];

    public function submit()
    {
        $this->aktif = filter_var($this->aktif, FILTER_VALIDATE_BOOLEAN);
        $this->validate();

        $exists = BagianKoordinator::where('bagian_id', $this->bagian_id)
            ->where('karyawan_id', $this->karyawan_id)
            ->exists();

        if ($exists) {
            $this->toast()->error('Error', 'Karyawan tersebut sudah ditugaskan sebagai koordinator di bagian ini.')->send();
            return;
        }

        try {
            BagianKoordinator::create([
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
            }

            $this->dispatch('new-bagian-koordinator-created');
            $this->dispatch('close-modal', id: 'new-bagian-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Bagian berhasil ditambahkan.')->send();
            
            $this->reset(['bagian_id', 'karyawan_id']);
            $this->aktif = true;
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian-koordinator.add', [
            'bagianOptions' => Bagian::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
        ]);
    }
}
