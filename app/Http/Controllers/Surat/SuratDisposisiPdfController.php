<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\Surat\SuratDisposisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class SuratDisposisiPdfController extends Controller
{
    /**
     * Download PDF Resmi Surat Disposisi Direktur dari Database/Template.
     */
    public function download(int $id)
    {
        $disposisi = SuratDisposisi::with(['details', 'direktur'])->findOrFail($id);

        $pdf = Pdf::loadView('livewire.surat.disposisi.print', compact('disposisi'))
            ->setPaper('a4', 'portrait');

        $safeAgenda = str_replace(['/', '\\', ' '], '_', $disposisi->no_agenda);
        $fileName = 'Surat_Disposisi_' . $safeAgenda . '.pdf';

        return $pdf->download($fileName);
    }
}
