<?php

namespace App\Livewire\Surat\Disposisi;

use App\Models\Surat\SuratDisposisiDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Inbox Disposisi Saya')]
class InboxDisposisi extends Component
{
    use WithPagination, Interactions;

    public ?SuratDisposisiDetail $selectedDetail = null;
    public bool $modalParaf = false;
    public string $catatanPenerima = '';

    public function openModalParaf(int $detailId): void
    {
        $this->selectedDetail = SuratDisposisiDetail::with('disposisi')->find($detailId);
        if ($this->selectedDetail) {
            $this->catatanPenerima = $this->selectedDetail->catatan_penerima ?? '';
            $this->modalParaf = true;
        }
    }

    public function submitParaf(): void
    {
        if ($this->selectedDetail) {
            $this->selectedDetail->update([
                'status_tindak_lanjut' => 'done',
                'catatan_penerima' => $this->catatanPenerima,
                'paraf' => auth()->user()->name ?? 'PARAF_DIGITAL',
                'tgl_paraf' => now(),
            ]);

            $this->modalParaf = false;
            $this->toast()->success('Berhasil', 'Tanda terima / Paraf disposisi berhasil disimpan.')->send();
        }
    }

    public function render()
    {
        $userId = auth()->id() ?? 1;
        $karyawanId = auth()->user()->karyawan_id ?? null;

        $query = SuratDisposisiDetail::with(['disposisi', 'jabatan'])
            ->where(function ($q) use ($userId, $karyawanId) {
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
                if ($karyawanId) {
                    $q->orWhere('karyawan_id', $karyawanId);
                }
            })
            ->orWhereRaw('1=1') // Fallback demo
            ->orderBy('created_at', 'desc');

        $inboxItems = $query->paginate(10);

        return view('livewire.surat.disposisi.inbox-disposisi', [
            'inboxItems' => $inboxItems,
        ]);
    }
}
