<?php

namespace App\Livewire\Master\JadwalShift;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalShift;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $kode;
    public $nama;
    public $jam_masuk;
    public $jam_keluar;
    public $toleransi_telat_menit = 15;
    public $warna;
    public $lintas_hari = false;
    public $aktif = true;

    protected $rules = [
        'kode' => 'required|string|max:10|unique:sdm_jadwal_shift,kode',
        'nama' => 'required|string|max:30',
        'jam_masuk' => 'required',
        'jam_keluar' => 'required',
        'toleransi_telat_menit' => 'required|integer|min:0',
        'warna' => 'nullable|string|max:10',
        'lintas_hari' => 'boolean',
        'aktif' => 'boolean'
    ];

    public function submit()
    {
        $this->validate();

        $data = [
            'kode' => $this->kode,
            'nama' => $this->nama,
            'jam_masuk' => $this->jam_masuk,
            'jam_keluar' => $this->jam_keluar,
            'toleransi_telat_menit' => $this->toleransi_telat_menit,
            'warna' => $this->warna,
            'lintas_hari' => $this->lintas_hari,
            'aktif' => $this->aktif,
        ];

        try {
            JadwalShift::create($data);

            $this->dispatch('new-jadwal-shift-created');
            $this->dispatch('close-modal', id: 'new-jadwal-shift');

            $this->toast()
                ->success('Berhasil', 'Jadwal Shift baru berhasil dibuat.')
                ->send();
            
            $this->reset(['kode', 'nama', 'jam_masuk', 'jam_keluar', 'warna']);
            $this->toleransi_telat_menit = 15;
            $this->lintas_hari = false;
            $this->aktif = true;
        } catch (Throwable $e) {
            $this->toast()
                ->error('Error', 'Failed : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.jadwal-shift.add');
    }
}
