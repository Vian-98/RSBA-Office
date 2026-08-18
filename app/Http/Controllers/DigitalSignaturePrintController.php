<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalSignatureDocument;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\Log;

class DigitalSignaturePrintController extends Controller
{
    public function print(int $id, DocstoreSyncService $docstoreSyncService)
    {
        $doc = DigitalSignatureDocument::with('user')->findOrFail($id);

        $pdfBase64 = null;
        if ($doc->docstore_key) {
            $docstoreData = $docstoreSyncService->fetchFromDocstore($doc->docstore_key);
            $content = $docstoreData['document']['content'] 
                ?? $docstoreData['data']['document']['content'] 
                ?? [];
            $pdfBase64 = is_array($content) ? ($content['pdf_base64'] ?? null) : null;
        }

        if (!$pdfBase64) {
            abort(404, 'Berkas PDF tidak ditemukan di Docstore Vault.');
        }

        // Parse stamp metadata
        $stampMeta = json_decode($doc->keterangan ?? '', true);
        $stampX = is_array($stampMeta) && isset($stampMeta['stamp_x']) ? $stampMeta['stamp_x'] : 70;
        $stampY = is_array($stampMeta) && isset($stampMeta['stamp_y']) ? $stampMeta['stamp_y'] : 75;
        $stampScale = is_array($stampMeta) && isset($stampMeta['stamp_scale']) ? $stampMeta['stamp_scale'] : 100;

        $qrService = app(\App\Services\QrGeneratorService::class);
        $verifyUrl = $doc->docstore_key 
            ? $qrService->getDocstoreVerifyUrl($doc->docstore_key) 
            : $qrService->getVerificationUrl($doc->signature_hash ?? $doc->byte_counter_hash);
        $qrBase64 = $qrService->generateQrPngBase64($verifyUrl, 3, 3);

        return view('kepegawaian.digital-signature-print', [
            'document'   => $doc,
            'pdfBase64'  => $pdfBase64,
            'stampX'     => $stampX,
            'stampY'     => $stampY,
            'stampScale' => $stampScale,
            'qrBase64'   => $qrBase64,
        ]);
    }

    public function download(int $id, DocstoreSyncService $docstoreSyncService)
    {
        $doc = DigitalSignatureDocument::findOrFail($id);

        $pdfBase64 = null;
        if ($doc->docstore_key) {
            $docstoreData = $docstoreSyncService->fetchFromDocstore($doc->docstore_key);
            $content = $docstoreData['document']['content'] 
                ?? $docstoreData['data']['document']['content'] 
                ?? [];
            $pdfBase64 = is_array($content) ? ($content['pdf_base64'] ?? null) : null;
        }

        if (!$pdfBase64) {
            abort(404, 'Berkas PDF resmi tidak ditemukan di Docstore Vault.');
        }

        $pdfBytes = base64_decode($pdfBase64);
        $cleanFileName = preg_replace('/[^\w\-\.]/', '_', pathinfo($doc->file_name ?? 'Dokumen_Digital', PATHINFO_FILENAME));
        $filename = $cleanFileName . '_Signed.pdf';

        return response($pdfBytes)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
