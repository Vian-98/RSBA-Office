<?php

namespace App\Livewire\Surat\Sp3;

use App\Enums\StatusApproval;
use App\Enums\TahapApprovalSp3;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Approval;
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
class VerifikasiKeuangan extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratSp3 $suratSp3 = null;

    public $optionsVerifikasi = [
        ['value' => 'approved', 'label' => 'Setujui Verifikasi Keuangan', 'color' => 'green'],
        ['value' => 'rejected', 'label' => 'Tolak (Kembalikan ke Pembuat)', 'color' => 'red'],
    ];

    public string $status = '';
    public ?string $keterangan = null;
    public ?string $password = null;

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount(?SuratSp3 $suratSp3 = null)
    {
        $this->suratSp3 = $suratSp3;
    }

    #[On('buka-verifikasi-keuangan-sp3')]
    public function setSuratSp3(int $id): void
    {
        $this->suratSp3 = SuratSp3::with(['details', 'createdBy.karyawan', 'jabatans'])->find($id);
        $this->reset(['status', 'keterangan', 'password']);
    }

    public function rules(): array
    {
        return [
            'status'     => 'required|in:approved,rejected',
            'keterangan' => $this->status === 'rejected' ? 'required|string|max:500' : 'nullable|string|max:500',
            'password'   => 'nullable|string',
        ];
    }

    public function submit()
    {
        $this->validate();

        if (!$this->suratSp3) {
            $this->toast()->error('Error', 'Data Surat SP3 tidak ditemukan.')->send();
            return;
        }

        $user = auth()->user();

        // Validasi: User haruslah verifikator keuangan yang dipilih atau memiliki role Keuangan/Super-Admin
        $isAssignedVerifier = $this->suratSp3->verifikator_keuangan_id && $this->suratSp3->verifikator_keuangan_id == $user->karyawan_id;
        $isKeuanganAuthorized = $user->hasRole(['Super-Admin', 'Wadir-Keuangan', 'Keuangan']) || $user->isKabagKeuangan();

        if (!$isAssignedVerifier && !$isKeuanganAuthorized) {
            $this->toast()->error('Tidak Berhak', 'Anda tidak memiliki hak untuk melakukan verifikasi keuangan pada SP3 ini.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            $signatureHash = null;

            if ($this->status === 'approved') {
                $payloadData = [
                    'surat_sp3_id' => $this->suratSp3->id,
                    'disetujui'    => $user->id,
                    'tahap'        => TahapApprovalSp3::VERIFIKASI_KEUANGAN->value,
                    'status'       => 'approved',
                    'timestamp'    => now()->toIso8601String(),
                ];

                $signResult = $this->digitalSignatureService->signData(
                    user: $user,
                    data: json_encode($payloadData),
                    password: $this->password ?: 'password123',
                    type: 'verifikasi_keuangan_sp3',
                    id: $this->suratSp3->id
                );

                $signatureHash = $signResult['data_hash'] ?? md5(microtime());
            } elseif ($this->status === 'manual') {
                $signatureHash = 'MANUAL_' . md5($this->suratSp3->id . '_' . now());
            } else {
                $signatureHash = 'REJECTED_' . md5($this->suratSp3->id . '_' . now());
            }


            // Create or update approval row for verifikasi_keuangan
            SuratSp3Approval::updateOrCreate(
                [
                    'surat_sp3_id' => $this->suratSp3->id,
                    'tahap'        => TahapApprovalSp3::VERIFIKASI_KEUANGAN->value,
                ],
                [
                    'disetujui'      => $user->id,
                    'status'         => $this->status,
                    'keterangan'     => $this->keterangan,
                    'signature_hash' => $signatureHash,
                    'approved_at'    => now()->toIso8601String(),
                ]
            );

            // Update surat_sp3 status
            $newStatus = ($this->status === 'approved') ? StatusApproval::WAITING : StatusApproval::REJECTED;
            $this->suratSp3->update([
                'status' => $newStatus,
            ]);

            // Log history verifikasi keuangan
            $isApproved = $this->status === 'approved';
            \App\Models\Surat\SuratSp3Log::create([
                'surat_sp3_id'   => $this->suratSp3->id,
                'user_id'        => $user->id,
                'karyawan_id'    => $user->karyawan_id,
                'nama_pelaku'    => $user->karyawan?->full_nama ?? $user->name,
                'jabatan_pelaku' => optional($user->karyawan?->jabatan?->first())->nama ?? 'Verifikator Keuangan',
                'aksi'           => $isApproved ? 'Verifikasi Keuangan - Disetujui' : 'Verifikasi Keuangan - Ditolak',
                'status'         => $this->status,
                'catatan'        => $this->keterangan ?: ($isApproved ? 'Diverifikasi dan diteruskan ke Direktur / Atasan untuk ACC.' : 'Ditolak oleh Verifikator Keuangan.'),
                'signature_hash' => $signatureHash,
            ]);

            // Sync docstore (without finalizing yet)
            app(DocumentSignatureService::class)->triggerDocstoreSync($this->suratSp3->fresh());


            DB::commit();

            $this->dispatch('update-approval');
            $this->dispatch('close-modal', id: 'modal-verifikasi-keuangan-sp3');

            $this->toast()
                ->success('Berhasil', "Verifikasi Keuangan Surat SP3 {$this->suratSp3->no} berhasil diproses.")
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
        return view('livewire.surat.sp3.verifikasi-keuangan');
    }
}
