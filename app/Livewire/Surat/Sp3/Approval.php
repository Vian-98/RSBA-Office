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

    public string $status = '', $keterangan;
    public string $password;
    private ?int $disetujui;

    public function rules(): array
    {
        return [
            'password' => $this->status === 'manual' ? 'nullable' : 'required',
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
            'disetujui' => $this->disetujui,
            'status' => $this->status,
            'keterangan' => $this->keterangan ?? null,
            'approved_at' => now()->toIso8601String(),
        ];

        DB::beginTransaction();
        try {
            // ambil data signature user [p12 path] , jika manual, gunakan tanda tangan Super Admin,
            // Super Admin credential mewakili credential sistem,
            $user = $this->status === 'manual' ? User::find(1) : auth()->user();
            $certificate = $user->certificate()->latest('id')->first();

            if (!$certificate) {
                throw new Exception("Tidak memiliki certificate.");
            }

            // buat signature hash dari p12
            $dataToSign = json_encode($data);

            $passwordCertificate = $this->status === 'manual' ? null : $this->password;

            // Proses tanda tangan data
            $signature = $this->digitalSignatureService->signData(
                user: $user,
                data: $dataToSign,
                password: $passwordCertificate,
                type: 'persetujuan_sp3',
                id: $this->suratSp3->id
            );

            if (!$signature['status']) {
                $this->toast()
                    ->error('Proses tanda tangan tidak berhasil.', "<i>{$signature['message']}</i>")
                    ->send();
                return;
            }

            $data['signature_hash'] = $signature['data_hash']; //adding hash to data

            SuratSp3Approval::create($data);
            $finalStatus = $this->status === 'manual' ? 'approved' : $this->status;
            
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

            DB::commit();
            $this->dispatch('update-approval');

            $this->toast()
                ->success('Berhasil.', "Surat SP3 {$this->suratSp3->no} berhasil diupdate.")
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
