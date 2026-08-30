<?php

namespace App\Services;

use Milon\Barcode\DNS2D;

class QrGeneratorService
{
    /**
     * Generate Base64 PNG QR Code image data.
     */
    public function generateQrPngBase64(string $content, int $width = 4, int $height = 4): string
    {
        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($content, 'QRCODE', $width, $height);
    }

    /**
     * Generate QR Code PNG untuk docstore_key.
     * QR ini yang dicetak di surat — saat di-scan membuka halaman verify dengan data dari docstore.
     *
     * @param string $docstoreKey UUID dari docstore
     * @param int $width
     * @param int $height
     * @return string base64 PNG
     */
    public function generateDocstoreQr(string $docstoreKey, int $width = 4, int $height = 4): string
    {
        $verifyUrl = $this->getDocstoreVerifyUrl($docstoreKey);
        return $this->generateQrPngBase64($verifyUrl, $width, $height);
    }

    /**
     * Build public verification URL untuk docstore_key.
     * URL ini di-embed ke QR code surat — scan QR → buka verify app langsung ke halaman dokumen tersebut.
     */
    public function getDocstoreVerifyUrl(string $docstoreKey): string
    {
        $verifyBaseUrl = config('services.docstore.verify_app_url', env('VERIFY_APP_URL', 'https://verify.makroboi.site'));
        return rtrim($verifyBaseUrl, '/') . '/?key=' . $docstoreKey;
    }

    /**
     * Build public verification URL untuk signature hash (legacy & direct scan).
     */
    public function getVerificationUrl(string $qrHash): string
    {
        $verifyBaseUrl = config('services.docstore.verify_app_url', env('VERIFY_APP_URL', 'https://verify.makroboi.site'));
        return rtrim($verifyBaseUrl, '/') . '/?key=' . $qrHash;
    }
}
