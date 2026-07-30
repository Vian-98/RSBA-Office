<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SignatureCerts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Exception;

class SuperAdminSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find Super Admin user
        $user = User::where('email', 'admin@rsba.com')->first();

        if (!$user) {
            // Fallback: search by role Super-Admin
            $user = User::role('Super-Admin')->first();
        }

        if (!$user) {
            // Fallback: search by any user with admin in email or name
            $user = User::where('email', 'like', '%admin%')->first();
        }

        if (!$user) {
            $this->command->warn('User Super Admin tidak ditemukan. Seeder dibatalkan.');
            return;
        }

        $this->command->info("Generating digital signature certificate for user: {$user->nama} ({$user->email})");

        // 2. Define parameters for certificate
        $nama = $user->karyawan->nama ?? $user->nama ?? 'Super Admin';
        $email = $user->email;
        $org = 'RSBA';
        $orgUnit = 'IT';
        $password = 'password123'; // Default password for PKCS12 file (p12)

        // 3. Create a temporary OpenSSL configuration file
        $tempCnf = tempnam(sys_get_temp_dir(), 'openssl');
        file_put_contents($tempCnf, "
[ req ]
default_bits        = 2048
distinguished_name  = req_distinguished_name
x509_extensions     = v3_ca

[ req_distinguished_name ]
countryName                     = Country Name (2 letter code)
stateOrProvinceName             = State or Province Name
localityName                    = Locality Name
organizationName                = Organization Name
organizationalUnitName          = Organizational Unit Name
commonName                      = Common Name
emailAddress                    = Email Address

[ v3_ca ]
basicConstraints = critical,CA:false
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
");

        try {
            $config = [
                'config' => $tempCnf,
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];

            // Generate private key
            $privateKeyResource = openssl_pkey_new($config);
            if (!$privateKeyResource) {
                throw new Exception("Gagal membuat private key OpenSSL: " . openssl_error_string());
            }
            openssl_pkey_export($privateKeyResource, $privateKeyPEM, null, $config);

            // Subject Distinguished Name (DN)
            $dn = [
                "countryName" => "ID",
                "stateOrProvinceName" => "Lampung",
                "localityName" => "Bandar Lampung",
                "organizationName" => $org,
                "organizationalUnitName" => $orgUnit,
                "commonName" => $nama,
                "emailAddress" => $email
            ];

            // Generate CSR
            $csrResource = openssl_csr_new($dn, $privateKeyResource, $config);
            if (!$csrResource) {
                throw new Exception("Gagal membuat CSR OpenSSL: " . openssl_error_string());
            }

            // Generate Self-Signed Certificate valid for 3 years (1095 days)
            $certResource = openssl_csr_sign($csrResource, null, $privateKeyResource, 1095, $config);
            if (!$certResource) {
                throw new Exception("Gagal membuat certificate OpenSSL: " . openssl_error_string());
            }
            openssl_x509_export($certResource, $certPEM);

            // Export PKCS12 (P12) content
            $p12Content = '';
            if (!openssl_pkcs12_export($certResource, $p12Content, $privateKeyResource, $password)) {
                throw new Exception("Gagal mengexport PKCS12: " . openssl_error_string());
            }

            // 4. Save certificate files into storage
            $userId = $user->id;
            $stamp = time();
            $basePath = "{$userId}/current/";

            // Deactivate old certificates in database
            SignatureCerts::where('user_id', $userId)->update(['is_active' => 0]);

            // Put files into 'certs' disk (app/certificates)
            $p12Filename = "{$stamp}-certificate.p12";
            Storage::disk('certs')->put("{$basePath}{$stamp}-private.key", $privateKeyPEM);
            Storage::disk('certs')->put("{$basePath}{$stamp}-certificate.crt", $certPEM);
            Storage::disk('certs')->put("{$basePath}{$p12Filename}", $p12Content);

            // 5. Parse certificate info to get metadata for database
            $certInfo = openssl_x509_parse($certPEM);
            if (!$certInfo) {
                throw new Exception("Gagal memparsing certificate info.");
            }

            // 6. Create SignatureCerts database record
            SignatureCerts::create([
                'user_id' => $userId,
                'public_key' => $certPEM,
                'cert_info' => json_encode($certInfo),
                'p12_path' => $p12Filename,
                'expired_at' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
                'is_active' => 1,
            ]);

            $this->command->info("Digital signature certificate berhasil dibuat dan disimpan untuk Super Admin.");
        } finally {
            @unlink($tempCnf);
        }
    }
}
