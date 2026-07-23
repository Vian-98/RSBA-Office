<?php

namespace App\Livewire\Surat\Sp3;

use Exception;
use Livewire\Component;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use App\Models\User;
use TallStackUi\Traits\Interactions;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Approval;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Lazy;

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
        return view('livewire.surat.sp3.verify');
    }

    public function updatedSignature()
    {
        $this->bgColor = 'white';
        $this->verify = false;

        $this->validate([
            'signature' => 'required|string',
        ]);

        $signature = $this->signature;
        $this->getSignature(signature: $signature);
        $this->signature = '';
    }

    private function getSignature(string $signature): void
    {
        $signature = trim($signature);
        if (str_contains($signature, '/verifikasi-surat/')) {
            $signature = last(explode('/verifikasi-surat/', $signature));
        }

        // 1. Cek berdasarkan QR Hash Header pada tabel surat_sp3
        $suratHeader = SuratSp3::where('qr_hash', $signature)->first();

        if (!$suratHeader) {
            foreach (SuratSp3::all() as $s) {
                if (hash('sha256', 'sp3-' . $s->id . '-' . config('app.key')) === $signature) {
                    $s->qr_hash = $signature;
                    $s->save();
                    $suratHeader = $s;
                    break;
                }
            }
        }

        if ($suratHeader) {
            $this->surat = $suratHeader;
            $this->verify = (bool) $suratHeader->is_valid;
            $this->bgColor = $this->verify ? 'green' : 'red';

            $this->dataSuratAsli = $suratHeader->approvals->map(function ($app) {
                $user = $app->users ?? User::find($app->disetujui);
                $signerName = $user?->karyawan?->nama ?? $user?->name ?? $app->penyetuju?->nama ?? 'Pejabat SP3';
                return [
                    'surat_sp3_id' => $app->surat_sp3_id,
                    'disetujui' => $signerName,
                    'status' => is_object($app->status) ? $app->status->nama() : (string)$app->status,
                    'keterangan' => $app->keterangan ?? '-',
                    'approved_at' => $app->approved_at ?? $app->created_at,
                ];
            })->toArray();

            $this->toast()->success('Terverifikasi', 'Dokumen SP3 resmi & valid.')->send();
            return;
        }

        // 2. Fallback pencarian persetujuan detail
        $suratApproval = SuratSp3Approval::where('signature_hash', $signature)->latest('id')->first();

        if (!$suratApproval) {
            $this->toast()->error('Invalid', 'Tidak ditemukan data verifikasi untuk QR/Hash tersebut.')->send();
            return;
        }
        $this->surat = $suratApproval->surat;

        // Cek SignatureLogs
        $log = SignatureLogs::where('data_hash', $signature)->orWhere('signature', $signature)->first();
        $certs = SignatureCerts::where('user_id', $suratApproval->disetujui)->latest('id')->first();

        if ($log && $certs) {
            try {
                $this->verify = $this->verifySignature(
                    data: $log->data,
                    signature: $log->signature,
                    publicKey: $certs->public_key
                );
            } catch (\Throwable $e) {
                $this->verify = true;
            }
        } else {
            $this->verify = true;
        }

        $user = $suratApproval->users ?? User::find($suratApproval->disetujui);
        $signerName = $user?->karyawan?->nama ?? $user?->name ?? 'Pejabat SP3';

        $this->bgColor = $this->verify ? 'green' : 'red';
        $this->dataSuratAsli = [
            [
                'surat_sp3_id' => $suratApproval->surat_sp3_id,
                'disetujui' => $signerName,
                'status' => is_object($suratApproval->status) ? $suratApproval->status->nama() : (string)$suratApproval->status,
                'keterangan' => $suratApproval->keterangan ?? '-',
                'approved_at' => $suratApproval->approved_at ?? $suratApproval->created_at,
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
            Log::error('Error verify SP3 data: ' . json_encode(['input_data' => $data]));
            return false;
        } else {
            throw new Exception("Verification error: " . openssl_error_string());
        }
    }
}
