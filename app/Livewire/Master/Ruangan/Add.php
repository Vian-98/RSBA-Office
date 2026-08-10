<?php

namespace App\Livewire\Master\Ruangan;

use Throwable;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\RuanganKoordinator;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama;
    public $karyawan_id;

    public $rules = [
        'nama' => 'required|string',
        'karyawan_id' => 'nullable',
    ];

    function submit()
    {
        $this->validate();

        try {
            $ruangan = Ruangan::create([
                'nama' => $this->nama,
            ]);

            if ($this->karyawan_id) {
                $karyawan = Karyawan::find($this->karyawan_id);
                if ($karyawan) {
                    RuanganKoordinator::updateOrCreate(
                        [
                            'ruangan_id'  => $ruangan->id,
                            'karyawan_id' => $karyawan->id,
                        ],
                        [
                            'user_id' => $karyawan->user_id ?: null,
                            'aktif'   => true,
                        ]
                    );
                }
            }

            $this->toast()
                ->success('Berhasil', 'Ruangan baru berhasil dibuat.')
                ->send();

            $this->reset(['nama', 'karyawan_id']);
            $this->dispatch('new-ruangan-created');
            $this->dispatch('close-modal', id: 'new-ruangan');
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        $karyawanOptions = Karyawan::whereNull('resign_at')
            ->select('id', 'nama', 'gelar_depan', 'gelar_belakang')
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->full_nama,
            ])
            ->toArray();

        return view('livewire.master.ruangan.add', [
            'karyawanOptions' => $karyawanOptions,
        ]);
    }
}
