<?php

namespace App\Services\Docstore\Synchronizers;

use App\Models\Surat\SuratPerintahTugas;
use App\Models\SignatureLogs;
use App\Models\SignatureCerts;
use App\Models\User;
use App\Services\Docstore\Contracts\DocumentSynchronizerInterface;
use App\Services\Docstore\DocstoreClient;
use Illuminate\Database\Eloquent\Model;

class PerintahTugasSynchronizer implements DocumentSynchronizerInterface
{
    public function __construct(protected DocstoreClient $client)
    {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof SuratPerintahTugas;
    }

    public function sync(Model $model): bool
    {
        if (!$this->supports($model)) {
            return false;
        }

        /** @var SuratPerintahTugas $model */
        $model->loadMissing(['direktur', 'karyawanTugas']);
        $payload = $this->buildPayload($model);

        $result = $this->client->postDocument($payload);

        if ($result && ($result['success'] ?? false)) {
            $docstoreKey = $result['docstore_key'] ?? ($result['data']['docstore_key'] ?? null);
            $model->updateQuietly([
                'docstore_key'       => $docstoreKey,
                'docstore_synced_at' => now(),
                'docstore_status'    => 'synced',
            ]);
            if (!empty($docstoreKey)) {
                $this->client->invalidateCache($docstoreKey);
            }
            return true;
        }

        $model->updateQuietly([
            'docstore_status' => 'failed',
        ]);
        return false;
    }

    public function buildPayload(Model $model): array
    {
        /** @var SuratPerintahTugas $model */
        $statusDoc = $this->mapDocumentStatus($model);

        $karyawanList = $model->karyawanTugas ? $model->karyawanTugas->map(fn($k) => [
            'id'      => $k->id,
            'nama'    => $k->nama,
            'nip'     => $k->nip,
            'jabatan' => $k->jabatan_nama,
        ])->toArray() : [];

        return [
            'document_number' => $model->no,
            'document_type'   => 'perintah_tugas',
            'status'          => $statusDoc,
            'docstore_key'    => $model->docstore_key ?: null,
            'content'         => [
                'surat_id'       => $model->id,
                'no'             => $model->no,
                'tgl'            => $model->tgl ? $model->tgl->format('Y-m-d') : null,
                'perihal'        => $model->perihal,
                'hari_tanggal'   => $model->hari_tanggal,
                'waktu'          => $model->waktu,
                'tempat'         => $model->tempat,
                'disetujui_oleh' => $model->disetujui_oleh,
                'nama_direktur'  => optional($model->direktur)->full_nama ?? 'dr. Rachmawati, MPH',
                'nip_direktur'   => optional($model->direktur)->nip ?? '24170002',
                'karyawan'       => $karyawanList,
            ],
            'signatures' => $this->buildSignatures($model),
        ];
    }

    public function buildSignatures(Model $model): array
    {
        /** @var SuratPerintahTugas $model */
        $signatures = [];

        $karyawanId = $model->disetujui_oleh;
        $user = $karyawanId ? User::where('karyawan_id', $karyawanId)->first() : null;
        $userId = $user?->id;

        $sigLog = SignatureLogs::where('sign_id', $model->id)
            ->where(function ($q) {
                $q->where('sign_type', 'perintah_tugas')
                  ->orWhere('sign_type', 'surat_perintah_tugas');
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
            'signature'      => $sigLog->signature ?? null,
            'signature_data' => $sigLog->signature ?? null,
            'original_data'  => $sigLog->data ?? null,
            'public_key'     => optional($cert)->public_key ?? null,
            'is_manual'      => false,
            'manual_note'    => $model->catatan_approval ?? null,
        ];

        return $signatures;
    }

    protected function mapDocumentStatus(SuratPerintahTugas $model): string
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
