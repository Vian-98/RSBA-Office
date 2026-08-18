<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\Surat\SuratBalasanPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SuratBalasanPklPdfController extends Controller
{
    /**
     * Download PDF Surat Balasan PKL & Lampiran
     */
    public function download(int $id)
    {
        $surat = SuratBalasanPkl::with(['mahasiswa', 'direktur', 'jabatan'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat-balasan-pkl', [
            'surat' => $surat,
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

        $pdf = Pdf::loadView('pdf.surat-balasan-pkl', [
            'surat' => $surat,
        ])->setPaper('a4', 'portrait');

        $filename = "Surat-Balasan-PKL-{$surat->no_clean}.pdf";

        return $pdf->stream($filename);
    }
}
