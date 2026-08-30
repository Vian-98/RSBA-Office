<?php

namespace App\Livewire\Profile\SignatureCerts;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Services\DigitalSignatureService;
use Livewire\Attributes\Computed;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Index extends Component
{
    use Interactions;

    public User $users;

    public string $pkcs12_password;
    public bool $modalPassw = false;

    public $rules = [
        'pkcs12_password' => 'required|string'
    ];

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount()
    {
        $this->users = auth()->user();
        // $this->passEncrypted = Crypt::encryptString($this->password);

        // $this->passDescrypted = Crypt::decryptString($this->passEncrypted);
    }

    #[Computed]
    public function getCertificate()
    {
        try {
            $certificate = $this->digitalSignatureService->getActiveCertificate($this->users->id);
            if (!$certificate) {
                return null;
            }

            $userId = $certificate->user_id;
            $filename = $certificate->p12_path ?? 'vault://' . $userId;
            $pathP12 = $this->digitalSignatureService->getPathP12($userId, $filename);

            $cert_info = is_string($certificate->cert_info) ? json_decode($certificate->cert_info) : (object) $certificate->cert_info;
            if (!$cert_info) {
                $cert_info = (object) [];
            }

            if (!isset($cert_info->serialNumberHex)) {
                $cert_info->serialNumberHex = 'SN-' . strtoupper(substr(md5((string) ($certificate->id ?? $userId)), 0, 16));
            }
            if (!isset($cert_info->issuer)) {
                $cert_info->issuer = (object) ['O' => 'RS Bintang Amin', 'CN' => 'RSBA Digital Signature Authority'];
            }
            $validFrom = isset($cert_info->validFrom_time_t) ? Carbon::createFromTimestamp($cert_info->validFrom_time_t) : ($certificate->created_at ? Carbon::parse($certificate->created_at) : now());
            $validTo = isset($cert_info->validTo_time_t) ? Carbon::createFromTimestamp($cert_info->validTo_time_t) : ($certificate->expired_at ? Carbon::parse($certificate->expired_at) : now()->addYears(3));

            return [
                'cert_info' => $cert_info,
                'p12_path' => $pathP12,
                'created' => $validFrom->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
                'expired' => $validTo->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
                'is_expired' => now()->timestamp >= $validTo->timestamp,
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.profile.signature-certs.index');
    }
}
