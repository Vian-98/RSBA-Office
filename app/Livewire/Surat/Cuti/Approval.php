<?php

namespace App\Livewire\Surat\Cuti;

use Exception;
use Throwable;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Approval extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratCuti $suratCuti;

    public ?string $status = '';
    public ?string $keterangan = null;
    public ?string $password = null;

    public $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui', 'color' => 'indigo'],
        ['value' => 'pending', 'label' => 'Tunda', 'color' => 'orange'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'red'],
    ];

    protected ?DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService): void
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount(?SuratCuti $suratCuti)
    {
        $this->suratCuti = $suratCuti;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'keterangan' => $this->status === 'rejected' ? 'required|string' : 'nullable|string'
        ];
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $dataSign = [
                'title' => "Approval Cuti {$this->suratCuti->id}",
                'status' => $this->status,
                'ket_reject' => $this->keterangan,
                'user' => $user->karyawan?->nama ?? $user->name,
            ];

            // Tanda tangan individual per-orang (dengan fallback password otomatis jika tidak dimasukkan)
            $signature = $this->digitalSignatureService->signData(
                user: $user,
                data: json_encode($dataSign),
                password: $this->password ?: 'password123',
                type: 'surat_cuti_approval',
                id: $this->suratCuti->id
            );

            $dataUpdate = [
                'status' => $this->status,
                'keterangan' => $this->keterangan ?? null,
                'signature_hash' => $signature['data_hash'] ?? md5(microtime()),
                'approved_at' => now()->toIso8601String()
            ];

            // Simpan data approval ke database
            $queryApprovals = SuratCutiApproval::where('surat_cuti_id', $this->suratCuti->id);
            $hasApproval = (clone $queryApprovals)
                ->where('disetujui_oleh', $user->karyawan_id)
                ->exists();

            if (!$hasApproval) {
                return throw new Exception('Surat cuti ini tidak ditujukan untuk anda setujui');
            }

            // update approval status orang tersebut
            (clone $queryApprovals)
                ->where('disetujui_oleh', $user->karyawan_id)
                ->update($dataUpdate);

            $approvals = (clone $queryApprovals)->get();

            $status = match (true) {
                $approvals->contains(fn($a) => $a->status->value === 'rejected') => 'rejected',
                $approvals->every(fn($a) => $a->status->value === 'approved') => 'approved',
                default => null
            };

            if ($status) {
                $this->suratCuti->update([
                    'status' => $status,
                    'updated_by' => $user->id
                ]);

                if ($status === 'rejected') {
                    $this->suratCuti->karyawan?->decrement('cuti', $this->suratCuti->lama_cuti);
                }
            }

            // Memicu purna-approval & penerbitan QR Header berbasis Sistem PKCS#12 (.p12) jika Full ACC
            app(\App\Services\DocumentSignatureService::class)->checkAndGenerateHeaderQr($this->suratCuti->fresh());

            DB::commit();

            $this->dispatch('surat-cuti-approved');

            $this->toast()
                ->success('Berhasil ACC', 'Persetujuan berhasil disimpan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.cuti.approval');
    }
}
