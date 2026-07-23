<?php

namespace App\Livewire\Surat\Sp3;

use Exception;
use Throwable;
use App\Models\Sdm\Jabatan;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Approval;
use App\Models\User;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Approval extends Component
{
    use Interactions;

    public ?SuratSp3 $suratSp3;

    public $optionsApproval = [
        ['value' => 'approved', 'label' => 'Setujui', 'color' => 'green'],
        ['value' => 'rejected', 'label' => 'Tolak', 'color' => 'red'],
        ['value' => 'manual', 'label' => 'Persetujuan Manual', 'color' => 'primary']
    ];

    public string $status = '';
    public ?string $keterangan = null;
    public ?string $password = null;
    private ?int $disetujui;

    public function rules(): array
    {
        return [
            'password' => 'nullable',
            'status' => 'required|string',
            'keterangan' => $this->status === 'rejected' ? 'required|string' : 'nullable|string'
        ];
    }

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount($suratSp3)
    {
        $this->suratSp3 = $suratSp3;
    }

    public function updatedStatus($value)
    {
        if ($value == 'manual') {
            $jabatan = Jabatan::find($this->suratSp3->jabatan_id);

            if ($jabatan) {
                $karyawanJabatan = $jabatan->jabatans()
                    ->where('tgl_berakhir', null)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($karyawanJabatan) {
                    // return
                    $this->disetujui = $karyawanJabatan->karyawan->user->id;
                } else {
                    $this->toast()
                        ->error('Pejabat tidak ditemukan.', "Tidak ada karyawan dengan jabatan <b>{$jabatan->nama}</b>.")
                        ->send();
                }
            }
        } else {
            $this->disetujui =  auth()->user()->id;
        }
    }

    public function submit()
    {
        $this->validate();

        $data = [
            'surat_sp3_id' => $this->suratSp3->id,
            'disetujui' => $this->disetujui ?? auth()->user()->id,
            'status' => $this->status,
            'keterangan' => $this->keterangan ?? null,
            'approved_at' => now()->toIso8601String(),
        ];

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $dataToSign = json_encode($data);

            $signature = $this->digitalSignatureService->signData(
                user: $user,
                data: $dataToSign,
                password: $this->password ?: 'password123',
                type: 'persetujuan_sp3',
                id: $this->suratSp3->id
            );

            $data['signature_hash'] = $signature['data_hash'] ?? md5(microtime());

            SuratSp3Approval::create($data);
            $finalStatus = in_array($this->status, ['approved', 'disetujui']) ? 'approved' : $this->status;
            
            $this->suratSp3->update([
                'status' => $finalStatus
            ]);

            // Update status pembayaran PO jika SP3 disetujui
            if (in_array($finalStatus, ['approved', 'disetujui'])) {
                \App\Models\Gudang\Pembelian::where('sp3_id', $this->suratSp3->id)
                    ->update([
                        'status_pembayaran' => 'lunas',
                        'tgl_pembayaran' => now()->format('Y-m-d')
                    ]);
            }

            // Memicu penerbitan System PKCS#12 QR Header jika Full ACC
            app(\App\Services\DocumentSignatureService::class)->checkAndGenerateHeaderQr($this->suratSp3->fresh());

            DB::commit();
            $this->dispatch('update-approval');

            $this->toast()
                ->success('Berhasil ACC', "Surat SP3 {$this->suratSp3->no} berhasil di-ACC.")
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Tidak Berhasil.', "Error : {$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.sp3.approval');
    }
}
