<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class PdfStamperService
{
    /**
     * Hard-stamp the RSBA QR Code Digital Signature Stamp Badge directly into page 1 of the PDF binary stream.
     */
    public function stampPdf(
        string $pdfPathOrBytes,
        float $pctX,
        float $pctY,
        float $scalePercent,
        string $signerName,
        string $signedAtDate,
        string $shaHash,
        string $documentNumber = '',
        string $title = '',
        string $verifyUrl = ''
    ): string {
        $stampImgPath = $this->generateSealImage($signerName, $signedAtDate, $shaHash, $verifyUrl);

        try {
            $pdf = new Fpdi();
            
            // Embed plain text metadata for docstore verification parsing
            $pdf->SetTitle($documentNumber . ' - ' . $title);
            $pdf->SetSubject($documentNumber);
            $pdf->SetKeywords($documentNumber . ' ' . $shaHash . ' OFFICIAL_SEALED_DOCUMENT');

            $tempInputFile = null;
            if (!file_exists($pdfPathOrBytes)) {
                $tempInputFile = tempnam(sys_get_temp_dir(), 'pdf_in_') . '.pdf';
                file_put_contents($tempInputFile, $pdfPathOrBytes);
                $pdfFileToRead = $tempInputFile;
            } else {
                $pdfFileToRead = $pdfPathOrBytes;
            }

            $pageCount = $pdf->setSourceFile($pdfFileToRead);

            for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
                $templateId = $pdf->importPage($pageNum);
                $size = $pdf->getTemplateSize($templateId);

                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Hard-stamp on Page 1
                if ($pageNum === 1) {
                    $pageWidthMm = $size['width'];
                    $pageHeightMm = $size['height'];

                    // Base stamp badge dimensions (230px width @ 850px page = ~56mm width, ~23mm height)
                    $baseStampWidthMm = 56.0 * ($scalePercent / 100);
                    $baseStampHeightMm = 23.0 * ($scalePercent / 100);

                    $posXmm = ($pctX / 100) * $pageWidthMm;
                    $posYmm = ($pctY / 100) * $pageHeightMm;

                    // Ensure stamp stays within page bounds
                    $posXmm = min($posXmm, $pageWidthMm - $baseStampWidthMm);
                    $posYmm = min($posYmm, $pageHeightMm - $baseStampHeightMm);

                    $pdf->Image($stampImgPath, $posXmm, $posYmm, $baseStampWidthMm, $baseStampHeightMm, 'PNG');
                }
            }

            $outputPdfContent = $pdf->Output('S');

            // Cleanup temporary files
            if (file_exists($stampImgPath)) @unlink($stampImgPath);
            if ($tempInputFile && file_exists($tempInputFile)) @unlink($tempInputFile);

            return $outputPdfContent;
        } catch (\Throwable $e) {
            Log::error('FPDI Hard-Stamp Error: ' . $e->getMessage());
            if (file_exists($stampImgPath)) @unlink($stampImgPath);
            if (isset($tempInputFile) && file_exists($tempInputFile)) @unlink($tempInputFile);
            
            // Fallback: return original PDF if stamping fails
            return is_file($pdfPathOrBytes) ? file_get_contents($pdfPathOrBytes) : $pdfPathOrBytes;
        }
    }

    /**
     * Generate high-resolution PNG image of QR Code Digital Signature Stamp using PHP GD and DNS2D.
     */
    protected function generateSealImage(
        string $signerName,
        string $signedAtDate,
        string $shaHash,
        string $verifyUrl = ''
    ): string {
        $w = 460; // High resolution 2x scale
        $h = 190;

        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Transparent background
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagealphablending($img, true);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $emeraldBorder = imagecolorallocate($img, 5, 150, 105); // #059669
        $emeraldText = imagecolorallocate($img, 6, 95, 70); // #065f46
        $emeraldBg = imagecolorallocate($img, 209, 250, 229); // #d1fae5
        $darkText = imagecolorallocate($img, 15, 23, 42); // #0f172a
        $mutedText = imagecolorallocate($img, 100, 116, 139); // #64748b
        $hashBg = imagecolorallocate($img, 238, 242, 255); // #eef2ff
        $hashText = imagecolorallocate($img, 67, 56, 202); // #4338ca
        $qrBg = imagecolorallocate($img, 248, 250, 252); // #f8fafc
        $qrBorder = imagecolorallocate($img, 226, 232, 240); // #e2e8f0

        // White card background with rounded rectangle
        $this->imagefilledroundedrect($img, 0, 0, $w - 1, $h - 1, 20, $white);
        $this->imageroundedrect($img, 0, 0, $w - 1, $h - 1, 20, $emeraldBorder, 4);

        // 1. Generate & Draw QR Code on Left Side
        $qrService = app(QrGeneratorService::class);
        $targetUrl = !empty($verifyUrl) ? $verifyUrl : $qrService->getVerificationUrl($shaHash);
        
        try {
            $qrPngBase64 = $qrService->generateQrPngBase64($targetUrl, 4, 4);
            $qrImg = imagecreatefromstring(base64_decode($qrPngBase64));
            
            if ($qrImg) {
                // QR Box Background
                $this->imagefilledroundedrect($img, 16, 16, 156, 172, 12, $qrBg);
                $this->imageroundedrect($img, 16, 16, 156, 172, 12, $qrBorder, 2);

                $qrW = imagesx($qrImg);
                $qrH = imagesy($qrImg);
                // Copy resized QR code
                imagecopyresampled($img, $qrImg, 24, 22, 0, 0, 124, 124, $qrW, $qrH);
                imagedestroy($qrImg);

                // Text under QR
                imagestring($img, 2, 34, 150, 'Scan Verifikasi', $mutedText);
            }
        } catch (\Throwable $e) {
            Log::warning('QR generation in stamp failed: ' . $e->getMessage());
        }

        // 2. Right Side: Header & Metadata
        $rightX = 172;

        // Checkmark Icon Circle
        imagefilledellipse($img, $rightX + 14, 30, 22, 22, $emeraldBorder);
        $this->drawCheckmark($img, $rightX + 14, 30, $white);

        // Header Text: E-SIGNATURE & VERIFIKASI RSBA
        imagestring($img, 4, $rightX + 32, 22, 'RS BINTANG AMIN', $emeraldText);

        // Divider Line
        imageline($img, $rightX, 50, $w - 20, 50, $emeraldBg);

        // Signer Name
        $signerTrunc = strlen($signerName) > 22 ? substr($signerName, 0, 20) . '..' : $signerName;
        imagestring($img, 5, $rightX, 64, $signerTrunc, $darkText);

        // Date WIB
        $dateStr = str_contains($signedAtDate, 'WIB') ? $signedAtDate : $signedAtDate . ' WIB';
        imagestring($img, 3, $rightX, 98, $dateStr, $mutedText);


        $tempPath = tempnam(sys_get_temp_dir(), 'stamp_img_') . '.png';
        imagepng($img, $tempPath);
        imagedestroy($img);

        return $tempPath;
    }

    protected function imagefilledroundedrect($img, $x1, $y1, $x2, $y2, $radius, $color)
    {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    protected function imageroundedrect($img, $x1, $y1, $x2, $y2, $radius, $color, $thickness = 1)
    {
        imagesetthickness($img, $thickness);
        imageline($img, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
        imageline($img, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
        imageline($img, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
        imageline($img, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagearc($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
        imagearc($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
        imagearc($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
        imagearc($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
        imagesetthickness($img, 1);
    }

    protected function drawCheckmark($img, $cx, $cy, $color)
    {
        imagesetthickness($img, 3);
        imageline($img, $cx - 4, $cy, $cx - 1, $cy + 4, $color);
        imageline($img, $cx - 1, $cy + 4, $cx + 5, $cy - 4, $color);
        imagesetthickness($img, 1);
    }
}

