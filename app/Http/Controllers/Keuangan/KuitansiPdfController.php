<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\Kuitansi;
use App\Models\Perusahaan;
use App\Services\QrGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;

class KuitansiPdfController extends Controller
{
    public function exportPdf(int $id)
    {
        abort_unless(auth()->user()->can('print-keuangan-kuitansi') || auth()->user()->hasRole('Super-Admin'), 403);

        $kuitansi = Kuitansi::with(['createdBy.karyawan', 'penerima.jabatan', 'approvals.disetujuiOleh.jabatan', 'details'])->findOrFail($id);
        $perusahaan = Perusahaan::first();

        $logoSrc = null;
        $logoPath = null;
        if ($perusahaan && $perusahaan->logo && file_exists(storage_path('app/public/' . $perusahaan->logo))) {
            $logoPath = storage_path('app/public/' . $perusahaan->logo);
        } elseif (file_exists(public_path('logo-fallback.png'))) {
            $logoPath = public_path('logo-fallback.png');
        }

        if ($logoPath && file_exists($logoPath)) {
            $mime = mime_content_type($logoPath);
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:' . $mime . ';base64,' . $logoData;
        }

        // Generate QR code for document
        $qrBase64 = null;
        $qrService = app(QrGeneratorService::class);
        if (!empty($kuitansi->docstore_key)) {
            $qrBase64 = $qrService->generateDocstoreQr($kuitansi->docstore_key, 3, 3);
        } elseif (!empty($kuitansi->qr_hash)) {
            $qrBase64 = $qrService->generateQrPngBase64($kuitansi->qr_hash, 3, 3);
        } else {
            $docSignService = app(\App\Services\DocumentSignatureService::class);
            $p12Hash = $docSignService->ensureP12SystemSignature($kuitansi);
            $qrBase64 = $qrService->generateQrPngBase64($p12Hash, 3, 3);
        }


        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ])->loadView('pdf.kuitansi', compact('kuitansi', 'perusahaan', 'logoSrc', 'qrBase64'))
            ->setPaper('a5', 'landscape');

        $sanitizedNomor = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kuitansi->nomor);
        return $pdf->stream("kuitansi-{$sanitizedNomor}.pdf");
    }
}
