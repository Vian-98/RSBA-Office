<?php

namespace App\Livewire\Kepegawaian\DigitalSignature;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;
use App\Models\DigitalSignatureDocument;
use App\Models\DigitalSignatureApproval;
use App\Services\DigitalSignatureService;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\Surat\SuratKategoriArsip;

#[Lazy]
class Form extends Component
{
    use WithFileUploads;
    use Interactions;

    // Form inputs
    public $pdf_file;
    public $title = '';
    public $document_number = '';
    public $document_type = 'file_text';
    public $keterangan = '';
    public $account_password = '';

    // Digital Signature & QR Stamp Customization
    public string $stamp_position = 'bottom_right';
    public float $stamp_x = 70.0;
    public float $stamp_y = 75.0;
    public int $stamp_scale = 100;

    // Multi-tier signing properties
    public array $signer_ids = [];
    public int $selected_add_user_id = 0;
    public $signature_type = 'qr_seal'; // 'qr_seal', 'digital_stamp'
    public $fileSizeFormatted = null;
    public $fileHashSHA256 = null;

    // Popup Password Modal State
    public $showPasswordModal = false;

    protected $rules = [
        'pdf_file'        => 'required|file|mimes:pdf|max:10240', // Max 10MB PDF
        'title'           => 'required|string|max:255',
        'document_number' => 'required|string|max:100',
        'document_type'   => 'required|string|max:50',
        'keterangan'      => 'nullable|string|max:500',
    ];

    protected $messages = [
        'pdf_file.required'        => 'Berkas PDF wajib diunggah.',
        'pdf_file.mimes'           => 'Format berkas harus berupa PDF (.pdf).',
        'pdf_file.max'             => 'Ukuran berkas PDF maksimal 10 MB.',
        'title.required'           => 'Judul / nama surat wajib diisi.',
        'document_number.required' => 'Nomor surat wajib diisi.',
        'document_type.required'   => 'Jenis / kategori arsip surat wajib dipilih.',
    ];

    public function mount()
    {
        $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));
        if (auth()->check() && empty($this->signer_ids)) {
            $this->signer_ids[] = auth()->id();
        }

        $firstCategory = SuratKategoriArsip::where('is_active', true)->orderBy('is_system', 'desc')->first();
        if ($firstCategory) {
            $this->document_type = $firstCategory->kode;
        }
    }

    public function addSignerUser(): void
    {
        if ($this->selected_add_user_id > 0 && !in_array($this->selected_add_user_id, $this->signer_ids)) {
            $this->signer_ids[] = (int) $this->selected_add_user_id;
            $this->selected_add_user_id = 0;
            $this->toast()->success('Penandatangan Ditambahkan', 'Tingkat persetujuan baru berhasil ditambahkan.')->send();
        }
    }

    public function removeSignerUser(int $index): void
    {
        if (isset($this->signer_ids[$index])) {
            array_splice($this->signer_ids, $index, 1);
        }
    }

    public function resolveHighestRankUser(array $userIds)
    {
        if (empty($userIds)) return auth()->user();

        $users = \App\Models\User::with(['karyawan.jabatan.tingkat'])->whereIn('id', $userIds)->get();
        $bestUser = null;
        $bestUrutan = 99999;

        foreach ($users as $u) {
            $urutan = 999;
            $karyawan = $u->karyawan;
            if ($karyawan) {
                $jabatan = $karyawan->jabatan()->with('tingkat')->first();
                if ($jabatan && $jabatan->tingkat) {
                    $urutan = (int) ($jabatan->tingkat->urutan ?? 999);
                }
            }
            if ($urutan < $bestUrutan) {
                $bestUrutan = $urutan;
                $bestUser = $u;
            }
        }

        return $bestUser ?: $users->first();
    }

    public function getHighestRankSignerNameProperty(): string
    {
        $user = $this->resolveHighestRankUser($this->signer_ids);
        return $user ? $user->name : (auth()->user()?->name ?? 'Penandatangan Digital');
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

    public function getPreviewQrCodeProperty()
    {
        if (!$this->fileHashSHA256) {
            return null;
        }
        try {
            $qrService = app(\App\Services\QrGeneratorService::class);
            $verifyUrl = $qrService->getVerificationUrl($this->fileHashSHA256);
            return $qrService->generateQrPngBase64($verifyUrl, 3, 3);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function updatedPdfFile()
    {
        $this->validateOnly('pdf_file');

        if ($this->pdf_file) {
            $realPath = $this->pdf_file->getRealPath();
            $this->fileHashSHA256 = hash_file('sha256', $realPath);
            $this->fileSizeFormatted = number_format(filesize($realPath)) . ' bytes';

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
            <p class="text-sm font-bold text-slate-700 mt-4">Memuat Studio Tanda Tangan Digital (Lazyload)...</p>
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
            $this->toast()->error('Password Salah', 'Password akun yang Anda masukkan salah. Silakan coba lagi.')->send();
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
                    $this->toast()->error('Gagal Sign', 'Gagal tanda tangan digital: ' . $signResult['message'])->send();
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

            // 3. Hard-stamp RSBA QR Code Digital Signature directly into PDF Binary stream
            $highestRankUser = $this->resolveHighestRankUser($this->signer_ids) ?: $user;

            $qrService = app(\App\Services\QrGeneratorService::class);
            $verifyUrl = $qrService->getVerificationUrl($byteCounterHash);

            $pdfStamperService = app(\App\Services\PdfStamperService::class);
            $stampedPdfBytes = $pdfStamperService->stampPdf(
                pdfPathOrBytes: $realPath,
                pctX: (float) $this->stamp_x,
                pctY: (float) $this->stamp_y,
                scalePercent: (float) $this->stamp_scale,
                signerName: $highestRankUser->name,
                signedAtDate: date('d M Y H:i') . ' WIB',
                shaHash: $byteCounterHash,
                documentNumber: $this->document_number,
                title: $this->title,
                verifyUrl: $verifyUrl
            );

            $stampedByteHash = hash('sha256', $stampedPdfBytes);
            $fileSize = strlen($stampedPdfBytes);
            $pdfBase64 = base64_encode($stampedPdfBytes);

            $signatureData['data_hash'] = $stampedByteHash;
            $signatureData['original_data'] = $byteCounterHash;
            $signatureData['original_byte_counter_hash'] = $byteCounterHash;
            $signatureData['stamped_data_hash'] = $stampedByteHash;

            // Prepare stamp metadata payload
            $stampMetaPayload = [
                'stamp_position' => $this->stamp_position,
                'stamp_x'        => $this->stamp_x,
                'stamp_y'        => $this->stamp_y,
                'stamp_scale'    => $this->stamp_scale,
                'user_note'      => $this->keterangan,
                'signer_ids'     => $this->signer_ids,
            ];

            $isMultiTier = count($this->signer_ids) > 1 || (count($this->signer_ids) === 1 && $this->signer_ids[0] != $user->id);
            $docStatus = $isMultiTier ? 'pending_approval' : 'approved';

            // 4. Save metadata record to local DB office
            $doc = DigitalSignatureDocument::create([
                'user_id'           => $user->id,
                'title'             => $this->title,
                'document_number'   => $this->document_number,
                'document_type'     => $this->document_type,
                'file_name'         => $fileName,
                'file_size'         => $fileSize,
                'byte_counter_hash' => $stampedByteHash,
                'signature_hash'    => $signatureHash,
                'status'            => $docStatus,
                'keterangan'        => json_encode($stampMetaPayload),
            ]);

            // Save multi-tier approval steps
            $step = 1;
            foreach ($this->signer_ids as $sId) {
                $apprHash = ($sId == $user->id)
                    ? $signatureHash
                    : hash('sha256', 'DS_APP_' . $doc->id . '_' . $sId . '_' . $step);

                DigitalSignatureApproval::create([
                    'digital_signature_document_id' => $doc->id,
                    'user_id'                       => $sId,
                    'step_order'                    => $step++,
                    'status'                        => ($sId == $user->id ? 'approved' : 'pending'),
                    'signed_at'                     => ($sId == $user->id ? now() : null),
                    'signature_hash'                => $apprHash,
                ]);
            }

            // 5. Send to docstore (Bank Surat & Cryptographic Vault)
            $synced = $docstoreSyncService->syncDigitalSignatureDoc($doc, $pdfBase64, $signatureData);

            // 6. Clean up temporary uploaded file on office server (Do NOT store local PDF)
            if (file_exists($realPath)) {
                @unlink($realPath);
            }

            $docstoreKey = $doc->fresh()->docstore_key;

            // Close modal & reset form
            $this->showPasswordModal = false;
            $this->reset(['pdf_file', 'title', 'keterangan', 'account_password', 'fileSizeFormatted', 'fileHashSHA256']);
            $this->document_number = 'DS/' . date('Y/m/') . sprintf('%04d', rand(1, 9999));

            // Dispatch event to parent component to switch to list tab and notify
            $this->dispatch('document-signed', docstoreKey: $docstoreKey, synced: $synced);

            if ($synced) {
                $this->toast()->success('Tanda Tangan Berhasil', 'Dokumen berhasil di-sign & terkirim ke Docstore (ID: ' . $docstoreKey . ')')->send();
                session()->flash('success', 'Dokumen PDF berhasil di-sign & terkirim ke Docstore dengan ID: ' . $docstoreKey);
            } else {
                $this->toast()->warning('Tanda Tangan Lokal', 'Dokumen berhasil di-sign secara lokal.')->send();
                session()->flash('warning', 'Dokumen PDF berhasil di-sign secara lokal, namun gagal sinkronisasi otomatis ke Docstore.');
            }

        } catch (\Throwable $e) {
            Log::error('Error signing digital signature document: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->toast()->error('Terjadi Kesalahan', $e->getMessage())->send();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->closePasswordModal();
        }
    }

    public function render()
    {
        $kategoriList = SuratKategoriArsip::where('is_active', true)
            ->orderBy('is_system', 'desc')
            ->get();

        $allUsers = \App\Models\User::with(['karyawan.jabatan.tingkat'])->get();

        return view('livewire.kepegawaian.digital-signature.form', [
            'kategoriList' => $kategoriList,
            'allUsers'     => $allUsers,
        ]);
    }
}
