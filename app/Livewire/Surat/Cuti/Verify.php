<?php

namespace App\Livewire\Surat\Cuti;

use Exception;
use Livewire\Component;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use App\Models\User;
use App\Models\Surat\SuratCutiApproval;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Verify extends Component
{
    use Interactions;

    public string $signature = '';

    public $surat;
    public array $dataSuratAsli = [];
    public bool $verify = false;
    public $bgColor = 'white';

    public function render()
    {
        return view('livewire.surat.cuti.verify');
    }

    public function updatedSignature()
    {
        $this->bgColor = 'white';
        $this->verify = false;

        $this->validate([
            'signature' => 'required|string',
        ]);

        $this->getSignature($this->signature);
        $this->signature = '';
    }

    private function getSignature(string $signature): void
    {
        $suratApproval = SuratCutiApproval::where('signature_hash', $signature)->latest('id')->first();

        if (!$suratApproval) {
            $this->toast()
                ->error('Invalid', 'Tidak ditemukan data.')
                ->send();
            return;
        }

        $this->surat = $suratApproval->cuti;

        // get user based on disetujui_oleh (which is karyawan_id)
        $user = User::where('karyawan_id', $suratApproval->disetujui_oleh)->first();
        if (!$user) {
            $this->toast()
                ->error('Invalid', 'User penandatangan tidak ditemukan.')
                ->send();
            return;
        }

        $certs = SignatureCerts::where('user_id', $user->id)->latest('id')->first();
        if (!$certs) {
            $this->toast()
                ->error('Invalid', 'Sertifikat tidak ditemukan.')
                ->send();
            return;
        }

        // Get the signed data from logs
        $log = SignatureLogs::where('data_hash', $signature)->first();
        if (!$log) {
            $this->toast()
                ->error('Invalid', 'Log tanda tangan tidak ditemukan.')
                ->send();
            return;
        }

        // Verify signature against original data in logs
        $this->verify = $this->verifySignature(
            data: $log->data,
            signature: $log->signature,
            publicKey: $certs->public_key
        );

        if ($this->verify) {
            $this->bgColor = 'green';
        } else {
            $this->bgColor = 'red';
        }

        $parsedData = json_decode($log->data, true);
        $this->dataSuratAsli = [
            [
                'status' => $parsedData['status'] ?? ($suratApproval->status?->value ?? 'Approved'),
                'disetujui' => $parsedData['user'] ?? ($suratApproval->karyawan?->nama ?? '-'),
                'keterangan' => $parsedData['ket_reject'] ?? ($suratApproval->keterangan ?? '-'),
                'approved_at' => $suratApproval->approved_at ?? $log->created_at,
            ]
        ];
    }

    private function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        $signatureBinary = base64_decode($signature);
        if ($signatureBinary === false) {
            throw new Exception("Invalid signature encoding");
        }

        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if ($publicKeyResource === false) {
            throw new Exception("Invalid public key");
        }

        $result = openssl_verify(
            $data,
            $signatureBinary,
            $publicKeyResource,
            OPENSSL_ALGO_SHA256
        );


        if ($result === 1) {
            return true;
        } elseif ($result === 0) {
            Log::error('Error verify Cuti data: ' . json_encode([
                'input_data' => $data,
                'public_key' => $publicKey,
                'signature' => $signature,
            ]));
            return false;
        } else {
            throw new Exception("Verification error: " . openssl_error_string());
        }
    }
}
