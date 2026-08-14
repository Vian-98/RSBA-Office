<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratBalasanPenelitian;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class Approval extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratBalasanPenelitian $suratBalasanPenelitian = null;

    public string $status = 'approved';
    public string $catatan = '';

    public array $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui', 'color' => 'emerald'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'rose'],
    ];

    public function mount(?SuratBalasanPenelitian $suratBalasanPenelitian)
    {
        $this->suratBalasanPenelitian = $suratBalasanPenelitian;
    }

    public function submit()
    {
        if ($this->status === 'rejected' && empty(trim($this->catatan))) {
            $this->toast()->error('Catatan Wajib Diisi', 'Silakan berikan alasan penolakan surat.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            $newStatus = $this->status === 'approved' ? StatusApproval::APPROVED : StatusApproval::REJECTED;

            $this->suratBalasanPenelitian->update([
                'status'           => $newStatus,
                'catatan_approval' => $this->catatan,
                'signed_at'        => $this->status === 'approved' ? now() : null,
                'disetujui_oleh'   => auth()->user()?->karyawan_id ?? $this->suratBalasanPenelitian->disetujui_oleh,
            ]);

            DB::commit();

            $this->dispatch('refresh-table-balasan-penelitian');
            $this->dispatch('close-modal', id: 'modal-approval-balasan-penelitian');

            $statusText = $this->status === 'approved' ? 'disetujui' : 'ditolak';
            $this->toast()->success('Berhasil', "Surat Balasan Penelitian berhasil {$statusText}.")->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.approval');
    }
}
