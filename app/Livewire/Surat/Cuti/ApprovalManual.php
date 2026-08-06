<?php

namespace App\Livewire\Surat\Cuti;

use Exception;
use Throwable;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Models\Sdm\Karyawan;
use App\Services\DigitalSignatureService;
use App\Services\SystemCertificateService;
use App\Services\DocumentSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class ApprovalManual extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratCuti $suratCuti;

    public ?int $mengetahui = null, $menyetujui = null;

    public function rules(): array
    {
        return [
            'menyetujui' => 'required'
        ];
    }

    protected DigitalSignatureService $digital_signature_service;

    public function boot(DigitalSignatureService $digital_signature_service)
    {
        $this->digital_signature_service = $digital_signature_service;
    }

    public function mount(?SuratCuti $suratCuti)
    {
        $this->suratCuti = $suratCuti;
    }

    #[Computed]
    public function approvalOptions(): array
    {
        $approvals = $this->suratCuti ? $this->suratCuti->approvals : collect();
        if ($approvals->isEmpty()) {
            return Karyawan::whereNull('resign')->get()->map(
                fn($k) => [
                    'value' => $k->id,
                    'nama' => $k->nama,
                ]
            )->toArray();
        }

        return $approvals->map(
            fn($approval) => [
                'value' => $approval->karyawan->id,
                'nama' => $approval->karyawan->nama,
            ]
        )->toArray();
    }

    public function printManual(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Menggunakan System Certificate User dengan password yang terkonfigurasi
            $systemCertService = app(SystemCertificateService::class);
            $user = $systemCertService->getOrCreateSystemUser();

            // Get data Approval
            $queryApprovals = SuratCutiApproval::where('surat_cuti_id', $this->suratCuti->id);
            $approvers = array_filter([$this->mengetahui, $this->menyetujui]);

            if (empty($approvers)) return;

            foreach ($approvers as $approver) {
                $dataSign = [
                    'title' => "Approval Cuti {$this->suratCuti->id}",
                    'status' => 'Approved Manual',
                    'ket_reject' => null,
                    'user' => auth()->user()->karyawan?->nama ?? auth()->user()->name,
                    'approver' => $approver
                ];

                $signature = $this->digital_signature_service->signData(
                    user: $user,
                    data: json_encode($dataSign),
                    password: 'password123',
                    type: 'surat_cuti_approval',
                    id: $this->suratCuti->id
                );

                if (!$signature['status']) {
                    throw new Exception("Proses tanda tangan tidak berhasil: " . $signature['message']);
                }

                // prepare data update untuk persetujuan manual
                $dataUpdate = [
                    'status' => \App\Enums\StatusApproval::MANUAL->value ?? 'manual',
                    'keterangan' => 'Approval cuti manual',
                    'signature_hash' => $signature['data_hash'],
                    'approved_at' => now()->toIso8601String()
                ];

                $hasApproval = (clone $queryApprovals)
                    ->where('disetujui_oleh', $approver)
                    ->exists();

                if (!$hasApproval) {
                    SuratCutiApproval::create([
                        'surat_cuti_id' => $this->suratCuti->id,
                        'disetujui_oleh' => $approver,
                        'status' => 'waiting',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                (clone $queryApprovals)
                    ->where('disetujui_oleh', $approver)
                    ->update($dataUpdate);

                $this->suratCuti->update([
                    'status' => \App\Enums\StatusApproval::MANUAL->value ?? 'manual',
                    'updated_by' => auth()->user()->id
                ]);
            }

            // Memicu penerbitan QR Header Sistem
            app(DocumentSignatureService::class)->checkAndGenerateHeaderQr($this->suratCuti);

            // Sync immediately to docstore jika ada
            if (class_exists(\App\Services\DocstoreSyncService::class)) {
                try {
                    app(\App\Services\DocstoreSyncService::class)->syncCuti($this->suratCuti);
                } catch (Throwable $th) {
                    // ignore
                }
            }

            DB::commit();

            $this->dispatch('surat-cuti-manual-approved');

            $this->toast()
                ->success('Berhasil Disimpan')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.cuti.approval-manual');
    }
}
