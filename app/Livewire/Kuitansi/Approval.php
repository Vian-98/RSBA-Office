<?php

namespace App\Livewire\Kuitansi;

use App\Enums\StatusApproval;
use App\Models\Keuangan\Kuitansi;
use App\Models\Keuangan\KuitansiApproval;
use App\Services\DigitalSignatureService;
use App\Services\DocumentSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Lazy]
class Approval extends Component
{
    use Interactions;

    #[Locked]
    public ?Kuitansi $kuitansi = null;

    public $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui (Digital)', 'color' => 'green'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'red'],
        ['value' => 'manual', 'label' => 'Persetujuan Manual (TTD Basah)', 'color' => 'primary'],
    ];

    public string $status = '';
    public ?string $keterangan = null;
    public ?string $password = null;

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount(?Kuitansi $kuitansi = null)
    {
        $this->kuitansi = $kuitansi;
    }

    #[On('buka-modal-kuitansi')]
    public function setKuitansi(int $id, string $modal): void
    {
        if ($modal === 'modal-approval-kuitansi') {
            $this->kuitansi = Kuitansi::with(['approvals.disetujuiOleh', 'createdBy.karyawan', 'details'])->find($id);
            $this->reset(['status', 'keterangan', 'password']);
        }
    }

    public function rules(): array
    {
        return [
            'status'     => 'required|in:approved,rejected,manual',
            'keterangan' => $this->status === 'rejected' ? 'required|string|max:500' : 'nullable|string|max:500',
            'password'   => 'nullable|string',
        ];
    }

    public function submit()
    {
        $this->validate();

        if (!$this->kuitansi) {
            $this->toast()->error('Error', 'Data kuitansi tidak ditemukan.')->send();
            return;
        }

        $user = auth()->user();
        $userKaryawanId = $user->karyawan_id;

        // Find approval record for this user or the first waiting approval if Super-Admin
        $approval = $this->kuitansi->approvals()
            ->where(function ($q) use ($userKaryawanId, $user) {
                if ($userKaryawanId) {
                    $q->where('disetujui_oleh', $userKaryawanId);
                }
                if ($user->hasRole('Super-Admin')) {
                    $q->orWhere('status', 'waiting');
                }
            })
            ->first();

        if (!$approval) {
            $this->toast()->error('Tidak Berhak', 'Anda tidak memiliki hak persetujuan pada kuitansi ini.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            $signatureHash = null;

            if ($this->status === 'approved') {
                $payloadData = [
                    'kuitansi_id'   => $this->kuitansi->id,
                    'nomor'         => $this->kuitansi->nomor,
                    'approver_id'   => $approval->disetujui_oleh,
                    'status'        => 'approved',
                    'timestamp'     => now()->toIso8601String(),
                ];

                $signResult = $this->digitalSignatureService->signData(
                    user: $user,
                    data: json_encode($payloadData),
                    password: $this->password ?: 'password123',
                    type: 'kuitansi_approval',
                    id: $this->kuitansi->id
                );

                $signatureHash = $signResult['data_hash'] ?? md5(microtime());
            } elseif ($this->status === 'manual') {
                $signatureHash = 'MANUAL_' . md5($this->kuitansi->id . '_' . now());
            } else {
                $signatureHash = 'REJECTED_' . md5($this->kuitansi->id . '_' . now());
            }


            $approval->update([
                'status'         => $this->status,
                'keterangan'     => $this->keterangan,
                'signature_hash' => $signatureHash,
                'approved_at'    => now()->toIso8601String(),
            ]);

            if ($this->status === 'rejected') {
                $this->kuitansi->update([
                    'status' => \App\Enums\StatusKuitansi::REJECTED,
                ]);
            }

            // Sync docstore and check if full approval is complete
            app(DocumentSignatureService::class)->triggerDocstoreSync($this->kuitansi->fresh());
            app(DocumentSignatureService::class)->checkAndGenerateHeaderQr($this->kuitansi->fresh());

            DB::commit();

            $this->dispatch('update-approval-kuitansi');
            $this->dispatch('close-modal', id: 'modal-approval-kuitansi');

            $this->toast()
                ->success('Berhasil', "Persetujuan kuitansi {$this->kuitansi->nomor} telah diproses.")
                ->send();

            $this->reset(['status', 'keterangan', 'password']);
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Gagal Memproses', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.kuitansi.approval');
    }
}
