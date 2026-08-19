<?php

namespace App\Livewire\Master\Bagian;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalAturan;
use App\Enums\KodeAturanJadwal;
use TallStackUi\Traits\Interactions;

class BagianAturanModal extends Component
{
    use Interactions;

    public bool $showModal = false;
    public ?int $bagianId = null;
    public ?Bagian $bagian = null;

    public string $kode = 'MAKSIMAL_HARI_KERJA_BERTURUT';
    public string $nilai = '6';
    public bool $aktif = true;
    public ?int $editingAturanId = null;

    protected $listeners = ['open-modal-aturan-bagian' => 'loadBagian'];

    public function rules(): array
    {
        return [
            'kode' => 'required|string',
            'nilai' => 'required|string',
            'aktif' => 'boolean',
        ];
    }

    #[On('open-modal-aturan-bagian')]
    public function loadBagian(int $bagianId): void
    {
        $this->bagianId = $bagianId;
        $this->bagian = Bagian::find($bagianId);
        $this->resetForm();
        $this->showModal = true;
    }

    public function saveAturan(): void
    {
        if (!auth()->user()?->can('manage-kepegawaian-master-aturan')) {
            $this->toast()->error('Akses Ditolak', 'Penambahan / perubahan aturan khusus hanya dapat dilakukan oleh Tim SDM.')->send();
            return;
        }

        $this->validate();

        if (!$this->bagianId) {
            return;
        }

        try {
            // Cek apakah aturan dengan (bagian_id, kode) tersebut sudah ada
            $existing = JadwalAturan::where('bagian_id', $this->bagianId)
                ->where('kode', $this->kode)
                ->first();

            if ($existing) {
                // Jika mengedit baris lain lalu diubah ke kode yang sudah ada, hapus baris asal agar tidak membuat duplikat
                if ($this->editingAturanId && $this->editingAturanId !== $existing->id) {
                    JadwalAturan::destroy($this->editingAturanId);
                }

                $existing->update([
                    'nilai' => $this->nilai,
                    'aktif' => $this->aktif,
                ]);

                $this->toast()->success('Berhasil', 'Aturan khusus departemen berhasil diperbarui / di-override.')->send();
            } else {
                if ($this->editingAturanId) {
                    $aturan = JadwalAturan::find($this->editingAturanId);
                    if ($aturan) {
                        $aturan->update([
                            'kode' => $this->kode,
                            'nilai' => $this->nilai,
                            'aktif' => $this->aktif,
                        ]);
                    }
                } else {
                    JadwalAturan::create([
                        'bagian_id' => $this->bagianId,
                        'kode' => $this->kode,
                        'nilai' => $this->nilai,
                        'aktif' => $this->aktif,
                    ]);
                }
                $this->toast()->success('Berhasil', 'Aturan khusus departemen berhasil ditambahkan.')->send();
            }

            $this->resetForm();
            $this->dispatch('jadwal-aturan-updated');
        } catch (Throwable $th) {
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $th->getMessage())->send();
        }
    }

    public function editAturan(int $id): void
    {
        $aturan = JadwalAturan::find($id);
        if ($aturan) {
            $this->editingAturanId = $aturan->id;
            $this->kode = $aturan->kode;
            $this->nilai = (string) $aturan->nilai;
            $this->aktif = (bool) $aturan->aktif;
        }
    }

    public function deleteAturan(int $id): void
    {
        if (!auth()->user()?->can('manage-kepegawaian-master-aturan')) {
            $this->toast()->error('Akses Ditolak', 'Penghapusan aturan khusus hanya dapat dilakukan oleh Tim SDM.')->send();
            return;
        }

        JadwalAturan::destroy($id);
        $this->toast()->warning('Dihapus', 'Aturan khusus departemen telah dihapus.')->send();
        $this->dispatch('jadwal-aturan-updated');
    }

    public function resetForm(): void
    {
        $this->editingAturanId = null;
        $this->kode = 'MAKSIMAL_HARI_KERJA_BERTURUT';
        $this->nilai = '6';
        $this->aktif = true;
    }

    public function render()
    {
        $aturanList = $this->bagianId 
            ? JadwalAturan::where('bagian_id', $this->bagianId)->get() 
            : collect();

        $optionsKode = array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->nama(),
        ], KodeAturanJadwal::cases());

        return view('livewire.master.bagian.bagian-aturan-modal', [
            'aturanList' => $aturanList,
            'optionsKode' => $optionsKode,
        ]);
    }
}
