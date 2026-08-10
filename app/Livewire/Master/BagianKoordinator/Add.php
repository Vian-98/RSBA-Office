<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $ruangan_id;
    public $karyawan_id;
    public $user_id = null; // user login yang akan jadi koordinator (opsional)
    public $aktif = true;

    protected $rules = [
        'ruangan_id'  => 'required|exists:ruangan,id',
        'karyawan_id' => 'required|exists:sdm_karyawan,id',
        'user_id'     => 'nullable|exists:users,id',
        'aktif'       => 'boolean',
    ];

    /**
     * Jika karyawan dipilih, auto-suggest user yang punya karyawan_id sama
     */
    public function updatedKaryawanId($value)
    {
        if ($value) {
            $user = User::where('karyawan_id', $value)->first();
            $this->user_id = $user?->id;

            $karyawan = Karyawan::find($value);
            $jabatanAktif = $karyawan?->jabatan->first();
            if ($jabatanAktif && $jabatanAktif->tingkat_id <= 3) {
                $this->toast()->warning('Informasi Jabatan', 'Karyawan ini menjabat sebagai ' . $jabatanAktif->nama . ' (Struktural). Pastikan rangkap tugas ini sudah sesuai.')->send();
            }
        }
    }

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
                'ruangan_id'  => $this->ruangan_id,
                'karyawan_id' => $this->karyawan_id,
                'user_id'     => $this->user_id ?: null,
                'aktif'       => $this->aktif,
            ]);

            // Hapus cache sidebar/permissions user jika ada akun login
            if ($this->user_id) {
                \Illuminate\Support\Facades\Cache::forget('user-sidebar-menu:' . $this->user_id);
                \Illuminate\Support\Facades\Cache::forget('user-permissions:view:' . $this->user_id);
            }

            $this->dispatch('new-ruangan-koordinator-created');
            $this->dispatch('close-modal', id: 'new-ruangan-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Ruangan berhasil ditambahkan.')->send();

            $this->reset(['ruangan_id', 'karyawan_id', 'user_id']);
            $this->aktif = true;
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public string $kategoriFilter = 'all';

    public function render()
    {
        $karyawanQuery = Karyawan::with('dokterRecord.spesialis')
            ->where('resign', null);

        if ($this->kategoriFilter === 'dokter') {
            $karyawanQuery->whereHas('dokterRecord');
        } elseif ($this->kategoriFilter === 'non_dokter') {
            $karyawanQuery->whereDoesntHave('dokterRecord');
        }

        $karyawanOptions = $karyawanQuery->get()->map(function ($k) {
            $isDokter = $k->dokterRecord ? true : false;
            $spesialis = $k->dokterRecord?->spesialis?->nama;
            $tag = $isDokter ? " [DOKTER" . ($spesialis ? " - $spesialis" : "") . "]" : " [KARYAWAN]";

            return [
                'value' => $k->id,
                'label' => $k->full_nama . $tag,
            ];
        })->toArray();

        return view('livewire.master.bagian-koordinator.add', [
            'ruanganOptions'  => Ruangan::select('id', 'nama')->where('is_active', true)->orderBy('nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => $karyawanOptions,
            'userOptions'     => User::with('karyawan')->get()->map(fn($u) => ['value' => $u->id, 'label' => $u->email . ($u->karyawan ? ' — ' . $u->karyawan->nama : '')])->toArray(),
        ]);
    }
}
