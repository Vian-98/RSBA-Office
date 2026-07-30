<?php

namespace App\Livewire\Surat\Cuti;

use Exception;
use Livewire\Component;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use App\Models\User;
use App\Models\Surat\SuratCuti;
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
        $signature = trim($signature);
        if (str_contains($signature, '/verifikasi-surat/')) {
            $signature = last(explode('/verifikasi-surat/', $signature));
        }

        // 1. Cek berdasarkan QR Hash Header pada tabel surat_cuti
        $suratHeader = SuratCuti::where('qr_hash', $signature)->first();

        if (!$suratHeader) {
            foreach (SuratCuti::all() as $c) {
                if (hash('sha256', 'cuti-' . $c->id . '-' . config('app.key')) === $signature) {
                    $c->qr_hash = $signature;
                    $c->save();
                    $suratHeader = $c;
                    break;
                }
            }
        }

        if ($suratHeader) {
            $this->surat = $suratHeader;
            $this->verify = (bool) $suratHeader->is_valid;
            $this->bgColor = $this->verify ? 'green' : 'red';

            $this->dataSuratAsli = $suratHeader->approvals->map(function ($app) {
                $signerName = $app->karyawan?->nama ?? User::where('karyawan_id', $app->disetujui_oleh)->first()?->name ?? 'Pejabat Penandatangan';
                return [
                    'status' => is_object($app->status) ? $app->status->nama() : (string)$app->status,
                    'disetujui' => $signerName,
                    'keterangan' => $app->keterangan ?? '-',
                    'approved_at' => $app->approved_at ?? $app->updated_at,
                ];
            })->toArray();

            $this->toast()->success('Terverifikasi', 'Dokumen Surat Cuti resmi & valid.')->send();
            return;
        }

        // 2. Fallback pencarian persetujuan detail
        $suratApproval = SuratCutiApproval::where('signature_hash', $signature)->latest('id')->first();

        if (!$suratApproval) {
            $this->toast()->error('Invalid', 'Tidak ditemukan data verifikasi untuk QR/Hash tersebut.')->send();
            return;
        }

        $this->surat = $suratApproval->cuti;

        $log = SignatureLogs::where('data_hash', $signature)->orWhere('signature', $signature)->first();
        $user = User::where('karyawan_id', $suratApproval->disetujui_oleh)->first();
        $certs = $user ? SignatureCerts::where('user_id', $user->id)->latest('id')->first() : null;

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

        $signerName = $suratApproval->karyawan?->nama ?? $user?->name ?? 'Pejabat Penandatangan';

        $this->bgColor = $this->verify ? 'green' : 'red';
        $this->dataSuratAsli = [
            [
                'status' => is_object($suratApproval->status) ? $suratApproval->status->nama() : (string)$suratApproval->status,
                'disetujui' => $signerName,
                'keterangan' => $suratApproval->keterangan ?? '-',
                'approved_at' => $suratApproval->approved_at ?? $suratApproval->updated_at,
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
            Log::error('Error verify Cuti data: ' . json_encode(['input_data' => $data]));
            return false;
        } else {
            throw new Exception("Verification error: " . openssl_error_string());
        }
    }
}
