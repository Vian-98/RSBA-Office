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

    public array $mengetahuiOptions = [];

    public function mountHasPayrollFinalisasiModal(): void
    {
        // Load management Jabatans for SP3
        $this->mengetahuiOptions = Jabatan::whereHas('bagian', function ($query) {
            $query->where('group', 'manajemen');
        })->get()->map(function ($item) {
            return [
                'label' => $item->nama,
                'value' => $item->id
            ];
        })->toArray();
    }

    #[On('trigger-open-finalisasi-modal')]
    public function openFinalisasiModal(string $periode, int $count, float $potongan, float $gajiBersih): void
    {
        $this->finalisasiPeriode = $periode;
        $this->finalisasiKaryawanCount = $count;
        $this->finalisasiTotalPotongan = $potongan;
        $this->finalisasiTotalGajiBersih = $gajiBersih;

        $this->formSp3Tgl = now()->format('Y-m-d');
        $this->formSp3Bayar = 'trf';

        $this->formSp3JabatanId = null;
        if (!empty($this->mengetahuiOptions)) {
            $dirOpt = collect($this->mengetahuiOptions)->first(function ($opt) {
                return str_contains(strtolower($opt['label']), 'direktur utama');
            });
            if ($dirOpt) {
                $this->formSp3JabatanId = $dirOpt['value'];
            } else {
                $this->formSp3JabatanId = $this->mengetahuiOptions[0]['value'];
            }
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
        ], [
            'formSp3Tgl.required' => 'Tanggal SP3 wajib diisi.',
            'formSp3Bayar.required' => 'Metode pembayaran wajib diisi.',
            'formSp3JabatanId.required' => 'Pejabat menyetujui wajib dipilih.',
        ]);

        try {
            $periodService->submitFinalisasi(
                $this->finalisasiPeriode,
                $this->formSp3Tgl,
                $this->formSp3Bayar,
                $this->formSp3JabatanId,
                $this->finalisasiKaryawanCount,
                $this->finalisasiTotalGajiBersih
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
            $isSuperAdmin = (bool) auth()->user()?->hasRole('Super-Admin');
            $periodService->unlockPeriode($periode, $isSuperAdmin);
            $this->toast()->success('Berhasil !', 'Kunci payroll periode ' . $periode . ' berhasil dibuka. Status dikembalikan ke draft.')->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', $e->getMessage())->send();
        }
    }
}
