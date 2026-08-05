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

    public function mount($id = null)
    {
        if ($id) {
            $this->loadData($id);
        }
    }

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
            'bagian_id' => 'nullable|exists:bagian,id',
            'kode' => 'required|string',
            'nilai' => 'required|string',
            'aktif' => 'boolean'
        ];
    }

    public function submit()
    {
        abort_unless(
            auth()->user()?->can('edit-kepegawaian-master-jadwal-aturan'),
            403,
            'Tidak memiliki akses untuk mengubah Aturan Jadwal.'
        );

        $this->validate();

        $targetBagianId = $this->bagian_id ?: null;

        try {
            $existing = JadwalAturan::where('bagian_id', $targetBagianId)
                ->where('kode', $this->kode)
                ->where('id', '!=', $this->record->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'nilai' => $this->nilai,
                    'aktif' => $this->aktif,
                ]);
                $this->record->delete();
                $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil diperbarui / di-override.')->send();
            } else {
                $this->record->update([
                    'bagian_id' => $targetBagianId,
                    'kode' => $this->kode,
                    'nilai' => $this->nilai,
                    'aktif' => $this->aktif,
                ]);
                $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil diperbarui.')->send();
            }

            $this->dispatch('jadwal-aturan-updated');
            $this->dispatch('close-modal', id: 'edit-jadwal-aturan');
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
            'bagianOptions' => array_merge(
                [['value' => '', 'label' => 'Aturan Umum RSBA (Semua Departemen)']],
                Bagian::select('id', 'nama')
                    ->orderBy('nama')
                    ->get()
                    ->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])
                    ->toArray()
            ),
            'kodeOptions' => $kodeOptions,
        ]);
    }
}
