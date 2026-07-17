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
    public $keterangan;
    public $password;

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
            $dataSign = [
                'title' => "Approval Cuti {$this->suratCuti->id}",
                'status' => $this->status,
                'ket_reject' => $this->keterangan,
                'user' => auth()->user()->karyawan->nama,
            ];

            $signature = $this->digitalSignatureService->signData(
                user: auth()->user(),
                data: json_encode($dataSign),
                password: $this->password,
                type: 'surat_cuti_approval',
                id: $this->suratCuti->id
            );

            if (!$signature['status']) {
                $this->toast()
                    ->error('Proses tanda tangan tidak berhasil.', "<i>{$signature['message']}</i>")
                    ->send();

                return;
            }

            $dataUpdate = [
                'status' => $this->status,
                'keterangan' => $this->keterangan ?? null,
                'signature_hash' => $signature['data_hash'],
                'approved_at' => now()->toIso8601String()
            ];
            // Simpan data approval ke database
            $queryApprovals = SuratCutiApproval::where('surat_cuti_id', $this->suratCuti->id);
            $hasApproval =  (clone $queryApprovals)
                ->where('disetujui_oleh', auth()->user()->karyawan_id)
                ->exists();

            if (!$hasApproval) {
                return throw new Exception('Surat cuti ini tidak ditujukan untuk anda setujui');
            }

            // update
            (clone $queryApprovals)
                ->where('disetujui_oleh', auth()->user()->karyawan_id)
                ->update($dataUpdate);

            $approvals = (clone $queryApprovals)->get();

            $status = match (true) {
                // Kondisi 1: Jika ada yang DITOLAK -> rejected
                $approvals->contains(fn($a) => $a->status->value === 'rejected') => 'rejected',

                // Kondisi 2: Jika SEMUA approval sudah APPROVED
                // -> suratCuti = DISETUJUI
                $approvals->every(fn($a) => $a->status->value === 'approved') => 'approved',

                // Kondisi 3 & 4: Jika masih ada yang WAITING atau PENDING
                // -> belum ada keputusan, return null (tidak update)
                $approvals->contains(fn($a) => $a->status->value === 'waiting')  => null,
                $approvals->contains(fn($a) => $a->status->value === 'pending')  => null,

                // Default: kondisi lain yang tidak terdefinisi
                default => null
            };

            if ($status) {
                $this->suratCuti->update(
                    [
                        'status' =>  $status,
                        'updated_by' => auth()->user()->id
                    ]
                );

                if ($status === 'rejected') {
                    // kembalikan cuti
                    $this->suratCuti->karyawan->decrement('cuti', $this->suratCuti->lama_cuti);
                }
            }

            // Sync immediately to docstore
            app(\App\Services\DocstoreSyncService::class)->syncCuti($this->suratCuti);

            DB::commit();

            $this->dispatch('surat-cuti-approved');

            $this->toast()
                ->success('Berhasil')
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
