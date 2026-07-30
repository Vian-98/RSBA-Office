<?php

namespace App\Livewire\Master\JadwalAturan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalAturan;
use App\Models\Sdm\Bagian;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;
use App\Enums\KodeAturanJadwal;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $bagian_id;
    public $kode;
    public $nilai;
    public $aktif = true;

    protected $rules = [
        'bagian_id' => 'required|exists:bagian,id',
        'kode' => 'required|string',
        'nilai' => 'required|string',
        'aktif' => 'boolean'
    ];

    public function submit()
    {
        $this->validate();

        $exists = JadwalAturan::where('bagian_id', $this->bagian_id)
            ->where('kode', $this->kode)
            ->exists();

        if ($exists) {
            $this->toast()->error('Error', 'Aturan dengan kode tersebut sudah ada untuk bagian ini.')->send();
            return;
        }

        try {
            JadwalAturan::create([
                'bagian_id' => $this->bagian_id,
                'kode' => $this->kode,
                'nilai' => $this->nilai,
                'aktif' => $this->aktif,
            ]);

            $this->dispatch('new-jadwal-aturan-created');
            $this->dispatch('close-modal', id: 'new-jadwal-aturan');

            $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil ditambahkan.')->send();
            
            $this->reset(['bagian_id', 'kode', 'nilai']);
            $this->aktif = true;
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $kodeOptions = collect(KodeAturanJadwal::cases())->map(fn($enum) => [
            'value' => $enum->value,
            'label' => $enum->nama()
        ])->toArray();

        return view('livewire.master.jadwal-aturan.add', [
            'bagianOptions' => Bagian::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'kodeOptions' => $kodeOptions,
        ]);
    }
}
