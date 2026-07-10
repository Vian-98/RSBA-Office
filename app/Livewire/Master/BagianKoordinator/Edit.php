<?php

namespace App\Livewire\Master\BagianKoordinator;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\BagianKoordinator;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?BagianKoordinator $record;

    public $bagian_id;
    public $karyawan_id;
    public $aktif;

    #[On('load-bagian-koordinator-data')]
    public function loadData($id)
    {
        $this->record = BagianKoordinator::findOrFail($id);
        $this->bagian_id = $this->record->bagian_id;
        $this->karyawan_id = $this->record->karyawan_id;
        $this->aktif = $this->record->aktif;
    }

    public function rules()
    {
        return [
            'bagian_id' => 'required|exists:bagian,id',
            'karyawan_id' => 'required|exists:sdm_karyawan,id',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        $this->validate();

        if ($this->record->bagian_id != $this->bagian_id || $this->record->karyawan_id != $this->karyawan_id) {
            $exists = BagianKoordinator::where('bagian_id', $this->bagian_id)
                ->where('karyawan_id', $this->karyawan_id)
                ->exists();

            if ($exists) {
                $this->toast()->error('Error', 'Karyawan tersebut sudah ditugaskan sebagai koordinator di bagian ini.')->send();
                return;
            }
        }

        try {
            $this->record->update([
                'bagian_id' => $this->bagian_id,
                'karyawan_id' => $this->karyawan_id,
                'aktif' => $this->aktif,
            ]);

            $this->dispatch('bagian-koordinator-updated');
            $this->dispatch('close-modal', id: 'edit-bagian-koordinator');

            $this->toast()->success('Berhasil', 'Koordinator Bagian berhasil diperbarui.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Error', 'Failed : ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian-koordinator.edit', [
            'bagianOptions' => Bagian::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
            'karyawanOptions' => Karyawan::select('id', 'nama')->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])->toArray(),
        ]);
    }
}
