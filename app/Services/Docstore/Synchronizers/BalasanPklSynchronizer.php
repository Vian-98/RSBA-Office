<?php

namespace App\Services\Docstore\Synchronizers;

use App\Models\Surat\SuratBalasanPkl;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use App\Services\Docstore\Contracts\DocumentSynchronizerInterface;
use App\Services\Docstore\DocstoreClient;
use Illuminate\Database\Eloquent\Model;

class BalasanPklSynchronizer implements DocumentSynchronizerInterface
{
    public function __construct(protected DocstoreClient $client)
    {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof SuratBalasanPkl;
    }

    public function sync(Model $model): bool
    {
        if (!$this->supports($model)) {
            return false;
        }

        /** @var SuratBalasanPkl $model */
        $model->loadMissing(['direktur', 'mahasiswa']);
        $payload = $this->buildPayload($model);

        $result = $this->client->postDocument($payload);

        if ($result && ($result['success'] ?? false)) {
            $docstoreKey = $result['docstore_key'] ?? ($result['data']['docstore_key'] ?? null);
            $model->updateQuietly([
                'docstore_key'       => $docstoreKey,
                'docstore_synced_at' => now(),
            ]);
            if (!empty($docstoreKey)) {
                $this->client->invalidateCache($docstoreKey);
            }
            return true;
        }

        return false;
    }

    public function buildPayload(Model $model): array
    {
        /** @var SuratBalasanPkl $model */
        $statusDoc = $this->mapDocumentStatus($model);

        $mahasiswaList = $model->mahasiswa ? $model->mahasiswa->map(fn($m) => [
            'id'   => $m->id,
            'nama' => $m->nama,
            'npm'  => $m->npm,
        ])->toArray() : [];

        return [
            'document_id'     => $model->id,
            'document_number' => $model->no,
            'document_type'   => 'balasan_pkl',
            'status'          => $statusDoc,
            'docstore_key'    => $model->docstore_key ?: null,
            'content'         => [
                'surat_id'                 => $model->id,
                'no'                       => $model->no,
                'tgl'                      => $model->tgl ? $model->tgl->format('Y-m-d') : null,
                'tujuan_institusi_id'      => $model->tujuan_institusi_id,
                'tujuan_universitas'       => $model->tujuan_universitas,
                'tujuan_nama'              => $model->tujuan_nama,
                'tujuan_alamat'            => $model->tujuan_alamat,
                'nomor_surat_masuk'        => $model->nomor_surat_masuk,
                'tgl_surat_masuk'          => $model->tgl_surat_masuk ? $model->tgl_surat_masuk->format('Y-m-d') : null,
                'prodi'                    => $model->prodi,
                'jumlah_mahasiswa'         => (int) $model->jumlah_mahasiswa,
                'lama_praktik_bulan'       => (int) $model->lama_praktik_bulan,
                'tgl_mulai'                => $model->tgl_mulai ? $model->tgl_mulai->format('Y-m-d') : null,
                'tgl_selesai'              => $model->tgl_selesai ? $model->tgl_selesai->format('Y-m-d') : null,
                'snap_biaya_praktik'       => (float) $model->snap_biaya_praktik,
                'snap_biaya_orientasi'     => (float) $model->snap_biaya_orientasi,
                'snap_nomor_sk'            => $model->snap_nomor_sk,
                'total_biaya_praktik'      => (float) $model->total_biaya_praktik,
                'total_biaya_orientasi'    => (float) $model->total_biaya_orientasi,
                'grand_total_biaya'        => (float) $model->grand_total_biaya,
                'disetujui_oleh'           => $model->disetujui_oleh,
                'nama_direktur'            => optional($model->direktur)->full_nama ?? 'dr. Rachmawati, MPH',
                'nip_direktur'             => optional($model->direktur)->nip ?? '24170002',
                'mahasiswa'                => $mahasiswaList,
            ],
            'signatures' => $this->buildSignatures($model),
        ];
    }

    public function buildSignatures(Model $model): array
    {
        /** @var SuratBalasanPkl $model */
        $signatures = [];

        $karyawanId = $model->disetujui_oleh;
        $user = $karyawanId ? User::where('karyawan_id', $karyawanId)->first() : null;
        $userId = $user?->id;

        $sigLog = SignatureLogs::where('sign_id', $model->id)
            ->where(function ($q) {
                $q->where('sign_type', 'balasan_pkl')
                  ->orWhere('sign_type', 'surat_balasan_pkl');
            })
            ->latest('id')
            ->first();

        $cert = $userId ? SignatureCerts::where('user_id', $userId)->where('is_active', 1)->first() : null;

        $statusVal = $this->mapDocumentStatus($model);
        $statusText = $statusVal === 'approved' ? 'SIGNED' : ($statusVal === 'rejected' ? 'REJECTED' : 'PENDING');

        $signatures[] = [
            'signer_name'    => optional($model->direktur)->full_nama ?? optional($user)->name ?? 'dr. Rachmawati, MPH',
            'signer_role'    => 'Direktur Rumah Sakit',
            'signer_order'   => 1,
            'status'         => $statusText,
            'signed_at'      => $model->signed_at ? $model->signed_at->toIso8601String() : null,
            'signature_hash' => $model->qr_verification_hash ?: ($sigLog->data_hash ?? null),
            'signature'      => $sigLog->signature ?? '',
            'signature_data' => $sigLog->signature ?? '',
            'original_data'  => $sigLog->data ?? ($sigLog->signature ?? ''),
            'public_key'     => optional($cert)->public_key ?? null,
            'is_manual'      => false,
            'manual_note'    => $model->catatan_approval ?? null,
        ];

        return $signatures;
    }

    protected function mapDocumentStatus(SuratBalasanPkl $model): string
    {
        $rawStatus = $model->status;
        $statusStr = is_object($rawStatus) && isset($rawStatus->value) ? $rawStatus->value : (string) $rawStatus;
        $statusLower = strtolower($statusStr);

        if (in_array($statusLower, ['rejected', 'ditolak'])) {
            return 'rejected';
        }
        if (in_array($statusLower, ['approved', 'disetujui', 'signed'])) {
            return 'approved';
        }
        return 'pending';
    }
}
