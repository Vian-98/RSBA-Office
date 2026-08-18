<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use TallStackUi\Traits\Interactions;
use App\Models\DigitalSignatureDocument;
use App\Services\DocstoreSyncService;

#[Lazy]
class Table extends Component
{
    use WithPagination;
    use Interactions;

    public $search = '';

    // Metadata Popup Modal State
    public $showPrintModal = false;
    public $selectedDocument = null;

    protected $paginationTheme = 'tailwind';

    #[On('document-signed')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openPrintModal($id)
    {
        $this->selectedDocument = DigitalSignatureDocument::with('user')->findOrFail($id);
        $this->showPrintModal = true;
    }

    public function closePrintModal()
    {
        $this->showPrintModal = false;
        $this->selectedDocument = null;
    }

    public function downloadPdf($id, DocstoreSyncService $docstoreSyncService)
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
            $this->toast()->error('Gagal Unduh', 'Berkas PDF resmi tidak ditemukan di Docstore Vault.')->send();
            return null;
        }

        $pdfBytes = base64_decode($pdfBase64);
        $cleanFileName = preg_replace('/[^\w\-\.]/', '_', pathinfo($doc->file_name ?? 'Dokumen_Digital', PATHINFO_FILENAME));
        $filename = $cleanFileName . '_Signed.pdf';

        return response()->streamDownload(function () use ($pdfBytes) {
            echo $pdfBytes;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="w-full bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm animate-pulse">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-700 mt-4">Memuat Komponen Tabel Surat (Lazyload)...</p>
            <p class="text-xs text-slate-400 mt-1">Mengambil data dari vault docstore</p>
        </div>
        HTML;
    }

    public function render()
    {
        $query = DigitalSignatureDocument::with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('document_number', 'like', '%' . $this->search . '%')
                  ->orWhere('docstore_key', 'like', '%' . $this->search . '%');
            });
        }

        $documents = $query->orderBy('id', 'desc')->paginate(12);

        return view('livewire.kepegawaian.digital-signature.table', [
            'documents' => $documents,
        ]);
    }
}
