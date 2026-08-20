<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Perusahaan;
use App\Services\QrGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SuratBalasanPklPdfController extends Controller
{
    /**
     * Helper untuk membuat data QR Code Base64
     */
    protected function getQrCodeBase64(SuratBalasanPkl $surat): ?string
    {
        $qrService = app(QrGeneratorService::class);

        if (!empty($surat->docstore_key)) {
            return $qrService->generateDocstoreQr($surat->docstore_key, 4, 4);
        }

        if (!empty($surat->qr_hash)) {
            return $qrService->generateQrPngBase64($qrService->getVerificationUrl($surat->qr_hash), 4, 4);
        }

        return null;
    }

    /**
     * Download PDF Surat Balasan PKL & Lampiran
     */
    public function download(int $id)
    {
        $surat = SuratBalasanPkl::with(['mahasiswa', 'direktur', 'jabatan'])->findOrFail($id);
        $qrBase64 = $this->getQrCodeBase64($surat);

        $pdf = Pdf::loadView('pdf.surat-balasan-pkl', [
            'surat' => $surat,
            'rs' => Perusahaan::first(),
            'qrBase64' => $qrBase64,
        ])->setPaper('a4', 'portrait');

        $filename = "Surat-Balasan-PKL-{$surat->no_clean}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Stream / Preview PDF di tab browser baru
     */
    public function stream(int $id)
    {
        $surat = SuratBalasanPkl::with(['mahasiswa', 'direktur', 'jabatan'])->findOrFail($id);
        $qrBase64 = $this->getQrCodeBase64($surat);

        $pdf = Pdf::loadView('pdf.surat-balasan-pkl', [
            'surat' => $surat,
            'rs' => Perusahaan::first(),
            'qrBase64' => $qrBase64,
        ])->setPaper('a4', 'portrait');

        $filename = "Surat-Balasan-PKL-{$surat->no_clean}.pdf";

        return $pdf->stream($filename);
    }
}
