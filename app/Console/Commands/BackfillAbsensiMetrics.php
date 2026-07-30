<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sdm\JadwalKerjaDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillAbsensiMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:backfill-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-calculate and populate menit_terlambat, menit_pulang_cepat, and menit_overtime for all historical attendance details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting historical metrics backfill for sdm_jadwal_kerja_detail...');

        $totalCount = JadwalKerjaDetail::whereNotNull('status_kehadiran')->count();
        $this->info("Total rows to process: {$totalCount}");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        JadwalKerjaDetail::whereNotNull('status_kehadiran')
            ->with('shift')
            ->chunkById(2000, function ($details) use ($bar) {
                foreach ($details as $row) {
                    $statusVal = $row->status_kehadiran instanceof \App\Enums\StatusKehadiran 
                        ? $row->status_kehadiran->value 
                        : (string) $row->status_kehadiran;

                    $menitTerlambat = 0;
                    $menitPulangCepat = 0;
                    $menitOvertime = 0;

                    // Parse minutes from catatan
                    if ($statusVal === 'terlambat' && $row->catatan) {
                        if (preg_match('/Terlambat (-?\d+) menit/i', $row->catatan, $m)) {
                            $menitTerlambat = abs((int) $m[1]);
                        }
                    } elseif ($statusVal === 'pulang_cepat' && $row->catatan) {
                        if (preg_match('/Pulang cepat (-?\d+) menit/i', $row->catatan, $m)) {
                            $menitPulangCepat = abs((int) $m[1]);
                        }
                    }

                    // Overtime calculation
                    if ($row->absen_masuk_at && $row->absen_keluar_at) {
                        $tanggalObj = Carbon::parse($row->tanggal);
                        $masuk = Carbon::parse($row->absen_masuk_at);
                        $keluar = Carbon::parse($row->absen_keluar_at);

                        if ($row->shift && $row->shift->jam_keluar) {
                            $jamKeluar = Carbon::parse($row->shift->jam_keluar);
                            $targetCheckout = Carbon::parse($tanggalObj->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                            if ($row->shift->lintas_hari || $jamKeluar->lt(Carbon::parse($row->shift->jam_masuk))) {
                                $targetCheckout->addDay();
                            }
                            if ($keluar->gt($targetCheckout)) {
                                $menitOvertime = abs($keluar->diffInMinutes($targetCheckout));
                            }
                        } else {
                            $menitOvertime = abs($keluar->diffInMinutes($masuk));
                        }
                    }

                    // Update metrics if changed
                    if (
                        $row->menit_terlambat !== $menitTerlambat ||
                        $row->menit_pulang_cepat !== $menitPulangCepat ||
                        $row->menit_overtime !== $menitOvertime
                    ) {
                        DB::table('sdm_jadwal_kerja_detail')
                            ->where('id', $row->id)
                            ->update([
                                'menit_terlambat' => $menitTerlambat,
                                'menit_pulang_cepat' => $menitPulangCepat,
                                'menit_overtime' => $menitOvertime,
                            ]);
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Metrics backfill completed successfully!');
    }
}
