<?php

namespace App\Console\Commands;

use App\Jobs\ResetKaryawanCuti;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetCutiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:reset
                            {--employee= : ID Karyawan tertentu(opsional)}
                            {--dry-run   : Simulasi tanpa mengubah data}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset cuti karyawan berdasarkan tanggal masuk kerja';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $karyawan_id = $this->option('employee');
        $isDryRun = $this->option('dry-run');


        if ($isDryRun) {
            $this->warn('⚠️ Mode DRY RUN aktif — tidak ada data yang akan diubah.');
        }

        // tampilkan karyawan yang akan di proses
        $query = Karyawan::query()
            ->whereNotNull('tgl_masuk')
            ->where('tgl_masuk', '<=', $today);


        if ($karyawan_id) {
            $query->where('id', $karyawan_id);
        } else {
            $query->whereMonth('tgl_masuk', $today->month)
                ->whereDay('tgl_masuk', $today->day);
        }


        // karyawans get
        $karyawans = $query->get();

        if ($karyawans->isEmpty()) {
            $this->info('✅ Tidak ada karyawan yang perlu direset hari ini.');
            return self::SUCCESS;
        }


        // ada karyawan
        $this->info("👥 Ditemukan {$karyawans->count()} karyawan:");
        $this->table(
            ['ID', 'Nama', 'Tanggal Masuk', 'Cuti Terpakai'],
            $karyawans->map(fn(Karyawan $emp) => [
                $emp->id,
                $emp->nama,
                Carbon::parse($emp->tgl_masuk)->toDateString(),
                $emp->cuti
            ]),
        );


        // if dry-run , return;
        if ($isDryRun) {
            $this->warn('🔍 Dry run selesai. Tidak ada perubahan data.');
            return self::SUCCESS;
        }


        // Next Proses
        // if (!$this->confirm('Lanjutkan proses reset cuti?', true)) {
        //     $this->info('Dibatalkan.');
        //     return self::SUCCESS;
        // }
        $shouldContinue = $this->input->isInteractive() ? $this->confirm('Lanjutkan proses reset cuti ?', true) : true;
        if (!$shouldContinue) {
            $this->info('Dibatalkan');
            return self::SUCCESS;
        }


        if ($karyawan_id) {
            # code...
            ResetKaryawanCuti::dispatch((int) $karyawan_id);
            $this->info("✅ Job reset cuti untuk Employee ID #{$karyawan_id} telah di-dispatch ke queue.");
        } else {
            ResetKaryawanCuti::dispatch();
            $this->info('✅ Job reset cuti telah di-dispatch ke queue.');
        }


        if ($this->input->isInteractive()) {
            $this->info('💡 Jalankan: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
