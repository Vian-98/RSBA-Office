<?php

namespace App\Livewire\Surat\Sp3;

use Livewire\Component;
use Milon\Barcode\DNS2D;
use App\Models\Surat\SuratSp3;
use App\Services\QrGeneratorService;
use App\Services\DocumentSignatureService;
use App\Services\DocstoreSyncService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class PrintSp3 extends Component
{
    public $suratSp3;

    /**
     * Data surat dari docstore (bank surat — source of truth).
     */
    public ?array $docstoreData = null;

    /**
     * Apakah data berhasil diambil dari docstore.
     */
    public bool $fromDocstore = false;

    /**
     * Pesan error jika docstore tidak dapat diakses.
     */
    public ?string $docstoreError = null;

    public function mount(?SuratSp3 $suratSp3)
    {
        $this->suratSp3 = $suratSp3;
        $this->loadFromDocstore();
    }

    #[On('update-approval')]
    public function refreshData()
    {
        $this->suratSp3 = SuratSp3::find($this->suratSp3->id);

        // Invalidate cache dan reload dari docstore
        if (!empty($this->suratSp3->docstore_key)) {
            app(DocstoreSyncService::class)->invalidateCache($this->suratSp3->docstore_key);
        }
        $this->loadFromDocstore();
    }

    /**
     * Load data dari docstore.
     * Print WAJIB dari docstore — jika docstore tidak tersedia, tampilkan error.
     */
    protected function loadFromDocstore(): void
    {
        $this->fromDocstore = false;
        $this->docstoreError = null;
        $this->docstoreData = null;

        if (!$this->suratSp3 || empty($this->suratSp3->docstore_key)) {
            $this->docstoreError = 'Surat ini belum tersinkronisasi ke bank surat (docstore). '
                . 'Lakukan approval terlebih dahulu atau hubungi administrator.';
            return;
        }

        try {
            $syncService = app(DocstoreSyncService::class);
            $data = $syncService->fetchFromDocstore($this->suratSp3->docstore_key);

            if ($data && ($data['success'] ?? false)) {
                $this->docstoreData = $data;
                $this->fromDocstore = true;
            } else {
                $this->docstoreError = 'Gagal mengambil data dari bank surat. '
                    . 'Pastikan server docstore aktif dan coba lagi.';
            }
        } catch (\Throwable $e) {
            $this->docstoreError = 'Koneksi ke bank surat gagal: ' . $e->getMessage();
        }
    }

    #[Computed]
    function approvals(): array
    {
        // Ambil dari docstoreData jika tersedia (data lebih terpercaya)
        if ($this->fromDocstore && !empty($this->docstoreData['all_signatures'])) {
            return array_map(function ($sig) {
                return [
                    'status'      => $sig['status'],
                    'nama'        => $sig['signer_name'],
                    'jabatan'     => $sig['signer_role'],
                    'approved_at' => $sig['signed_at'],
                    'signature'   => $sig['signature_hash'],
                    'is_manual'   => false,
                ];
            }, $this->docstoreData['all_signatures']);
        }

        // Fallback ke data lokal
        return $this->suratSp3->approvals->map(function ($item): array {
            $isManual = $item->status === \App\Enums\StatusApproval::MANUAL
                || str_contains(strtolower($item->keterangan ?? ''), 'manual');
            $tahapLabel = is_object($item->tahap) ? $item->tahap->nama() : ($item->tahap === 'verifikasi_keuangan' ? 'Verifikasi Keuangan' : 'Tanda Tangan Atasan');
            return [
                'tahap'       => $tahapLabel,
                'status'      => $isManual ? 'Manual' : (is_object($item->status) ? $item->status->nama() : ucfirst($item->status)),
                'nama'        => $item->users->karyawan->full_nama ?? $item->users->nama,
                'jabatan'     => $item->users->karyawan?->jabatan ?? null,
                'approved_at' => $item->approved_at,
                'signature'   => $item->signature_hash,
                'is_manual'   => $isManual,
            ];
        })->toArray();
    }

    /**
     * Generate QR Code untuk surat.
     *
     * Prioritas QR:
     * 1. docstore_key (UUID) → QR berisi URL verify app dengan docstore_key
     *    Scan QR → verify app → fetch data langsung dari docstore
     * 2. qr_hash (SHA256) → fallback ke hash lokal
     */
    #[Computed]
    public function generateHeaderQrCode()
    {
        $qrService = app(QrGeneratorService::class);

        // Prioritas: gunakan docstore_key untuk QR
        if (!empty($this->suratSp3->docstore_key)) {
            return $qrService->generateDocstoreQr($this->suratSp3->docstore_key, 4, 4);
        }

        // Fallback: generate qr_hash dari sistem PKCS12
        $docSignService = app(DocumentSignatureService::class);
        $p12Hash = $docSignService->ensureP12SystemSignature($this->suratSp3);
        return $qrService->generateQrPngBase64($p12Hash, 4, 4);
    }

    #[Computed]
    public function ttdAtasan(): ?array
    {
        $sigs = $this->approvals();
        $atasanSig = collect($sigs)->first(function ($s) {
            $tahap = strtolower($s['tahap'] ?? '');
            return str_contains($tahap, 'atasan') || str_contains($tahap, 'direktur') || empty($tahap);
        });

        if (!$atasanSig && !empty($sigs)) {
            $first = $sigs[0];
            if (!str_contains(strtolower($first['tahap'] ?? ''), 'keuangan')) {
                $atasanSig = $first;
            }
        }

        return $atasanSig;
    }

    #[Computed]
    public function generateBarcode()
    {
        $ttd = $this->ttdAtasan();
        $ttdSig = $ttd['signature'] ?? null;

        if (empty($ttdSig) || $ttdSig === 'PENDING_APPROVAL' || str_starts_with($ttdSig, 'pending_') || str_starts_with($ttdSig, 'REJECTED_')) {
            return '';
        }

        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($ttdSig, 'QRCODE');
    }



    /**
     * Apakah print dapat dilakukan.
     */
    #[Computed]
    public function canPrint(): bool
    {
        return $this->fromDocstore && !empty($this->docstoreData);
    }

    public function render()
    {
        return view('livewire.surat.sp3.print-sp3');
    }
}
