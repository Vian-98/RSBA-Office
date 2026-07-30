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
    function getCertificate()
    {
        try {
            $certificate = $this->digitalSignatureService->getActiveCertificate($this->users->id);
        } catch (Throwable $e) {
            $this->toast()
                ->error('Not Found !.', $e->getMessage())
                ->send();

            return;
        }

        $userId = $certificate->user_id;
        $filename = $certificate->p12_path;

        try {
            $pathP12 = $this->digitalSignatureService->getPathP12($userId, $filename);

            $cert_info = json_decode($certificate->cert_info);

            return [
                'cert_info' => $cert_info,
                'p12_path' => $pathP12,
                'created' => Carbon::parse($cert_info->validFrom_time_t)->locale('id'),
                'expired' => Carbon::parse($cert_info->validTo_time_t)->locale('id'),
                'is_expired' => time() >= (int) $cert_info->validTo_time_t ? true : false,
            ];
        } catch (Throwable $e) {
            $this->toast()
                ->error('Not Found!', $e->getMessage())
                ->send();

            return null;
        }
    }

    public function render()
    {
        return view('livewire.profile.signature-certs.index');
    }
}
