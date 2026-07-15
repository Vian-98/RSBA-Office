<?php

namespace App\Livewire\Surat\Sp3;

use Exception;
use Livewire\Component;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use TallStackUi\Traits\Interactions;
use App\Models\Surat\SuratSp3Approval;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;

#[Lazy]
class Verify extends Component
{
    use Interactions;

    public string $signature;

    public $surat;
    public array $dataSuratAsli;
    public bool $verify;
    public $bgColor = 'white';

    public function render()
    {
        return view('livewire.surat.sp3.verify');
    }

    public function updatedSignature()
    {
        $this->bgColor = 'white';
        $this->verify = false;

        // Validate the signature
        $this->validate([
            'signature' => 'required|string',
        ]);


        $signature = $this->signature;
        $this->getSignature(signature: $signature);

        // reset input
        $this->signature = '';
    }

    private function getSignature(string $signature): void
    {
        $suratApproval = SuratSp3Approval::where('signature_hash', $signature)->latest('id')->first();



        if (!$suratApproval) {
            $this->toast()
                ->error('Invalid', 'Tidak ditemukan data.')
                ->send();
            return;
        }
        $this->surat = $suratApproval->surat;

        // get log from SignatureLogs
        $log = SignatureLogs::where('data_hash', $signature)->first();
        if (!$log) {
            $this->toast()
                ->error('Invalid', 'Log tanda tangan tidak ditemukan.')
                ->send();
            return;
        }

        // get p12 based user approval
        $certs = SignatureCerts::where('user_id', $suratApproval->disetujui)->latest('id')->first();
        if (!$certs) {
            $this->toast()
                ->error('Invalid', 'Sertifikat tidak ditemukan.')
                ->send();
            return;
        }

        // verify signature against the original signed data stored in log
        $this->verify = $this->verifySignature(
            data: $log->data,
            signature: $log->signature,
            publicKey: $certs->public_key
        );

        //return data
        if ($this->verify) {
            $this->bgColor = 'green';
        } else {
            $this->bgColor = 'red';
        }
        // data surat yanga asli
        $this->dataSuratAsli = SignatureLogs::where('data_hash', $signature)->get()
            ->map(function ($item) {
                $data = json_decode($item->data);
                $signer = \App\Models\User::find($data->disetujui)?->karyawan?->nama ?? 'Sistem';
                return [
                    'surat_sp3_id' => $data->surat_sp3_id,
                    'disetujui' => $signer,
                    'status' => $data->status,
                    'keterangan' => $data->keterangan ?? null,
                    'approved_at' => $data->approved_at,
                ];
            })->toArray();
    }

    private function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        // Decode signature from base64
        $signatureBinary = base64_decode($signature);
        if ($signatureBinary === false) {
            throw new Exception("Invalid signature encoding");
        }

        // Get public key resource
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if ($publicKeyResource === false) {
            throw new Exception("Invalid public key");
        }

        // Verify signature
        $result = openssl_verify(
            $data,
            $signatureBinary,
            $publicKeyResource,
            OPENSSL_ALGO_SHA256
        );

        // Handle result
        if ($result === 1) {
            return true;
        } elseif ($result === 0) {
            $debugInfo = [
                'input_data' => $data,
                'public_key' => $publicKey,
                'signature' => $signature,
                'openssl_error' => openssl_error_string()
            ];
            Log::error('Error verify data : ' . json_encode($debugInfo));

            return false;
        } else {
            throw new Exception("Verification error: " . openssl_error_string());
        }
    }
}
