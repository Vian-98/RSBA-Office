<?php

namespace App\Services;

use App\Models\User;
use App\Models\SignatureCerts;
use Illuminate\Support\Facades\Log;

class SystemCertificateService
{
    public function __construct(
        protected DigitalSignatureService $digitalSignatureService
    ) {}

    /**
     * Get or create active System PKCS#12 (.p12) User & Certificate for official document signing.
     */
    public function getOrCreateSystemUser(): User
    {
        $systemUser = User::where('email', 'system@rsba.co.id')->first();

        if (!$systemUser) {
            $karyawanSystem = \App\Models\Sdm\Karyawan::where('nip', '999999999')->first() ?? \App\Models\Sdm\Karyawan::forceCreate([
                'nip' => '999999999',
                'nik' => '9999999999999999',
                'nama' => 'Sistem RSBA',
                'tgl_lahir' => '1990-01-01',
                'hp' => '0800000000',
                'prov' => 'Lampung',
                'kab' => 'Bandar Lampung',
                'kec' => 'Kedaton',
                'desa' => 'Penengahan',
                'alamat' => 'Jl. RSBA',
                'agama' => 'islam',
                'tgl_masuk' => '2020-01-01',
            ]);

            $systemUser = User::forceCreate([
                'email' => 'system@rsba.co.id',
                'password' => bcrypt('SystemRSBA2026!'),
                'karyawan_id' => $karyawanSystem->id,
            ]);
        }

        // Ensure active system PKCS12 certificate exists for this user
        $cert = SignatureCerts::where('user_id', $systemUser->id)->where('is_active', 1)->first();
        if (!$cert) {
            $res = $this->digitalSignatureService->generate(
                user: $systemUser,
                nama: 'Sistem Resmi RSBA',
                org: 'RSBA',
                org_unit: 'Manajemen Sistem',
                email: 'system@rsba.co.id',
                password: 'password123'
            );

            if (!($res['status'] ?? false)) {
                Log::warning('Gagal auto-generate sertifikat sistem: ' . ($res['message'] ?? 'Unknown error'));
            }
        }

        return $systemUser;
    }

    /**
     * Ensure System PKCS#12 (.p12) certificate exists and return active cert model.
     */
    public function getActiveSystemCertificate(): ?SignatureCerts
    {
        $systemUser = $this->getOrCreateSystemUser();

        return SignatureCerts::where('user_id', $systemUser->id)
            ->where('is_active', 1)
            ->latest('id')
            ->first();
    }
}
