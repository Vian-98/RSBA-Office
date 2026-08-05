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
        'bagian_id' => 'nullable|exists:bagian,id',
        'kode' => 'required|string',
        'nilai' => 'required|string',
        'aktif' => 'boolean'
    ];

    public function submit()
    {
        abort_unless(
            auth()->user()?->can('add-kepegawaian-master-jadwal-aturan'),
            403,
            'Tidak memiliki akses untuk menambah Aturan Jadwal.'
        );

        $this->validate();
        $targetBagianId = $this->bagian_id ?: null;

        try {
            $existing = JadwalAturan::where('bagian_id', $targetBagianId)
                ->where('kode', $this->kode)
                ->first();

            if ($existing) {
                $existing->update([
                    'nilai' => $this->nilai,
                    'aktif' => $this->aktif,
                ]);
                $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil diperbarui / di-override.')->send();
            } else {
                JadwalAturan::create([
                    'bagian_id' => $targetBagianId,
                    'kode' => $this->kode,
                    'nilai' => $this->nilai,
                    'aktif' => $this->aktif,
                ]);
                $this->toast()->success('Berhasil', 'Aturan Jadwal berhasil ditambahkan.')->send();
            }

            $this->dispatch('new-jadwal-aturan-created');
            $this->dispatch('close-modal', id: 'new-jadwal-aturan');

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

        $bagianOptions = Bagian::select('id', 'nama')
            ->orderBy('nama')
            ->get()
            ->map(fn($item) => ['value' => $item->id, 'label' => $item->nama])
            ->toArray();
        array_unshift($bagianOptions, ['value' => '', 'label' => 'Aturan Umum RSBA (Semua Departemen)']);

        return view('livewire.master.jadwal-aturan.add', [
            'bagianOptions' => $bagianOptions,
            'kodeOptions' => $kodeOptions,
        ]);
    }
}
