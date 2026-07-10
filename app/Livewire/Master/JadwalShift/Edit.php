<?php

namespace App\Livewire\Master\JadwalShift;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalShift;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?JadwalShift $shift;

    public $kode;
    public $nama;
    public $jam_masuk;
    public $jam_keluar;
    public $toleransi_telat_menit;
    public $warna;
    public $lintas_hari;
    public $aktif;

    #[On('load-shift-data')]
    public function loadData($id)
    {
        $this->shift = JadwalShift::findOrFail($id);
        $this->kode = $this->shift->kode;
        $this->nama = $this->shift->nama;
        $this->jam_masuk = substr($this->shift->jam_masuk, 0, 5);
        $this->jam_keluar = substr($this->shift->jam_keluar, 0, 5);
        $this->toleransi_telat_menit = $this->shift->toleransi_telat_menit;
        $this->warna = $this->shift->warna;
        $this->lintas_hari = $this->shift->lintas_hari;
        $this->aktif = $this->shift->aktif;
    }

    public function rules()
    {
        return [
            'kode' => 'required|string|max:10|unique:sdm_jadwal_shift,kode,' . ($this->shift->id ?? ''),
            'nama' => 'required|string|max:30',
            'jam_masuk' => 'required',
            'jam_keluar' => 'required',
            'toleransi_telat_menit' => 'required|integer|min:0',
            'warna' => 'nullable|string|max:10',
            'lintas_hari' => 'boolean',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        $this->validate();

        try {
            $this->shift->update([
                'kode' => $this->kode,
                'nama' => $this->nama,
                'jam_masuk' => $this->jam_masuk,
                'jam_keluar' => $this->jam_keluar,
                'toleransi_telat_menit' => $this->toleransi_telat_menit,
                'warna' => $this->warna,
                'lintas_hari' => $this->lintas_hari,
                'aktif' => $this->aktif,
            ]);

            $this->dispatch('jadwal-shift-updated');
            $this->dispatch('close-modal', id: 'edit-jadwal-shift');

            $this->toast()
                ->success('Berhasil', 'Jadwal Shift berhasil diperbarui.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Error', 'Failed : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.jadwal-shift.edit');
    }
}
