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
     * Build public verification URL for a given document qr_hash.
     */
    public function getVerificationUrl(string $qrHash): string
    {
        return url('/verifikasi-surat/' . $qrHash);
    }
}
