<?php

namespace App\Livewire\Gaji\Rekap\Concerns;

use App\Models\Sdm\Jabatan;
use App\Livewire\Gaji\Services\PayrollPeriodService;
use Livewire\Attributes\On;

trait HasPayrollFinalisasiModal
{
    // Finalisasi & SP3 Integration Form Properties
    public bool $isFinalisasiModalOpen = false;
    public string $finalisasiPeriode = '';
    public int $finalisasiKaryawanCount = 0;
    public float $finalisasiTotalPotongan = 0;
    public float $finalisasiTotalGajiBersih = 0;

    public string $formSp3Tgl = '';
    public string $formSp3Bayar = 'trf';
    public ?int $formSp3JabatanId = null;
    public ?int $formSp3VerifikatorKeuanganId = null;

    public array $mengetahuiOptions = [];
    public array $verifikatorKeuanganOptions = [];

    public function mountHasPayrollFinalisasiModal(): void
    {
        $this->loadMengetahuiOptions();
        $this->loadVerifikatorKeuanganOptions();
    }

    public function loadMengetahuiOptions(): void
    {
        $this->mengetahuiOptions = Jabatan::with(['jabatans.karyawan', 'bagian'])
            ->where(function ($query) {
                $query->whereIn('tingkat_id', [1, 2, 3])
                    ->orWhereNull('bagian_id')
                    ->orWhere('nama', 'like', '%direktur%')
                    ->orWhere('nama', 'like', '%wadir%')
                    ->orWhere('nama', 'like', '%kepala%')
                    ->orWhere('nama', 'like', '%kabag%')
                    ->orWhere('nama', 'like', '%kabid%');
            })
            ->get()
            ->map(function ($item) {
                $pejabatName = $item->jabatans->pluck('karyawan.nama')->filter()->first();
                $label = $item->nama . ($pejabatName ? " ({$pejabatName})" : '');
                return [
                    'label' => $label,
                    'value' => $item->id
                ];
            })
            ->values()
            ->toArray();

        // Fallback jika tidak ada filter yang cocok, ambil seluruh jabatan yang ada
        if (empty($this->mengetahuiOptions)) {
            $this->mengetahuiOptions = Jabatan::all()->map(function ($item) {
                return [
                    'label' => $item->nama,
                    'value' => $item->id
                ];
            })->toArray();
        }
    }

    public function loadVerifikatorKeuanganOptions(): void
    {
        $query = \App\Models\Sdm\Karyawan::with(['jabatan.bagian', 'user.roles'])
            ->select('sdm_karyawan.id', 'sdm_karyawan.nama', 'sdm_karyawan.gelar_depan', 'sdm_karyawan.gelar_belakang')
            ->where(function ($q) {
                $q->whereHas('user', function ($uq) {
                    $uq->whereHas('roles', function ($rq) {
                        $rq->whereIn('name', ['Keuangan', 'Wadir-Keuangan', 'Super-Admin']);
                    });
                })
                ->orWhereHas('jabatan', function ($jq) {
                    $jq->whereHas('bagian', fn($bq) => $bq->where('nama', 'like', '%keuangan%')->orWhere('group', 'non_medis'))
                       ->orWhere('sdm_jabatan.nama', 'like', '%keuangan%');
                });
            })
            ->orderBy('sdm_karyawan.nama');

        $this->verifikatorKeuanganOptions = $query->get()->unique('id')->map(function ($k) {
            $jab = $k->jabatan->first();
            $namaBagian = $jab?->bagian?->nama ?? '';
            $desc = ($jab?->nama ?? 'Staf Keuangan') . ($namaBagian ? ' · ' . $namaBagian : '');
            return [
                'value' => $k->id,
                'label' => $k->full_nama . ' (' . $desc . ')',
            ];
        })->values()->toArray();

        // Fallback jika tidak ada, ambil seluruh pegawai
        if (empty($this->verifikatorKeuanganOptions)) {
            $this->verifikatorKeuanganOptions = \App\Models\Sdm\Karyawan::orderBy('nama')->get()->map(function ($k) {
                return [
                    'value' => $k->id,
                    'label' => $k->full_nama,
                ];
            })->values()->toArray();
        }
    }

    #[On('trigger-open-finalisasi-modal')]
    public function openFinalisasiModal(string $periode, int $count, float $potongan, float $gajiBersih): void
    {
        $this->loadMengetahuiOptions();
        $this->loadVerifikatorKeuanganOptions();

        $this->finalisasiPeriode = $periode;
        $this->finalisasiKaryawanCount = $count;
        $this->finalisasiTotalPotongan = $potongan;
        $this->finalisasiTotalGajiBersih = $gajiBersih;

        $this->formSp3Tgl = now()->format('Y-m-d');
        $this->formSp3Bayar = 'trf';

        $this->formSp3JabatanId = null;
        if (!empty($this->mengetahuiOptions)) {
            $dirOpt = collect($this->mengetahuiOptions)->first(function ($opt) {
                return str_contains(strtolower($opt['label']), 'wadir sdm') 
                    || str_contains(strtolower($opt['label']), 'wadir')
                    || str_contains(strtolower($opt['label']), 'direktur');
            });
            if ($dirOpt) {
                $this->formSp3JabatanId = $dirOpt['value'];
            } else {
                $this->formSp3JabatanId = $this->mengetahuiOptions[0]['value'];
            }
        }

        $this->formSp3VerifikatorKeuanganId = null;
        if (!empty($this->verifikatorKeuanganOptions)) {
            $this->formSp3VerifikatorKeuanganId = $this->verifikatorKeuanganOptions[0]['value'];
        }

        $this->isFinalisasiModalOpen = true;
    }

    public function closeFinalisasiModal(): void
    {
        $this->isFinalisasiModalOpen = false;
        $this->finalisasiPeriode = '';
        $this->finalisasiKaryawanCount = 0;
        $this->finalisasiTotalPotongan = 0;
        $this->finalisasiTotalGajiBersih = 0;
        $this->formSp3VerifikatorKeuanganId = null;
    }

    public function submitToReviewPajak(string $periode, PayrollPeriodService $periodService): void
    {
        try {
            $periodService->submitToReviewPajak($periode);
            $this->toast()->success('Berhasil !', 'Payroll periode ' . $periode . ' telah dikirim ke Tim Pajak untuk direview.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function approveByPajak(string $periode, PayrollPeriodService $periodService): void
    {
        try {
            $periodService->approveByPajak($periode);
            $this->toast()->success('Berhasil !', 'Review pajak selesai. Payroll periode ' . $periode . ' telah dikembalikan ke SDM untuk finalisasi.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function rejectByPajak(string $periode, PayrollPeriodService $periodService): void
    {
        try {
            $periodService->rejectByPajak($periode);
            $this->toast()->warning('Ditolak', 'Data gaji dikembalikan ke SDM untuk diperbaiki.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }

    public function submitFinalisasi(PayrollPeriodService $periodService): void
    {
        $this->validate([
            'formSp3Tgl' => 'required|date',
            'formSp3Bayar' => 'required|in:tunai,trf,giro',
            'formSp3JabatanId' => 'required|exists:sdm_jabatan,id',
            'formSp3VerifikatorKeuanganId' => 'required|exists:sdm_karyawan,id',
        ], [
            'formSp3Tgl.required' => 'Tanggal SP3 wajib diisi.',
            'formSp3Bayar.required' => 'Metode pembayaran wajib diisi.',
            'formSp3JabatanId.required' => 'Pejabat menyetujui wajib dipilih.',
            'formSp3VerifikatorKeuanganId.required' => 'Verifikator keuangan wajib dipilih.',
        ]);

        try {
            $periodService->submitFinalisasi(
                $this->finalisasiPeriode,
                $this->formSp3Tgl,
                $this->formSp3Bayar,
                $this->formSp3JabatanId,
                $this->finalisasiKaryawanCount,
                $this->finalisasiTotalGajiBersih,
                $this->formSp3VerifikatorKeuanganId
            );

            $this->closeFinalisasiModal();
            $this->toast()->success('Berhasil !', 'Payroll periode ' . $this->finalisasiPeriode . ' berhasil disetujui & dikunci. Surat SP3 telah dikirim ke sistem persetujuan.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function unlockPeriode(string $periode, PayrollPeriodService $periodService): void
    {
        try {
            $isSuperAdmin = (bool) auth()->user()?->can('unlock-payroll-approved');
            $periodService->unlockPeriode($periode, $isSuperAdmin);
            $this->toast()->success('Berhasil !', 'Kunci payroll periode ' . $periode . ' berhasil dibuka. Status dikembalikan ke draft.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }
}
