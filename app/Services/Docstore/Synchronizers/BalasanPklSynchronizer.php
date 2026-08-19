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
        $model->loadMissing(['direktur']);
        $payload = $this->buildPayload($model);

        $result = $this->client->postDocument($payload);

        if ($result && ($result['success'] ?? false)) {
            $docstoreKey = $result['docstore_key'] ?? ($result['data']['docstore_key'] ?? null);
            $model->updateQuietly([
                'docstore_key'       => $docstoreKey,
                'docstore_synced_at' => now(),
                'docstore_status'    => 'synced',
            ]);
            $this->client->invalidateCache($docstoreKey);
            return true;
        }

        $model->updateQuietly([
            'docstore_status' => 'failed',
        ]);
        return false;
    }

    public function buildPayload(Model $model): array
    {
        /** @var SuratBalasanPkl $model */
        $statusDoc = $this->mapDocumentStatus($model);

        return [
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
                'direktur_user_id'         => $model->direktur_user_id,
                'nama_direktur'            => optional($model->direktur)->full_nama ?? 'dr. Rachmawati, MPH',
                'nip_direktur'             => optional($model->direktur)->nip ?? '24170002',
            ],
            'signatures' => $this->buildSignatures($model),
        ];
    }

    public function buildSignatures(Model $model): array
    {
        /** @var SuratBalasanPkl $model */
        $signatures = [];

        $userId = $model->direktur_user_id;
        $user = $userId ? User::find($userId) : null;
        $sigLog = SignatureLogs::where('reference_id', $model->id)
            ->where('reference_type', 'balasan_pkl')
            ->latest()
            ->first();

        $cert = $userId ? SignatureCerts::where('user_id', $userId)->where('status', 'active')->first() : null;

        $statusText = $model->status === 'approved' ? 'SIGNED' : ($model->status === 'rejected' ? 'REJECTED' : 'PENDING');

        $signatures[] = [
            'signer_name'    => optional($model->direktur)->full_nama ?? optional($user)->name ?? 'dr. Rachmawati, MPH',
            'signer_role'    => 'Direktur Rumah Sakit',
            'signer_order'   => 1,
            'status'         => $statusText,
            'signed_at'      => $model->approved_at ? $model->approved_at->toIso8601String() : ($model->tgl ? $model->tgl->toIso8601String() : null),
            'signature_hash' => $model->qr_verification_hash ?: ($sigLog->signature_hash ?? null),
            'signature_data' => $sigLog->signature_data ?? null,
            'original_data'  => $sigLog->original_data ?? null,
            'public_key'     => optional($cert)->public_key ?? null,
            'is_manual'      => false,
            'manual_note'    => null,
        ];

        return $signatures;
    }

    protected function mapDocumentStatus(SuratBalasanPkl $model): string
    {
        $statusLower = strtolower($model->status ?? 'approved');
        if (in_array($statusLower, ['rejected', 'ditolak'])) {
            return 'rejected';
        }
        if (in_array($statusLower, ['pending', 'draft', 'proses'])) {
            return 'pending';
        }
        return 'approved';
    }
}
