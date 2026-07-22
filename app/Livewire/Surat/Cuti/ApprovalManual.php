<?php

namespace App\Livewire\Surat\Cuti;

use Exception;
use Throwable;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Models\User;
use App\Models\Sdm\Karyawan;
use App\Services\DigitalSignatureService;
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

            // ambil data signature user [p12 path] , jika manual, gunakan tanda tangan Super Admin,
            // Super Admin credential mewakili credential sistem,
            $user = User::find(1) ?? auth()->user();
            $certificate = $user->certificate()->latest('id')->first();

            if (!$certificate) {
                throw new Exception("Tidak memiliki certificate.");
            }

            // Get data Approval
            $queryApprovals = SuratCutiApproval::where('surat_cuti_id', $this->suratCuti->id);

            $approvers = array_filter([$this->mengetahui, $this->menyetujui]);

            if (empty($approvers)) return;

            foreach ($approvers as $approver) {
                $dataSign = [
                    'title' => "Approval Cuti {$this->suratCuti->id}",
                    'status' => 'Approved Manual',
                    'ket_reject' => null,
                    'user' => auth()->user()->karyawan->nama,
                    'approver' => $approver
                ];

                $signature = $this->digital_signature_service->signData(
                    user: $user,
                    data: json_encode($dataSign),
                    password: null,
                    type: 'surat_cuti_approval',
                    id: $this->suratCuti->id
                );


                if (!$signature['status']) {
                    throw new Exception("Proses tanda tangan tidak berhasil: " . $signature['message']);
                }

                // prepare data update
                $dataUpdate = [
                    'status' => 'approved',
                    'keterangan' => 'Aproval cuti dengan manual',
                    'signature_hash' => $signature['data_hash'],
                    'approved_at' => now()->toIso8601String()
                ];

                // If approval record does not exist in DB, create it first
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

                // update approvals cuti
                (clone $queryApprovals)
                    ->where('disetujui_oleh', $approver)
                    ->update($dataUpdate);

                // selesaikan status cuti
                $this->suratCuti->update([
                    'status' =>  'approved',
                    'updated_by' => auth()->user()->id
                ]);
            }

            // Sync immediately to docstore
            app(\App\Services\DocstoreSyncService::class)->syncCuti($this->suratCuti);

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
