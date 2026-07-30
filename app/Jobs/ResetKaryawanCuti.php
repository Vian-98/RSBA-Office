<?php

namespace App\Jobs;

use Throwable;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResetKaryawanCuti implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;


    public int $tries = 3; // percobaan ulang

    public int $timeout = 120; // timout max (detik)
    /**
     * Create a new job instance.
     */
    public function __construct(private readonly ?int $karyawan_id = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Job berjalan: ' . now());
        // hari ini
        $today = Carbon::today();

        // get karyawan tgl masuk hari ini
        $query = Karyawan::query()
            ->whereNotNull('tgl_masuk')
            ->where('tgl_masuk', '<=', $today);


        // if employee selected
        if ($this->karyawan_id) {
            $query->where('id', $this->karyawan_id);
        } else {
            $query->whereMonth('tgl_masuk', $today->month)
                ->whereDay('tgl_masuk', $today->day);
        }

        // get data
        $karyawans =  $query->get();

        // If null
        if ($karyawans->isEmpty()) {
            Log::info(
                '[CutiReset] Tidak ada karyawan yang anniversary hari ini.',
                ['date' => Carbon::parse($today)->toDateString()]
            );
            return;
        }


        // Log Start
        Log::info('[CutiReset] Mulai proses reset cuti.', [
            'date' => Carbon::parse($today)->toDateString(),
            'total_karyawan' => $karyawans->count()
        ]);

        $successCount = 0;
        $failCount    = 0;

        // reset cuti
        foreach ($karyawans as $karyawan) {
            try {
                $karyawan->update(['cuti' => 0]);
                $successCount++;

                Log::info('[cutiReset] Cuti direset.', [
                    'karyawan_id' => $karyawan->id,
                    'nama'        => $karyawan->nama, // sesuaikan nama kolom
                    'tanggal'     => Carbon::parse($today)->toDateString(),
                ]);
            } catch (Throwable $e) {
                $failCount++;
                Log::error('[cutiReset] Gagal reset cuti karyawan.', [
                    'employee_id'   => $karyawan->id,
                    'employee_name' => $karyawan->nam,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        Log::info("[CutiReset] Selesai. Total Reset Sukses: {$successCount}, Gagal Reset : {$failCount}");
    }
}
