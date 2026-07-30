<?php

namespace App\Livewire\Profile\SignatureCerts;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Services\DigitalSignatureService;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Regenerate extends Component
{
    use Interactions;

    public $users;
    public string $password, $passwordConfirmation;

    protected DigitalSignatureService $digitalSignatureService;

    public $rules = [
        'password' => 'required|same:passwordConfirmation'
    ];

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function render()
    {
        return view('livewire.profile.signature-certs.regenerate');
    }

    public function mount(?User $users)
    {
        $this->users = $users;
    }

    public function submited() {}

    // function submit()
    // {
    //     $this->validate();

    //     // expired days 
    //     $exp_days = 1095; //3 tahun

    //     // temp dir untuk csr
    //     $tempDir = storage_path('app/temp/' . Str::random(16));
    //     if (!File::exists($tempDir)) {
    //         File::makeDirectory($tempDir, 0755, true);
    //     }

    //     // Subject 
    //     $cert_info = json_decode($this->users->certificate()->latest('id')->first()->cert_info);
    //     $subject = $cert_info->name;

    //     // ambil private key lama
    //     $privateKeyPath = Storage::disk('certs')->path($this->users->id . '/private.key');
    //     // $privateKey = file_get_contents($privateKeyPath);

    //     // generate .csr di tmp
    //     $csrPath = $tempDir . '/request.csr';
    //     shell_exec("openssl req -new -key {$privateKeyPath} -out {$csrPath} -subj '{$subject}'");

    //     // generate .crt di certificate
    //     $certPath = Storage::disk('certs')->path($this->users->id . '/certificate.key');
    //     shell_exec("openssl x509 -req -days {$exp_days} -in {$csrPath} -signkey {$privateKeyPath} -out {$certPath}");

    //     // generate .p12 di folder p12
    //     $p12Path = $tempDir . '/certificate.p12';
    //     shell_exec("openssl pkcs12 -export -out {$p12Path} -inkey {$privateKeyPath} -in {$certPath} -password pass:{$this->password}");

    //     if (file_exists($p12Path)) {
    //         $relativePath = $this->users->id;
    //         $finalPathP12 = $relativePath . '/p12/certificate-' . time() . '.p12';

    //         Storage::disk('certs')->put($relativePath . '/certificate.crt', file_get_contents($certPath));
    //         Storage::disk('certs')->put($finalPathP12, file_get_contents($p12Path));

    //         // parse p12 data
    //         $certs = $this->parseCertificate(p12: $finalPathP12, pass: $this->password);

    //         // simpan p12 data ke databse 
    //         SignatureCerts::create(
    //             [
    //                 'user_id' => $this->users->id,
    //                 'public_key' => $certs['public_key'],
    //                 'cert_info' => $certs['cert_info'],
    //                 'p12_path' => $certs['p12_path'],
    //                 'expired_at' => $certs['expired_at'],
    //             ]
    //         );

    //         // Hapus file temporary
    //         File::deleteDirectory($tempDir);

    //         $this->toast()
    //             ->success('Berhasil', 'Digital signature berhasil digenerate.')
    //             ->send();
    //     } else {
    //         $this->toast()
    //             ->error('Tidak Berhasil', 'Gagal generate digital signature.')
    //             ->send();
    //     }
    // }


    // // Parse data .P12
    // private function parseCertificate($p12, $pass)
    // {
    //     $pkcs12Path = Storage::disk('certs')->path($p12);
    //     $pkcs12 = file_get_contents($pkcs12Path);
    //     $certs = [];
    //     if (openssl_pkcs12_read($pkcs12, $certs, $pass)) {
    //         // Ambil data dari sertifikat
    //         $certData = openssl_x509_parse($certs['cert']);

    //         // ambil data public key
    //         $publicKey = openssl_pkey_get_details(openssl_pkey_get_public($certs['cert']))['key'];

    //         // ambil expired date
    //         $expiredAt = isset($certData['validTo_time_t'])
    //             ? Carbon::createFromTimestamp($certData['validTo_time_t'])
    //             : null;

    //         return [
    //             'public_key' => $publicKey,
    //             'cert_info' => json_encode($certData),
    //             'p12_path' => $p12,
    //             'expired_at' => $expiredAt,
    //         ];
    //     } else {
    //         // Gagal membaca p12
    //         throw new \Exception("Gagal membaca file .p12, mungkin password salah?");
    //     }
    // }
}
