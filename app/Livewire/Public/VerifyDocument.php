<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratSp3;
use App\Models\SignatureLogs;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use TallStackUi\Traits\Interactions;

/**
 * Livewire Component: Portal Verifikasi QR Code Legalitas Dokumen
 */
class VerifyDocument extends Component
{
    use Interactions;

    public string $hash = '';
    public string $inputHash = '';
    public bool $isValid = false;
    public ?string $documentTypeLabel = '';
    public ?string $nomorSurat = '';
    public ?string $tanggalSurat = '';
    public ?string $namaPegawai = '';
    public ?string $unitKerja = '';
    public ?string $perihal = '';
    public ?string $signedAt = '';
    public array $approvals = [];
    public ?string $validationNote = '';

    public function mount(?string $hash = null)
    {
        if ($hash) {
            $this->hash = trim($hash);
            $this->inputHash = $this->hash;
            $this->verifyDocumentHash();
        }
    }

    public function updatedInputHash()
    {
        $this->hash = trim($this->inputHash);
        $this->verifyDocumentHash();
    }

    public function submitSearch()
    {
        $this->hash = trim($this->inputHash);
        $this->verifyDocumentHash();
    }

    public function verifyDocumentHash()
    {
        if (empty($this->hash)) {
            $this->isValid = false;
            return;
        }

        if (str_contains($this->hash, '/verifikasi-surat/')) {
            $this->hash = last(explode('/verifikasi-surat/', $this->hash));
        }

        // 1. Cari pada Surat Cuti
        $suratCuti = SuratCuti::where('qr_hash', $this->hash)->first();

        if (!$suratCuti) {
            foreach (SuratCuti::all() as $c) {
                $calc = hash('sha256', 'cuti-' . $c->id . '-' . config('app.key'));
                if ($calc === $this->hash) {
                    $c->qr_hash = $this->hash;
                    $c->save();
                    $suratCuti = $c;
                    break;
                }
            }
        }

        if ($suratCuti) {
            $this->documentTypeLabel = 'Surat Izin Cuti';
            $this->nomorSurat = $suratCuti->no_surat;
            $this->tanggalSurat = $suratCuti->tgl_surat;
            $this->namaPegawai = $suratCuti->karyawan?->nama ?? '-';
            $this->unitKerja = $suratCuti->karyawan?->ruangan?->nama ?? $suratCuti->karyawan?->unitKerja?->nama ?? 'RSBA';
            $this->perihal = 'Pengajuan Cuti ' . ucfirst(optional($suratCuti->jenis)->nama ?? $suratCuti->urgensi ?? 'Tahunan') . ' (' . $suratCuti->lama_cuti . ' hari)';
            $this->signedAt = $suratCuti->signed_at ?? $suratCuti->created_at;

            $this->approvals = $suratCuti->approvals->map(function ($app) {
                $signerName = $app->karyawan?->nama ?? User::where('karyawan_id', $app->disetujui_oleh)->first()?->name ?? 'Pejabat Penandatangan';
                return [
                    'nama' => $signerName,
                    'jabatan' => $app->karyawan?->jabatan?->first()?->nama ?? 'Pejabat Penandatangan',
                    'status' => is_object($app->status) ? $app->status->nama() : (string)$app->status,
                    'approved_at' => $app->approved_at ?? $app->updated_at,
                ];
            })->toArray();

            $this->isValid = $this->customValidationCheck($suratCuti);

            $this->logScanEvent('surat_cuti', $suratCuti->id);
            return;
        }

        // 2. Cari pada Surat SP3 / Keuangan
        $suratSp3 = SuratSp3::where('qr_hash', $this->hash)->first();

        if (!$suratSp3) {
            foreach (SuratSp3::all() as $s) {
                $calc = hash('sha256', 'sp3-' . $s->id . '-' . config('app.key'));
                if ($calc === $this->hash) {
                    $s->qr_hash = $this->hash;
                    $s->save();
                    $suratSp3 = $s;
                    break;
                }
            }
        }

        if ($suratSp3) {
            $this->documentTypeLabel = 'Surat SP3 / Keuangan';
            $this->nomorSurat = (string) $suratSp3->no;
            $this->tanggalSurat = $suratSp3->tgl;
            $this->namaPegawai = $suratSp3->penyetuju?->nama ?? '-';
            $this->unitKerja = $suratSp3->jabatan ?? 'RSBA';
            $this->perihal = 'SP3 / Pembayaran Rekanan ' . $suratSp3->rekanan;
            $this->signedAt = $suratSp3->signed_at ?? $suratSp3->created_at;

            $this->approvals = $suratSp3->approvals->map(function ($app) {
                $user = $app->users ?? User::find($app->disetujui);
                $signerName = $user?->karyawan?->nama ?? $user?->name ?? $app->penyetuju?->nama ?? 'Pejabat SP3';
                return [
                    'nama' => $signerName,
                    'jabatan' => $user?->karyawan?->jabatan?->first()?->nama ?? $app->jabatan ?? 'Pejabat Penandatangan',
                    'status' => is_object($app->status) ? $app->status->nama() : (string)$app->status,
                    'approved_at' => $app->approved_at ?? $app->created_at,
                ];
            })->toArray();

            $this->isValid = $this->customValidationCheck($suratSp3);

            $this->logScanEvent('surat_sp3', $suratSp3->id);
            return;
        }

        // 3. Cari pada Kuitansi
        $kuitansi = \App\Models\Keuangan\Kuitansi::where('qr_hash', $this->hash)->first();

        if (!$kuitansi) {
            foreach (\App\Models\Keuangan\Kuitansi::all() as $k) {
                $calc = hash('sha256', 'kuitansi-' . $k->id . '-' . config('app.key'));
                if ($calc === $this->hash) {
                    $k->qr_hash = $this->hash;
                    $k->save();
                    $kuitansi = $k;
                    break;
                }
            }
        }

        if ($kuitansi) {
            $this->documentTypeLabel = 'Kuitansi Pembayaran';
            $this->nomorSurat = (string) $kuitansi->nomor;
            $this->tanggalSurat = $kuitansi->tanggal ? $kuitansi->tanggal->format('Y-m-d') : '-';
            $this->namaPegawai = $kuitansi->penerima_nama ?? '-';
            $this->unitKerja = 'Keuangan RSBA';
            $this->perihal = 'Pembayaran: ' . $kuitansi->keterangan . ' (Jumlah: ' . formatRupiah($kuitansi->jumlah) . ')';
            $this->signedAt = $kuitansi->signed_at ?? $kuitansi->created_at;

            $this->approvals = $kuitansi->approvals->map(function ($app) {
                $signerName = $app->disetujuiOleh?->full_nama ?? $app->disetujuiOleh?->nama ?? 'Pejabat Keuangan';
                return [
                    'nama' => $signerName,
                    'jabatan' => $app->disetujuiOleh?->jabatan?->first()?->nama ?? 'Pejabat Keuangan',
                    'status' => is_object($app->status) ? $app->status->nama() : (string)$app->status,
                    'approved_at' => $app->approved_at ?? $app->created_at,
                ];
            })->toArray();

            $this->isValid = $this->customValidationCheck($kuitansi);

            $this->logScanEvent('kuitansi', $kuitansi->id);
            return;
        }

        // 4. Cari pada Surat Disposisi Direktur
        $disposisi = \App\Models\Surat\SuratDisposisi::where('signature_hash', $this->hash)
            ->orWhere('docstore_key', $this->hash)
            ->first();

        if ($disposisi) {
            $this->documentTypeLabel = 'Surat Disposisi Direktur';
            $this->nomorSurat = $disposisi->no_agenda;
            $this->tanggalSurat = $disposisi->tgl_surat ? $disposisi->tgl_surat->format('d F Y') : '-';
            $this->namaPegawai = 'Direktur RS Bintang Amin';
            $this->unitKerja = 'Direksi RS Bintang Amin';
            $this->perihal = 'Disposisi #' . $disposisi->no_agenda . ': ' . $disposisi->perihal;
            $this->signedAt = $disposisi->signed_at ? $disposisi->signed_at->format('d/m/Y H:i') : ($disposisi->created_at ? $disposisi->created_at->format('d/m/Y H:i') : '-');

            $this->approvals = $disposisi->details->map(function ($det) {
                return [
                    'nama' => $det->nama_tujuan,
                    'jabatan' => 'Penerima Disposisi',
                    'status' => $det->status_tindak_lanjut === 'done' ? 'Disetujui / Paraf' : 'Pending',
                    'approved_at' => $det->tgl_paraf ? $det->tgl_paraf->format('d/m/Y H:i') : '-',
                ];
            })->toArray();

            $this->isValid = true;
            $this->logScanEvent('surat_disposisi', $disposisi->id);
            return;
        }

        // 3. Fallback: Cari di signature_logs
        $log = SignatureLogs::where('data_hash', $this->hash)
            ->orWhere('signature', $this->hash)
            ->first();

        if ($log) {
            $this->isValid = true;
            $this->documentTypeLabel = 'Dokumen Legalitas Terdaftar (' . strtoupper($log->sign_type) . ')';
            $this->nomorSurat = 'LOG-' . $log->id;
            $this->tanggalSurat = $log->created_at?->format('Y-m-d');
            $this->namaPegawai = $log->user_sign?->karyawan?->nama ?? $log->user_sign?->name ?? 'Sistem RSBA';
            $this->unitKerja = 'Sistem Informasi RSBA';
            $this->perihal = 'Digital Signature Log Document';
            $this->signedAt = $log->created_at;

            $this->logScanEvent($log->sign_type, $log->sign_id);
            return;
        }

        $this->isValid = false;
    }

    protected function customValidationCheck($documentModel): bool
    {
        $baseValid = (bool) ($documentModel->is_valid ?? true);
        return $baseValid;
    }

    protected function logScanEvent(string $type, int $id)
    {
        try {
            Log::info("Public Verification QR Scan [{$type}:{$id}] Hash: {$this->hash} from IP: " . request()->ip());
        } catch (Exception $e) {
            // ignore
        }
    }

    public function render()
    {
        return view('livewire.public.verify-document')->layout('components.layouts.guest', ['title' => 'Verifikasi Dokumen Resmi RSBA']);
    }
}
