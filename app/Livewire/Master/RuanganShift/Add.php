<?php

namespace App\Livewire\Master\RuanganShift;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganShift;
use App\Models\Ruangan;
use App\Models\Sdm\JadwalShift;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $ruangan_id;
    public $shift_id;
    public $jam_masuk_override;
    public $jam_keluar_override;
    public $toleransi_telat_menit_override;

    protected $rules = [
        'ruangan_id' => 'required|exists:ruangan,id',
        'shift_id' => 'required|exists:sdm_jadwal_shift,id',
        'jam_masuk_override' => 'nullable|date_format:H:i',
        'jam_keluar_override' => 'nullable|date_format:H:i',
        'toleransi_telat_menit_override' => 'nullable|integer|min:0|max:120',
    ];

    public function submit()
    {
        $this->validate();

        $exists = RuanganShift::where('ruangan_id', $this->ruangan_id)
            ->where('shift_id', $this->shift_id)
            ->exists();

        if ($exists) {
            $this->toast()->error('Error', 'Shift tersebut sudah didaftarkan pada ruangan ini.')->send();
            return;
        }

        try {
            RuanganShift::create([
                'ruangan_id' => $this->ruangan_id,
                'shift_id' => $this->shift_id,
                'jam_masuk_override' => $this->jam_masuk_override ?: null,
                'jam_keluar_override' => $this->jam_keluar_override ?: null,
                'toleransi_telat_menit_override' => $this->toleransi_telat_menit_override ?: null,
            ]);

            $this->dispatch('new-ruangan-shift-created');
            $this->dispatch('close-modal', id: 'new-ruangan-shift');

            $this->toast()->success('Berhasil', 'Validasi Shift Ruangan berhasil ditambahkan.')->send();
            
            $this->reset(['ruangan_id', 'shift_id', 'jam_masuk_override', 'jam_keluar_override', 'toleransi_telat_menit_override']);
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.ruangan-shift.add', [
            'ruanganOptions' => Ruangan::select('id', 'nama')->where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'shiftOptions' => JadwalShift::select('id', 'nama', 'jam_masuk', 'jam_keluar')->where('aktif', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama . ' (' . substr($item->jam_masuk, 0, 5) . '-' . substr($item->jam_keluar, 0, 5) . ')'])->toArray(),
        ]);
    }
}
