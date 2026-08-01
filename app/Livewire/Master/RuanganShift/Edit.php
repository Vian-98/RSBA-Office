<?php

namespace App\Livewire\Master\RuanganShift;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganShift;
use App\Models\Ruangan;
use App\Models\Sdm\JadwalShift;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?RuanganShift $record;

    public $ruangan_id;
    public $shift_id;
    public $jam_masuk_override;
    public $jam_keluar_override;
    public $toleransi_telat_menit_override;

    #[On('load-ruangan-shift-data')]
    public function loadData($id)
    {
        $this->record = RuanganShift::findOrFail($id);
        $this->ruangan_id = $this->record->ruangan_id;
        $this->shift_id = $this->record->shift_id;
        $this->jam_masuk_override = $this->record->jam_masuk_override ? substr($this->record->jam_masuk_override, 0, 5) : null;
        $this->jam_keluar_override = $this->record->jam_keluar_override ? substr($this->record->jam_keluar_override, 0, 5) : null;
        $this->toleransi_telat_menit_override = $this->record->toleransi_telat_menit_override;
    }

    public function rules()
    {
        return [
            'ruangan_id' => 'required|exists:ruangan,id',
            'shift_id' => 'required|exists:sdm_jadwal_shift,id',
            'jam_masuk_override' => 'nullable|date_format:H:i',
            'jam_keluar_override' => 'nullable|date_format:H:i',
            'toleransi_telat_menit_override' => 'nullable|integer|min:0|max:120',
        ];
    }

    public function submit()
    {
        $this->validate();

        if ($this->record->ruangan_id != $this->ruangan_id || $this->record->shift_id != $this->shift_id) {
            $exists = RuanganShift::where('ruangan_id', $this->ruangan_id)
                ->where('shift_id', $this->shift_id)
                ->exists();

            if ($exists) {
                $this->toast()->error('Error', 'Shift tersebut sudah didaftarkan pada ruangan ini.')->send();
                return;
            }
        }

        try {
            $this->record->update([
                'ruangan_id' => $this->ruangan_id,
                'shift_id' => $this->shift_id,
                'jam_masuk_override' => $this->jam_masuk_override ?: null,
                'jam_keluar_override' => $this->jam_keluar_override ?: null,
                'toleransi_telat_menit_override' => $this->toleransi_telat_menit_override ?: null,
            ]);

            $this->dispatch('ruangan-shift-updated');
            $this->dispatch('close-modal', id: 'edit-ruangan-shift');

            $this->toast()->success('Berhasil', 'Validasi Shift Ruangan berhasil diperbarui.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.ruangan-shift.edit', [
            'ruanganOptions' => Ruangan::select('id', 'nama')->where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'shiftOptions' => JadwalShift::select('id', 'nama', 'jam_masuk', 'jam_keluar')->where('aktif', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama . ' (' . substr($item->jam_masuk, 0, 5) . '-' . substr($item->jam_keluar, 0, 5) . ')'])->toArray(),
        ]);
    }
}
