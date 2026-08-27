<?php

namespace App\Livewire\Surat\Sp3;

use Livewire\Component;
use Milon\Barcode\DNS2D;
use App\Enums\StatusApproval;
use App\Enums\TahapApprovalSp3;
use Livewire\Attributes\Lazy;
use App\Models\Surat\SuratSp3;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use App\Models\Surat\SuratSp3Detail;

#[Lazy]
class Details extends Component
{
    #[Locked]
    public ?SuratSp3 $suratSp3;

    public $headers = [
        ['index' => 'keterangan', 'label' => 'Keterangan'],
        ['index' => 'nominal', 'label' => 'Nominal'],
    ];

    public function mount($suratSp3)
    {
        $this->suratSp3 = $suratSp3->load(['details', 'approvals.users.karyawan', 'logs.user.karyawan', 'jabatans', 'verifikatorKeuangan']);
    }

    #[Computed]
    public function rows()
    {
        return $this->suratSp3->details->map(function ($detail) {
            return [
                'keterangan' => $detail->keterangan,
                'nominal'    => formatRupiah($detail->nominal, true, false),
            ];
        });
    }

    /**
     * Tanda tangan Atasan / Direktur (Format SP3 Klasik).
     * Verifikasi Keuangan tidak ditampilkan sebagai QR tanda tangan kedua di format fisik SP3.
     */
    #[Computed]
    public function ttdAtasan()
    {
        $barcode = new DNS2D();

        $item = $this->suratSp3->approvals
            ->first(function ($appr) {
                $tahap = is_object($appr->tahap) ? $appr->tahap->value : $appr->tahap;
                return $tahap === 'ttd_atasan' || $tahap === TahapApprovalSp3::TTD_ATASAN->value || empty($tahap);
            });

        if (!$item) {
            return null;
        }

        $isManual = $item->status === StatusApproval::MANUAL || str_contains(strtolower($item->keterangan ?? ''), 'manual');
        $sigBarcode = null;
        if ($item->signature_hash) {
            $sigBarcode = $barcode->getBarcodePNG($item->signature_hash, 'QRCODE');
        }

        return [
            'status'      => $isManual ? 'Manual' : (is_object($item->status) ? $item->status->nama() : ucfirst($item->status)),
            'nama'        => $item->users?->karyawan?->full_nama ?? $item->users?->karyawan?->nama ?? $item->users?->name ?? 'Pejabat',
            'jabatan'     => optional($this->suratSp3->jabatans)->nama ?? optional($item->users?->karyawan?->jabatan?->first())->nama ?? 'Atasan',
            'approved_at' => $item->approved_at,
            'signature'   => $item->signature_hash,
            'barcode'     => $sigBarcode,
            'is_manual'   => $isManual,
        ];
    }

    /**
     * Riwayat / Log Persetujuan Lengkap
     */
    #[Computed]
    public function logs()
    {
        $dbLogs = $this->suratSp3->logs;
        if ($dbLogs && $dbLogs->isNotEmpty()) {
            return $dbLogs;
        }

        // Fallback untuk data lama sebelum ada tabel logs: buat representasi log dari approvals
        $syntheticLogs = collect();

        // Log pembuatan
        $syntheticLogs->push((object)[
            'created_at'     => $this->suratSp3->created_at,
            'nama_pelaku'    => $this->suratSp3->dibuatOleh ?? 'Pembuat SP3',
            'jabatan_pelaku' => 'Pembuat Surat',
            'aksi'           => 'Dibuat',
            'status'         => 'pending',
            'catatan'        => 'Surat SP3 dibuat dan diteruskan ke Bagian Keuangan.',
        ]);

        foreach ($this->suratSp3->approvals as $approval) {
            $tahap = is_object($approval->tahap) ? $approval->tahap->value : $approval->tahap;
            $statusVal = is_object($approval->status) ? $approval->status->value : $approval->status;
            $nama = $approval->users?->karyawan?->full_nama ?? $approval->users?->name ?? 'Pejabat';

            if ($tahap === 'verifikasi_keuangan' || $tahap === TahapApprovalSp3::VERIFIKASI_KEUANGAN->value) {
                $isAppr = $statusVal === 'approved';
                $syntheticLogs->push((object)[
                    'created_at'     => $approval->created_at ?? $approval->approved_at,
                    'nama_pelaku'    => $nama,
                    'jabatan_pelaku' => 'Verifikator Keuangan',
                    'aksi'           => $isAppr ? 'Verifikasi Keuangan - Disetujui' : 'Verifikasi Keuangan - Ditolak',
                    'status'         => $statusVal,
                    'catatan'        => $approval->keterangan ?: ($isAppr ? 'Diverifikasi dan diteruskan ke Direktur / Atasan.' : 'Ditolak.'),
                ]);
            } else {
                $syntheticLogs->push((object)[
                    'created_at'     => $approval->created_at ?? $approval->approved_at,
                    'nama_pelaku'    => $nama,
                    'jabatan_pelaku' => optional($this->suratSp3->jabatans)->nama ?? 'Atasan / Direktur',
                    'aksi'           => 'ACC Direktur / Atasan - ' . ucfirst($statusVal),
                    'status'         => $statusVal,
                    'catatan'        => $approval->keterangan ?: ($statusVal === 'approved' ? 'Surat SP3 disetujui (ACC).' : null),
                ]);
            }
        }

        return $syntheticLogs;
    }

    #[Computed]
    public function generateBarcode()
    {
        $ttdAtasan = $this->suratSp3->approvals->firstWhere('tahap', TahapApprovalSp3::TTD_ATASAN);
        $sig = $ttdAtasan?->signature_hash ?? $this->suratSp3->qr_hash ?? $this->suratSp3->approvals->first()?->signature_hash;

        if (!$sig) {
            return '';
        }

        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($sig, 'QRCODE');
    }

    public function render()
    {
        return view('livewire.surat.sp3.details');
    }
}
