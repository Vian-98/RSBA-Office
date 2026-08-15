<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class PdfStamperService
{
    /**
     * Hard-stamp the Mekari Vault Seal Badge image directly into page 1 of the PDF binary stream.
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
        string $title = ''
    ): string {
        $stampImgPath = $this->generateSealImage($signerName, $signedAtDate, $shaHash);

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

                    // Base stamp badge dimensions (190px width @ 850px page = 46.9mm width)
                    $baseStampWidthMm = 46.9 * ($scalePercent / 100);
                    $baseStampHeightMm = 21.8 * ($scalePercent / 100);

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
     * Generate high-resolution PNG image of Mekari Vault Seal Stamp using PHP GD.
     */
    protected function generateSealImage(string $signerName, string $signedAtDate, string $shaHash): string
    {
        $w = 380; // High resolution 2x scale
        $h = 176;

        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Transparent background
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagealphablending($img, true);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $emeraldBorder = imagecolorallocate($img, 16, 185, 129); // #10b981
        $emeraldText = imagecolorallocate($img, 6, 95, 70); // #065f46
        $emeraldBg = imagecolorallocate($img, 209, 250, 229); // #d1fae5
        $darkText = imagecolorallocate($img, 30, 41, 59); // #1e293b
        $mutedText = imagecolorallocate($img, 100, 116, 139); // #64748b
        $hashBg = imagecolorallocate($img, 238, 242, 255); // #eef2ff
        $hashText = imagecolorallocate($img, 67, 56, 202); // #4338ca

        // White card background with rounded rectangle
        $this->imagefilledroundedrect($img, 0, 0, $w - 1, $h - 1, 20, $white);
        $this->imageroundedrect($img, 0, 0, $w - 1, $h - 1, 20, $emeraldBorder, 4);

        // Top Header Divider Line
        imageline($img, 20, 52, $w - 20, 52, $emeraldBg);

        // Checkmark Icon Circle
        imagefilledellipse($img, 34, 30, 24, 24, $emeraldBorder);
        $this->drawCheckmark($img, 34, 30, $white);

        // Text: SIGNED BY MEKARI VAULT
        imagestring($img, 4, 56, 22, 'SIGNED BY MEKARI VAULT', $emeraldText);

        // Text: Signer Name
        $signerTrunc = strlen($signerName) > 22 ? substr($signerName, 0, 20) . '..' : $signerName;
        imagestring($img, 5, 20, 62, $signerTrunc, $darkText);

        // Text: Date WIB
        $dateStr = str_contains($signedAtDate, 'WIB') ? $signedAtDate : $signedAtDate . ' WIB';
        imagestring($img, 3, 20, 94, $dateStr, $mutedText);

        // SHA Hash pill background
        $shaTrunc = 'SHA: ' . substr($shaHash, 0, 14) . '...';
        $this->imagefilledroundedrect($img, 16, 126, $w - 16, 162, 8, $hashBg);
        imagestring($img, 3, 24, 134, $shaTrunc, $hashText);

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
