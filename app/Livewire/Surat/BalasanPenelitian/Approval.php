<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Services\DigitalSignatureService;
use App\Services\DocumentSignatureService;
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
    public ?string $password = null;

    public array $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui', 'color' => 'emerald'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'rose'],
    ];

    protected ?DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService): void
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

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
            $user = auth()->user();

            $sigHash = null;
            if ($this->status === 'approved') {
                $signResult = $this->digitalSignatureService->signData(
                    user: $user,
                    data: json_encode([
                        'title'      => "Persetujuan Surat Balasan Penelitian {$this->suratBalasanPenelitian->no}",
                        'surat_type' => 'surat_balasan_penelitian',
                        'surat_id'   => $this->suratBalasanPenelitian->id,
                        'no'         => $this->suratBalasanPenelitian->no,
                        'status'     => 'approved',
                        'approved_by'=> $user->karyawan?->full_nama ?? $user->name,
                        'timestamp'  => now()->toIso8601String(),
                    ]),
                    password: $this->password ?: 'password123',
                    type: 'surat_balasan_penelitian',
                    id: $this->suratBalasanPenelitian->id
                );

                $sigHash = $signResult['data_hash'] ?? md5('penelitian_' . $this->suratBalasanPenelitian->id . '_' . time());
            }

            $this->suratBalasanPenelitian->update([
                'status'               => $newStatus,
                'catatan_approval'     => $this->catatan,
                'signed_at'            => $this->status === 'approved' ? now() : null,
                'disetujui_oleh'       => $user?->karyawan_id ?? $this->suratBalasanPenelitian->disetujui_oleh,
                'qr_verification_hash' => $sigHash,
            ]);

            // Sync ke docstore (bank surat & source of truth)
            app(DocumentSignatureService::class)->triggerDocstoreSync($this->suratBalasanPenelitian->fresh());

            DB::commit();

            $this->dispatch('refresh-table-balasan-penelitian');
            $this->dispatch('close-modal', id: 'modal-approval-balasan-penelitian');

            $statusText = $this->status === 'approved' ? 'disetujui dan disinkronkan ke Bank Surat' : 'ditolak';
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
