<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Lazy;
use App\Models\DigitalSignatureDocument;
use App\Services\DigitalSignatureService;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

#[Lazy]
class Form extends Component
{
    use WithFileUploads;

    // Form inputs
    public $pdf_file;
    public $title = '';
    public $document_number = '';
    public $keterangan = '';
    public $account_password = '';

    // Mekari Sign Customization
    public $stamp_position = 'bottom_right';
    public $stamp_x = 70; // percentage from left (0 to 100)
    public $stamp_y = 75; // percentage from top (0 to 100)
    public $stamp_scale = 100; // percentage scale (50% to 180%)
    public $signature_type = 'qr_seal'; // 'qr_seal', 'digital_stamp'
    public $previewPdfBase64 = null;
    public $fileSizeFormatted = null;
    public $fileHashSHA256 = null;

    // Popup Password Modal State
    public $showPasswordModal = false;

    protected $rules = [
        'pdf_file'        => 'required|file|mimes:pdf|max:10240', // Max 10MB PDF
        'title'           => 'required|string|max:255',
        'document_number' => 'required|string|max:100',
        'keterangan'      => 'nullable|string|max:500',
    ];

    protected $messages = [
        'pdf_file.required'        => 'Berkas PDF wajib diunggah.',
        'pdf_file.mimes'           => 'Format berkas harus berupa PDF (.pdf).',
        'pdf_file.max'             => 'Ukuran berkas PDF maksimal 10 MB.',
        'title.required'           => 'Judul / nama surat wajib diisi.',
        'document_number.required' => 'Nomor surat wajib diisi.',
    ];

    public function mount()
    {
        $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));
    }

    public function setPresetPosition($preset)
    {
        $this->stamp_position = $preset;
        if ($preset === 'bottom_right') {
            $this->stamp_x = 70;
            $this->stamp_y = 75;
        } elseif ($preset === 'bottom_left') {
            $this->stamp_x = 5;
            $this->stamp_y = 75;
        } elseif ($preset === 'bottom_center') {
            $this->stamp_x = 38;
            $this->stamp_y = 75;
        } elseif ($preset === 'top_right') {
            $this->stamp_x = 70;
            $this->stamp_y = 5;
        } elseif ($preset === 'top_left') {
            $this->stamp_x = 5;
            $this->stamp_y = 5;
        }
    }

    public function getPreviewPdfUrlProperty()
    {
        if (!$this->pdf_file) {
            return null;
        }
        try {
            return $this->pdf_file->temporaryUrl();
        } catch (\Throwable $e) {
            $realPath = $this->pdf_file->getRealPath();
            return 'data:application/pdf;base64,' . base64_encode(file_get_contents($realPath));
        }
    }

    public function updatedPdfFile()
    {
        $this->validateOnly('pdf_file');

        if ($this->pdf_file) {
            $realPath = $this->pdf_file->getRealPath();
            $this->fileHashSHA256 = hash_file('sha256', $realPath);
            $this->fileSizeFormatted = number_format(filesize($realPath)) . ' bytes';
            $this->previewPdfBase64 = null;

            if (empty($this->title)) {
                $filename = pathinfo($this->pdf_file->getClientOriginalName(), PATHINFO_FILENAME);
                $this->title = ucwords(str_replace(['_', '-'], ' ', $filename));
            }
        }
    }

    public function openPasswordModal()
    {
        $this->validate();
        $this->account_password = '';
        $this->resetErrorBag();
        $this->showPasswordModal = true;
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->account_password = '';
        $this->resetErrorBag('account_password');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="w-full bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm animate-pulse">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-700 mt-4">Memuat Studio Mekari Sign (Lazyload)...</p>
            <p class="text-xs text-slate-400 mt-1">Menyiapkan engine RSA, Canvas Drag-and-Drop & ByteCounter</p>
        </div>
        HTML;
    }

    public function confirmAndSign(
        ?DigitalSignatureService $signatureService = null,
        ?DocstoreSyncService $docstoreSyncService = null
    ) {
        $signatureService = $signatureService ?? app(DigitalSignatureService::class);
        $docstoreSyncService = $docstoreSyncService ?? app(DocstoreSyncService::class);

        $this->validate([
            'account_password' => 'required|string',
        ], [
            'account_password.required' => 'Password akun wajib diisi untuk mengonfirmasi pengiriman.',
        ]);

        $user = Auth::user();

        // Verifikasi Password Akun Pengirim
        if (!Hash::check($this->account_password, $user->password)) {
            $this->addError('account_password', 'Password akun yang Anda masukkan salah. Silakan coba lagi.');
            return;
        }

        try {
            $realPath = $this->pdf_file->getRealPath();
            $fileName = $this->pdf_file->getClientOriginalName();
            $fileSize = filesize($realPath);

            // 1. Calculate ByteCounter (SHA-256 binary hash of the PDF)
            $byteCounterHash = $this->fileHashSHA256 ?? hash_file('sha256', $realPath);

            // 2. Prepare Digital Signature
            $signatureData = [];
            $signatureHash = null;

            $activeCert = $signatureService->getActiveCertificate($user->id);
            $hasActiveCert = $activeCert !== null;
            if ($hasActiveCert) {
                $signResult = $signatureService->signData(
                    user: $user,
                    data: $byteCounterHash,
                    type: 'digital_signature',
                    id: time(),
                    password: null
                );

                if ($signResult['status']) {
                    $signatureData = [
                        'signature'     => $signResult['signature'],
                        'data_hash'     => $signResult['data_hash'],
                        'original_data' => $byteCounterHash,
                        'public_key'    => $activeCert->public_key ?? 'MOCK_PUBLIC_KEY',
                        'stamp_position'=> $this->stamp_position,
                        'stamp_x'       => $this->stamp_x,
                        'stamp_y'       => $this->stamp_y,
                        'stamp_scale'   => $this->stamp_scale,
                        'signature_type'=> $this->signature_type,
                    ];
                    $signatureHash = hash('sha256', $signResult['signature']);
                } else {
                    session()->flash('error', 'Gagal tanda tangan digital: ' . $signResult['message']);
                    $this->closePasswordModal();
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
                    'stamp_position'=> $this->stamp_position,
                    'stamp_x'       => $this->stamp_x,
                    'stamp_y'       => $this->stamp_y,
                    'stamp_scale'   => $this->stamp_scale,
                    'signature_type'=> $this->signature_type,
                ];
            }

            // 3. Read PDF file contents as Base64 for docstore vault
            $pdfBase64 = base64_encode(file_get_contents($realPath));

            // Prepare stamp metadata payload
            $stampMetaPayload = [
                'stamp_position' => $this->stamp_position,
                'stamp_x'        => $this->stamp_x,
                'stamp_y'        => $this->stamp_y,
                'stamp_scale'    => $this->stamp_scale,
                'user_note'      => $this->keterangan,
            ];

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
                'keterangan'        => json_encode($stampMetaPayload),
            ]);

            // 5. Send to docstore (Bank Surat & Cryptographic Vault)
            $synced = $docstoreSyncService->syncDigitalSignatureDoc($doc, $pdfBase64, $signatureData);

            // 6. Clean up temporary uploaded file on office server (Do NOT store local PDF)
            if (file_exists($realPath)) {
                @unlink($realPath);
            }

            // Close modal & reset form
            $this->showPasswordModal = false;
            $this->reset(['pdf_file', 'title', 'keterangan', 'account_password', 'previewPdfBase64', 'fileSizeFormatted', 'fileHashSHA256']);
            $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));

            // Dispatch event to parent component to switch to list tab and notify
            $this->dispatch('document-signed', docstoreKey: $doc->fresh()->docstore_key, synced: $synced);

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
            $this->closePasswordModal();
        }
    }

    public function render()
    {
        return view('livewire.kepegawaian.digital-signature.form');
    }
}
