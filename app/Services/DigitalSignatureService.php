<?php

namespace App\Services;

use Throwable;
use Exception;
use App\Models\SignatureCerts;
use App\Models\SignatureLogs;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureService
{
    private const DEFAULT_KEY_SIZE = 2048;
    private const DEFAULT_ALGORITHM = 'sha256';
    private const DEFAULT_EXPIRY_DAYS = 1095; //3 years
    private const TEMP_DIR_PREFIX = 'app/temp/certs/';
    private const DISK_DIR_STORE = 'certs';

    /**
     * Get the openssl binary path from env, fallback to 'openssl' in PATH.
     */
    private function opensslBin(): string
    {
        return env('OPENSSL_BIN', 'openssl');
    }

    /**
     * Get the openssl config path from env or common fallbacks.
     */
    private function opensslConf(): ?string
    {
        $conf = env('OPENSSL_CONF');
        if ($conf && file_exists($conf)) {
            return $conf;
        }
        
        $fallbacks = [
            'C:/xampp/apache/conf/openssl.cnf',
            'C:/Program Files/Common Files/SSL/openssl.cnf',
            'C:/Program Files (x86)/Common Files/SSL/openssl.cnf',
        ];
        
        foreach ($fallbacks as $fb) {
            if (file_exists($fb)) {
                return $fb;
            }
        }
        
        return null;
    }

    protected string $country = "ID";
    protected string $state = "Lampung";
    protected string $local = "Bandar Lampung";
    protected string $org = "RSBA";
    protected array $sanDomains = [
        'rspba.co.id',
        '*.rspba.co.id',
        'office.rsba.co.id',
        'office.rsba'
    ];


    /**
     * Generate certificate data dan file p12
     */
    public function generate(
        User $user,
        string $nama,
        string $org,
        string $org_unit,
        string $email,
        ?string $password,
        int $exp  = self::DEFAULT_EXPIRY_DAYS,
    ) {
        $lastCertificate = $user->certificate()->latest('id')->first();

        // SAN (Subject Alternative Names)
        $sanDomains = $this->sanDomains;

        // ::CREATE TEMPORARY FILE::
        $tempDir = storage_path(self::TEMP_DIR_PREFIX . uniqid());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        try {
            //Make subject certificate
            $subject = $this->buildCertificateSubject($nama, $org, $org_unit, $email);

            // Generate Certificate return paths each of certificate
            $paths = $this->generateCertificateFiles($tempDir, $subject, $password, $exp, $sanDomains);

            // Save certificate files to storage
            $storagePaths = $this->saveCertificateFiles(
                userId: $user->id,
                paths: $paths,
                status: 'new'
            );

            // Extract or Parsing Certificate
            $certData = $this->extractCertificateData(
                userId: $user->id,
                filename: $storagePaths['p12'],
                pass: $password
            );

            // Save certificate info to database
            $signatureCert = $this->saveCertificateToDatabase($user->id, $certData);

            // update certificate sebelumnya menjadi tidak aktif
            if ($lastCertificate) {
                $lastCertificate->update([
                    'is_active' => 0
                ]);
            }

            // Try generate pkcs12
            return [
                'status' => true,
                'message' => 'Digital signature berhasil digenerate.',
                'certificate' => $signatureCert,
                'paths' => $storagePaths
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'message' => "Gagal generate digital signature. ({$e->getMessage()})",
                'error' => $e->getMessage()
            ];
        } finally {
            // Clean up temporary files
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Perpanjangan certificate
     * TODO: renew certificate p12 dengan private key dan csr yang sama (perpanjangan waktu valid)
     */
    public function renewGenerate(
        User $user,
        string $existingPrivateKeyPath,
        string $password,
        int $expiryDays = self::DEFAULT_EXPIRY_DAYS
    ) {
        $sanDomains = $this->sanDomains;

        // ::CREATE TEMPORARY FILE::
        $tempDir = storage_path(self::TEMP_DIR_PREFIX . Str::random(16));
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        $paths = $this->defineCertificatePaths($tempDir);

        if (!file_exists($existingPrivateKeyPath)) {
            throw new Exception("Private key tidak ditemukan {$existingPrivateKeyPath}");
        }

        // Skip generate private key, gunakan yang sudah ada.
        if ($existingPrivateKeyPath !== $paths['privateKey']) {
            copy($existingPrivateKeyPath, $paths['privateKey']);
        }

        $subject = '';
        // generate CSR
        $this->execSSLCsr($paths, $subject);

        // Update last certificate is_active = 0
    }

    /**
     * Create digital signature from data and certificate
     *
     * @param string $data Data yang akan di-sign
     * @param string $certificate Nama file certificate (*.p12)
     * @param string $password Password certificate
     * @return string Base64 encoded signature
     * @throws Exception
     */
    public function signData(
        User $user,
        $data,
        ?string $type,
        int $id,
        string $algorithm = self::DEFAULT_ALGORITHM,
        ?string $password = null,
    ): array {

        try {
            // dd($user, $data, $password, $type, $id);
            // validateb
            if (!is_string($data)) {
                $data = json_encode($data);
            }
            // 01. p12 file and path
            $certificate = $this->getActiveCertificate($user->id);

            // 03. get privateKey
            $keyPassword = $password;
            if ($user->id === 1 && $keyPassword === null) {
                $keyPassword = 'password123';
            }
            $privateKey = $this->extractPrivateKey($user->id, $certificate->p12_path, $keyPassword);

            // 04. Sign
            // openssl_sign($data,$signature,$privateKey,$algorithm)
            $rawSignature = $this->createSignature($data, $privateKey, $algorithm);
            $signature = base64_encode($rawSignature);
            $dataHash = hash($algorithm, $data);

            // create logs signature
            $this->saveSignDataLogs(
                userId: $user->id,
                data: $data,
                signature: $signature,
                data_hash: $dataHash,
                algorithm: $algorithm,
                type: $type,
                id: $id,
                certificate_id: $certificate->id
            );


            // return result signature
            return [
                'status' => true,
                'message' => 'Data berhasil ditandatangani.',
                'signature' => $signature,
                'data_hash' => $dataHash,
                'algorithm' => $algorithm,
                'certificate_id' => $certificate->id,
                'signed_at' => now()->toIso8601String(),
                'ip_address' => request()->ip()
            ];

            // 05. return base64_encode($signature)
        } catch (Throwable $e) {
            return [
                'status' => false,
                'message' => "Gagal menandatangani data, " . $e->getMessage()
            ];
        } finally {
            // 05. cleanup
            // if ($privateKey !== null) {
            //     openssl_free_key($privateKey);
            // }
        }
    }


    /**
     * Attach ddigital signature pada file
     */
    public function signFile() {}


    /**
     * Verify digital signature
     *
     * @param string $data Original data
     * @param string $signature Base64 encoded signature
     * @param string $certificate Certificate file name
     * @param string $password Certificate password
     * @return bool
     * @throws Exception
     */
    public function verifyDataSignature(): bool
    {

        return true;
    }

    /**
     * Verify file signature
     */
    public function verifyFileSignature(): array
    {
        return [];
    }


    /**
     * Get certificate information
     *
     * @param string $certificate Certificate file name
     * @param string $password Certificate password
     * @return array
     * @throws Exception
     */
    public function getCertificateInfo(): array
    {

        return [];
    }



    /**
     * Validate certificate password
     *
     * @param string $certificate
     * @param string $password
     * @return bool
     */
    public function validatePassword(): bool
    {
        return true;
    }



    // ==================== Private Helper Methods ====================

    /**
     * Build Certificate Subject
     * @param $nama
     * @param $org
     * @param $org_unit
     * @param $email
     */
    private function buildCertificateSubject(string $nama, string $org, string $org_unit, string $email): string
    {
        return sprintf(
            "/C=%s/ST=%s/L=%s/O=%s/OU=%s/CN=%s/emailAddress=%s",
            $this->country,
            $this->state,
            $this->local,
            $org,
            $org_unit,
            $nama,
            $email
        );
    }

    /**
     * Paths setiap certifikat yang dibuat
     */
    protected function defineCertificatePaths(string $tempDir): array
    {
        return [
            'privateKey' => "$tempDir/private.key",
            'csr' => "{$tempDir}/request.csr",
            'cert' => "{$tempDir}/certificate.crt",
            'p12' => "{$tempDir}/certificate.p12",
            'configFile' => "{$tempDir}/openssl_v3.cnf"
        ];
    }

    /**
     * Create certificate file using execute SSL Command 
     */
    private function executeSSLCommand(
        array $paths,
        string $subject,
        string $password,
        int $expiryDays,
        array $sanDomains
    ): void {
        /** ::Generate Private Key::
         * shell_exec("openssl genrsa -out {$paths['privateKey']} " . self::DEFAULT_KEY_SIZE);
         */
        $this->execSSLPrivateKey($paths);

        /** ::Generate CSR (Certificate Signing Request)::
         * shell_exec("openssl req -new -key {$paths['privateKey']} -out {$paths['csr']} -subj '{$subject}'");
         */
        $this->execSSLCsr($paths, $subject);


        /** ::Self-Sign Certificate X509 
         *  V1
         * shell_exec("openssl genrsa -out {$paths['privateKey']} " . self::DEFAULT_KEY_SIZE);
         * 
         * V3
         * shell_exec("openssl x509 -req -days {$expiryDays} -in {$paths['csr']} -signkey {$paths['privateKey']} -out {$paths['cert']} -extfile {$configFile} -extensions v3_req");
         */
        $this->execX509($paths, $sanDomains, $expiryDays); // version 3


        /** ::Generate PKCS#12 file::
         * shell_exec("openssl pkcs12 -export -out {$paths['p12']} -inkey {$paths['privateKey']} -in {$paths['cert']} -password pass:{$password}");
         */
        $this->execPKCS12($paths, $password);
    }

    /**
     * Shell: create private key file.
     */
    private function execSSLPrivateKey(array $paths): void
    {
        $bin = $this->opensslBin();
        shell_exec("\"{$bin}\" genrsa -out {$paths['privateKey']} " . self::DEFAULT_KEY_SIZE);

        if (!file_exists($paths['privateKey']) || filesize($paths['privateKey']) === 0) {
            throw new Exception("Tidak berhasil membuat private key file.");
        }
    }

    /**
     * Shell: create CSR file
     */
    private function execSSLCsr(array $paths, string $subject): void
    {
        $bin = $this->opensslBin();
        $conf = $this->opensslConf();
        $configFlag = $conf ? " -config \"" . $conf . "\"" : "";
        
        $escapedSubject = str_replace('"', '\"', $subject);
        
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "\"{$bin}\" req -new -key {$paths['privateKey']} -out {$paths['csr']} -subj \"{$escapedSubject}\"{$configFlag}";
        } else {
            $cmd = "\"{$bin}\" req -new -key {$paths['privateKey']} -out {$paths['csr']} -subj '{$subject}'{$configFlag}";
        }
        
        shell_exec($cmd);

        if (!file_exists($paths['csr']) || filesize($paths['csr']) === 0) {
            throw new Exception("Tidak berhasil membuat CSR file.");
        }
    }


    /**
     * Shell: Create X509 version 3 file.
     */
    private function execX509(array $paths, array $sanDomains, $expiryDays): void
    {
        $configFile = $paths['configFile'] ?? sys_get_temp_dir() . '/openssl_v3.cnf';
        $this->createSSLv3ConfigFile($configFile, $sanDomains);

        $bin = $this->opensslBin();
        shell_exec("\"{$bin}\" x509 -req -days {$expiryDays} -in {$paths['csr']} -signkey {$paths['privateKey']} -out {$paths['cert']} -extfile {$configFile} -extensions v3_req");

        if (!file_exists($paths['cert']) || filesize($paths['cert']) === 0) {
            throw new Exception("Tidak berhasil membuat X.509 sertifikat.");
        }
    }

    /**
     * Shell: Create PKCS#12 file
     */
    private function execPKCS12(array $paths, string $password): void
    {
        $bin = $this->opensslBin();
        shell_exec("\"{$bin}\" pkcs12 -export -out {$paths['p12']} -inkey {$paths['privateKey']} -in {$paths['cert']} -password pass:{$password}");

        if (!file_exists($paths['p12']) || filesize($paths['p12'] === 0)) {
            throw new Exception("Gagal membuat file PCKS#12");
        }
    }

    /**
     * Shell : Signing data
     */
    private function createSignature($data, $privateKey, string $algorithm): string
    {
        $signature = '';
        $openSslAlgo  = $this->getOpensslAlgo($algorithm);
        $signing = openssl_sign($data, $signature, $privateKey, $openSslAlgo);

        if (!$signing) {
            throw new Exception("Gagal membuat signature data.");
        }

        return $signature;
    }


    /**
     * Generate Certificate File
     * @return \Paths temporary each of certificate created. 
     */
    private function generateCertificateFiles(
        string $tempDir,
        string $subject,
        ?string $password,
        int $expiryDays,
        array $sanDomains
    ): array {
        $paths = $this->defineCertificatePaths($tempDir);

        $this->executeSSLCommand($paths, $subject, $password, $expiryDays, $sanDomains);

        if (!file_exists($paths['p12'])) {
            throw new Exception("Tidak berhasil generate PKCS#12 file.");
        }

        return $paths;
    }

    /**
     * Storing files certificate temp to storage
     */
    private function saveCertificateFiles(int $userId, $paths, string $status)
    {
        $stamp = time(); // (int) date time utc
        $basePath = (string) "{$userId}/current/";
        $archivedPath = (string) "{$userId}/archived/";

        // Archived crt dan p12 sembelum simpan yang baru
        $this->archiveOldCertificates($basePath, $archivedPath, $status);

        $pathsStore = [
            'privateKey' => "{$basePath}{$stamp}-private.key",
            'crt' => "{$basePath}{$stamp}-certificate.crt",
            'p12' => "{$stamp}-certificate.p12", //filename
            'p12Path' => "{$basePath}{$stamp}-certificate.p12" //p12 path 
        ];

        // put on storage
        Storage::disk(self::DISK_DIR_STORE)->put($pathsStore['privateKey'], file_get_contents($paths['privateKey']));
        Storage::disk(self::DISK_DIR_STORE)->put($pathsStore['crt'], file_get_contents($paths['cert']));
        Storage::disk(self::DISK_DIR_STORE)->put($pathsStore['p12Path'], file_get_contents($paths['p12']));

        return [
            'privateKey' => $pathsStore['privateKey'],
            'cert' => $pathsStore['crt'],
            'p12' => $pathsStore['p12'],
            'p12Path' => $pathsStore['p12Path']
        ];
    }

    private function archiveOldCertificates(string $currentPath, string $archivedPath, string $status = 'new')
    {
        $disk = Storage::disk(self::DISK_DIR_STORE);

        if (!$disk->exists($currentPath)) {
            return; // no directory, tidak ada untuk di archived
        }
        // get all files dalam directory
        $files = $disk->files($currentPath);

        if (empty($files)) {
            return; //tidak ada file
        }

        // Filter files matching certificate patterns
        $certificateFiles = array_filter($files, function ($file) use ($status) {
            $fileName = basename($file);

            $certificate = str_ends_with($fileName, '-certificate.crt') ||
                str_ends_with($fileName, '-certificate.p12');

            $privateKey = str_ends_with($fileName, '-private.key');

            return $certificate || ($privateKey && $status === 'new');
        });

        if (empty($certificateFiles)) {
            return; // No certificate files found
        }

        // Folder "archived" sudah ada ?
        if ($disk->exists($archivedPath)) {
            $disk->makeDirectory($archivedPath);
        }

        foreach ($certificateFiles as $file) {
            $fileName = basename($file);
            $archiveDestination = $archivedPath . $fileName;

            $disk->move($file, $archiveDestination);
        }
    }


    /**
     * Get certificate p12 active by user
     * @param $userId, int
     */

    public function getActiveCertificate(int $userId): SignatureCerts
    {
        $certificate = SignatureCerts::where('user_id', $userId)
            ->where('expired_at', '>', now())
            ->where('is_active', 1)
            ->latest()
            ->first();

        if (!$certificate) {
            throw new Exception("Tidak ada sertifikat aktif untuk user ini.");
        }

        return $certificate;
    }

    /**
     * Get P12 path on storage
     * build path p12 base filename
     */
    public function getPathP12(int $userId, string $filename)
    {
        $folders = ['current', 'archived'];

        foreach ($folders as $folder) {
            $filePath = "{$userId}/{$folder}/{$filename}";

            $file =  Storage::disk(self::DISK_DIR_STORE)->get($filePath);
            if ($file) {
                return [
                    'file' => $filename,
                    'path' => $filePath,
                    'folder' => $folder,
                    'exists' => true,
                ];
            }
        }

        throw new Exception("File PKCS#12 tidak ditemukan di storage.");
    }

    /**
     * Parsing / Menguraikan PKCS#12 File
     * @param $userId, user id
     * @param $filename, filename p12
     * @param $pass, password certficate
     */
    private function extractCertificateData(int $userId, string $filename, string $pass)
    {
        $p12Path = $this->getPathP12($userId, $filename);
        $pkcs12File = $this->readFileP12($p12Path['path'], self::DISK_DIR_STORE);
        $certs = $this->parseP12Content($pkcs12File, $pass);
        $certInfo = $this->parseCertificateInfo($certs);

        return [
            'public_key' => $certs['cert'],
            'cert_info' => json_encode($certInfo),
            'p12_path' => $filename, //save filename 
            'expired_at' => date('Y-m-d H:i:s', $certInfo['validTo_time_t'])
        ];
    }

    /**
     * Get ProvateKey dalam p12
     */
    private function extractPrivateKey(int $userId, string $p12Filename, ?string $password)
    {
        $p12Path = $this->getPathP12($userId, $p12Filename);
        $p12File = $this->readFileP12($p12Path['path'], self::DISK_DIR_STORE);

        $certs = $this->parseP12Content($p12File, $password);
        $privateKey = openssl_pkey_get_private($certs['pkey']);

        if (!$privateKey) {
            throw new Exception("Gagal mendapatkan private key.");
        }

        return $privateKey;
    }

    /**
     * Read File PKCS#12
     * @param $path, path certificate.p12
     */
    private function readFileP12(string $path)
    {
        if (!Storage::disk(self::DISK_DIR_STORE)->exists($path)) {
            throw new Exception("File sertifikat tidak ditemukan : {$path}");
        }

        $pkcs12Content = Storage::disk(self::DISK_DIR_STORE)->get($path);
        if ($pkcs12Content === null) {
            throw new Exception("Gagal membaca file sertfikat : {$path}");
        }

        return $pkcs12Content;
    }

    /**
     * Parsing PKCS#12
     */
    private function parseP12Content(string $content, ?string $password): array
    {
        $certs = [];
        if (!openssl_pkcs12_read($content, $certs, $password)) {
            throw new Exception("Proses parsing sertifikat tidak berhasil, pastikan password anda benar.");
        }

        return $certs;
    }

    /**
     * Parsing certificate Info
     */
    private function parseCertificateInfo(array $certs): array
    {
        $certInfo = openssl_x509_parse($certs['cert']);

        if (!$certInfo) {
            throw new Exception("Gagal memparsing data sertifikat info.");
        }
        return $certInfo;
    }

    private function createSSLv3ConfigFile($configFile, $sanDomains = [])
    {
        // Default SAN jika tidak ada
        if (empty($sanDomains)) {
            $sanDomains = ['localhost', '127.0.0.1'];
        }

        // Build SAN string
        $sanEntries = [];
        $dnsCount = 1;
        $ipCount = 1;

        foreach ($sanDomains as $entry) {
            // Check if IP address
            if (filter_var($entry, FILTER_VALIDATE_IP)) {
                $sanEntries[] = "IP.{$ipCount} = {$entry}";
                $ipCount++;
            } else {
                $sanEntries[] = "DNS.{$dnsCount} = {$entry}";
                $dnsCount++;
            }
        }

        $sanString = implode("\n", $sanEntries);

        // OpenSSL v3 extensions configuration
        $config = <<<EOL
        [ v3_req ]
        # Extensions untuk X.509 v3
        basicConstraints = CA:FALSE
        keyUsage = nonRepudiation, digitalSignature, keyEncipherment
        extendedKeyUsage = serverAuth, clientAuth
        subjectKeyIdentifier = hash
        authorityKeyIdentifier = keyid,issuer

        # Subject Alternative Name (SAN)
        subjectAltName = @alt_names

        [ alt_names ]
        {$sanString}
        EOL;

        file_put_contents($configFile, $config);
    }

    /**
     * Convert hash algorithm string to OpenSSL constant
     */
    private function getHashAlgo(int $opensslAlgo): string
    {
        $mapping = [
            OPENSSL_ALGO_SHA256 => 'sha256',
            OPENSSL_ALGO_SHA384 => 'sha384',
            OPENSSL_ALGO_SHA512 => 'sha512',
            OPENSSL_ALGO_SHA1 => 'sha1',
        ];
        return $mapping[$opensslAlgo] ?? 'sha256';
    }

    /**
     * Convert string to OpenSSL algorithm constant
     */
    private function getOpensslAlgo(string $hashAlgo): int
    {
        $mapping = [
            'sha256' => OPENSSL_ALGO_SHA256,
            'sha384' => OPENSSL_ALGO_SHA384,
            'sha512' => OPENSSL_ALGO_SHA512,
            'sha1' => OPENSSL_ALGO_SHA1,
        ];
        return $mapping[$hashAlgo] ?? OPENSSL_ALGO_SHA256;
    }

    /**
     * Maps type signature logs table
     */
    private function getTableMap(string $type): string
    {
        $mapping = [
            'permintaan_beli_approval' => 'um_pembelian_requests'
        ];

        return $mapping[$type] ?? '';
    }


    /**
     * Create record database
     */
    private function saveCertificateToDatabase(int $userId, array $certData): SignatureCerts
    {
        return SignatureCerts::create(
            [
                'user_id' => $userId,
                'public_key' => $certData['public_key'],
                'cert_info' => $certData['cert_info'],
                'p12_path' => $certData['p12_path'],
                'expired_at' => $certData['expired_at'],
            ]
        );
    }

    private function saveSignDataLogs(
        int $userId,
        $data,
        $signature,
        $data_hash,
        $algorithm,
        string $type,
        int $id,
        int $certificate_id
    ): SignatureLogs {
        return SignatureLogs::create(
            [
                'data' => $data,
                'signature' => $signature,
                'data_hash' => $data_hash,
                'algorithm' => $algorithm,
                'sign_type' => $type,
                'sign_id' => $id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => $userId,
                'certificate_id' => $certificate_id,
            ]
        );
    }
}
