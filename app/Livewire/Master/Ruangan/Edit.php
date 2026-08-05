<?php

namespace App\Livewire\Master\Ruangan;

use Throwable;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\RuanganKoordinator;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public $nama;
    public $karyawan_id;
    public ?Ruangan $ruangan;

    public $rules = [
        'nama' => 'required|string',
        'karyawan_id' => 'nullable',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->loadData($id);
        }
    }

    #[On('load-edit-ruangan')]
    public function loadData($id)
    {
        $this->ruangan = Ruangan::with('koordinatorAktif')->findOrFail($id);
        $this->nama = $this->ruangan->nama;
        $this->karyawan_id = $this->ruangan->koordinatorAktif?->karyawan_id;
    }

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->ruangan->nama = $this->nama;
            $this->ruangan->save();

            // Update Koordinator Ruangan
            $currentKoor = $this->ruangan->koordinatorAktif;
            $newKaryawanId = $this->karyawan_id ?: null;

            if (($currentKoor?->karyawan_id ?? null) != $newKaryawanId) {
                // Non-aktifkan seluruh penugasan koordinator lama untuk ruangan ini
                RuanganKoordinator::where('ruangan_id', $this->ruangan->id)
                    ->update(['aktif' => false]);

                // Buat/aktifkan penugasan baru jika karyawan dipilih
                if ($newKaryawanId) {
                    $karyawan = Karyawan::find($newKaryawanId);
                    if ($karyawan) {
                        RuanganKoordinator::updateOrCreate(
                            [
                                'ruangan_id'  => $this->ruangan->id,
                                'karyawan_id' => $karyawan->id,
                            ],
                            [
                                'user_id' => $karyawan->user_id ?: null,
                                'aktif'   => true,
                            ]
                        );
                    }
                }
            }

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Ruangan berhasil diperbarui.')
                ->send();

            $this->dispatch('new-ruangan-updated');
            $this->dispatch('close-modal', id: 'modal-edit-ruangan');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        $ruanganId = $this->ruangan?->id;
        $karyawanQuery = Karyawan::whereNull('resign_at');

        if ($ruanganId) {
            $roomQuery = (clone $karyawanQuery)->where(function ($q) use ($ruanganId) {
                $q->where('ruangan_id', $ruanganId)
                  ->orWhereHas('ruangans', function ($rq) use ($ruanganId) {
                      $rq->where('ruangan.id', $ruanganId);
                  });
            });

            // Jika ada karyawan bertugas di ruangan ini, batasi opsi hanya karyawan ruangan ini
            if ($roomQuery->count() > 0) {
                $karyawanQuery = $roomQuery;
            }
        }

        $karyawanOptions = $karyawanQuery
            ->select('id', 'nama', 'gelar_depan', 'gelar_belakang')
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->full_nama,
            ])
            ->toArray();

        array_unshift($karyawanOptions, ['value' => '', 'label' => '-- Belum Ada Yang Menjabat --']);

        return view('livewire.master.ruangan.edit', [
            'karyawanOptions' => $karyawanOptions,
        ]);
    }
}
