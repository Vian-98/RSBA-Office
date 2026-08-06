<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\DigitalSignatureDocument;
use App\Services\DigitalSignatureService;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithFileUploads;

    // Form inputs
    public $pdf_file;
    public $title = '';
    public $document_number = '';
    public $keterangan = '';
    public $passphrase = '';

    // Print Modal State
    public $showPrintModal = false;
    public $selectedDocument = null;
    public $docstoreData = null;

    protected $rules = [
        'pdf_file'        => 'required|file|mimes:pdf|max:10240', // Max 10MB PDF
        'title'           => 'required|string|max:255',
        'document_number' => 'required|string|max:100',
        'keterangan'      => 'nullable|string|max:500',
        'passphrase'      => 'nullable|string|max:255',
    ];

    protected $messages = [
        'pdf_file.required' => 'Berkas PDF wajib diunggah.',
        'pdf_file.mimes'    => 'Format berkas harus berupa PDF (.pdf).',
        'pdf_file.max'      => 'Ukuran berkas PDF maksimal 10 MB.',
    ];

    public function mount()
    {
        $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));
    }

    public function saveAndSign(
        DigitalSignatureService $signatureService,
        DocstoreSyncService $docstoreSyncService
    ) {
        $this->validate();

        try {
            $user = Auth::user();
            $realPath = $this->pdf_file->getRealPath();
            $fileName = $this->pdf_file->getClientOriginalName();
            $fileSize = filesize($realPath);

            // 1. Calculate ByteCounter (SHA-256 binary hash of the PDF)
            $byteCounterHash = hash_file('sha256', $realPath);

            // 2. Prepare Digital Signature
            $signatureData = [];
            $signatureHash = null;

            $hasActiveCert = $user->certificate()->where('is_active', 1)->exists();
            if ($hasActiveCert) {
                $signResult = $signatureService->signData(
                    user: $user,
                    data: $byteCounterHash,
                    type: 'digital_signature',
                    id: time(),
                    password: $this->passphrase
                );

                if ($signResult['status']) {
                    $signatureData = [
                        'signature'     => $signResult['signature'],
                        'data_hash'     => $signResult['data_hash'],
                        'original_data' => $byteCounterHash,
                        'public_key'    => optional($user->certificate()->where('is_active', 1)->first())->public_key ?? 'MOCK_PUBLIC_KEY',
                    ];
                    $signatureHash = hash('sha256', $signResult['signature']);
                } else {
                    session()->flash('error', 'Gagal tanda tangan digital: ' . $signResult['message']);
                    return;
                }
            } else {
                // Generasi hash tanda tangan standar jika belum ada p12 sertifikat
                $signatureHash = hash('sha256', 'DS_SIG_' . $byteCounterHash . '_' . time());
                $signatureData = [
                    'signature'     => base64_encode('MOCK_SIG_' . $signatureHash),
                    'data_hash'     => $byteCounterHash,
                    'original_data' => $byteCounterHash,
                    'public_key'    => 'MOCK_PUBLIC_KEY',
                ];
            }

            // 3. Read PDF file contents as Base64 for docstore vault
            $pdfBase64 = base64_encode(file_get_contents($realPath));

            // 4. Save metadata record to local DB office
            $doc = DigitalSignatureDocument::create([
                'user_id'           => $user->id,
                'title'             => $this->title,
                'document_number'   => $this->document_number,
                'file_name'         => $fileName,
                'file_size'         => $fileSize,
                'byte_counter_hash' => $byteCounterHash,
                'signature_hash'    => $signatureHash,
                'status'            => 'signed',
                'keterangan'        => $this->keterangan,
            ]);

            // 5. Send to docstore (Bank Surat & Cryptographic Vault)
            $synced = $docstoreSyncService->syncDigitalSignatureDoc($doc, $pdfBase64, $signatureData);

            // 6. Clean up temporary uploaded file on office server (Do NOT store local PDF)
            if (file_exists($realPath)) {
                @unlink($realPath);
            }

            // Reset form
            $this->reset(['pdf_file', 'title', 'keterangan', 'passphrase']);
            $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));

            if ($synced) {
                session()->flash('success', 'Dokumen PDF berhasil di-sign & terkirim ke Docstore dengan ID: ' . $doc->fresh()->docstore_key);
            } else {
                session()->flash('warning', 'Dokumen PDF berhasil di-sign secara lokal, namun gagal sinkronisasi otomatis ke Docstore.');
            }

        } catch (\Throwable $e) {
            Log::error('Error signing digital signature document: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function openPrintModal($id, DocstoreSyncService $docstoreSyncService)
    {
        $doc = DigitalSignatureDocument::with('user')->findOrFail($id);
        $this->selectedDocument = $doc;

        if ($doc->docstore_key) {
            $this->docstoreData = $docstoreSyncService->fetchFromDocstore($doc->docstore_key);
        } else {
            $this->docstoreData = null;
        }

        $this->showPrintModal = true;
    }

    public function closePrintModal()
    {
        $this->showPrintModal = false;
        $this->selectedDocument = null;
        $this->docstoreData = null;
    }

    public function render()
    {
        $documents = DigitalSignatureDocument::with('user')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.kepegawaian.digital-signature.index', [
            'documents' => $documents,
        ])->layout('layouts.app', ['title' => 'Tanda Tangan Digital']);
    }
}

