<?php

namespace App\Livewire\Master\JadwalAturan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalAturan;
use App\Models\Sdm\Bagian;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;
use App\Enums\KodeAturanJadwal;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?JadwalAturan $record;

    public $bagian_id;
    public $kode;
    public $nilai;
    public $aktif;

    #[On('load-jadwal-aturan-data')]
    public function loadData($id)
    {
        $this->record = JadwalAturan::findOrFail($id);
        $this->bagian_id = $this->record->bagian_id;
        $this->kode = $this->record->kode;
        $this->nilai = $this->record->nilai;
        $this->aktif = $this->record->aktif;
    }

    public function rules()
    {
        return [
            'bagian_id' => 'required|exists:bagian,id',
            'kode' => 'required|string',
            'nilai' => 'required|string',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        $this->validate();

        if ($this->record->bagian_id != $this->bagian_id || $this->record->kode != $this->kode) {
            $exists = JadwalAturan::where('bagian_id', $this->bagian_id)
                ->where('kode', $this->kode)
                ->exists();

            if ($exists) {
                $this->toast()->error('Error', 'Aturan dengan kode tersebut sudah ada untuk bagian ini.')->send();
                return;
            }
        }

        try {
            $this->record->update([
                'bagian_id' => $this->bagian_id,
                'kode' => $this->kode,
                'nilai' => $this->nilai,
                'aktif' => $this->aktif,
            ]);

            $this->dispatch('jadwal-aturan-updated');
            $this->dispatch('close-modal', id: 'edit-jadwal-aturan');

            $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil diperbarui.')->send();
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

        return view('livewire.master.jadwal-aturan.edit', [
            'bagianOptions' => Bagian::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'kodeOptions' => $kodeOptions,
        ]);
    }
}
