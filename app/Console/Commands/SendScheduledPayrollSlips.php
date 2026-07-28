<?php

namespace App\Console\Commands;

use App\Mail\SlipGajiMail;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\PayrollSendLog;
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendScheduledPayrollSlips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:send-scheduled-slips
                            {--force    : Abaikan verifikasi jadwal tanggal/jam & status terkunci}
                            {--dry-run  : Simulasi tanpa mengirim email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim slip gaji email otomatis secara teratur dan terkontrol (chunking)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', 0);
        $today = Carbon::today();

        $periode = $today->format('Y-m');
        $isForce = (bool) $this->option('force');
        $isDryRun = (bool) $this->option('dry-run');

        // Check settings
        $enabled = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_enabled')->value('value') === '1';
        $configuredDay = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_day')->value('value') ?: '25';
        $configuredTime = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_time')->value('value') ?: '08:00';
        $chunkSize = (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_chunk_size')->value('value') ?: 10);
        $delaySeconds = (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_delay_seconds')->value('value') ?: 3);

        if (!$enabled && !$isForce) {
            $this->info('ℹ️ Fitur pengiriman otomatis slip gaji tidak aktif.');
            return self::SUCCESS;
        }

        // Verify Day of Month
        $currentDay = (string) $today->day;
        $isTargetDay = false;

        if ($configuredDay === 'last_day') {
            $isTargetDay = $today->isLastOfMonth();
        } else {
            $isTargetDay = ($currentDay === (string) $configuredDay);
        }

        if (!$isTargetDay && !$isForce) {
            $this->info("ℹ️ Hari ini (Tanggal {$currentDay}) bukan jadwal pengiriman (Jadwal: Tanggal {$configuredDay}).");
            return self::SUCCESS;
        }

        // Verify Execution Time (HH:MM)
        $currentTime = Carbon::now()->format('H:i');
        if ($configuredTime && $currentTime !== $configuredTime && !$isForce) {
            return self::SUCCESS;
        }


        // Verify Lock Status
        $lockRecord = DB::table('sdm_payroll_period_locks')
            ->where('periode', $periode)
            ->first();

        $isLocked = $lockRecord && ($lockRecord->is_locked ?? 0) == 1;

        if (!$isLocked && !$isForce) {
            $this->warn("⚠️ Periode penggajian {$periode} belum disetujui & dikunci. Pengiriman otomatis ditangguhkan.");
            return self::SUCCESS;
        }

        // Fetch active employees with emails via user relation
        $employees = Karyawan::with('user')
            ->whereNull('resign_at')
            ->whereHas('user', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->get();

        if ($employees->isEmpty()) {
            $this->info('ℹ️ Tidak ada karyawan aktif yang memiliki alamat email valid.');
            return self::SUCCESS;
        }

        $this->info("📧 Memproses pengiriman slip gaji periode {$periode} untuk {$employees->count()} karyawan...");

        if ($isDryRun) {
            $this->warn('⚠️ Mode DRY RUN aktif - tidak ada email yang dikirim.');
        }

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($employees as $karyawan) {
            $email = $karyawan->email ?: optional($karyawan->user)->email;
            if (!$email) {
                PayrollSendLog::updateOrCreate(
                    [
                        'periode' => $periode,
                        'karyawan_id' => $karyawan->id,
                    ],
                    [
                        'email' => '-',
                        'status' => 'failed',
                        'tipe_pengiriman' => $isForce ? 'instant_batch' : 'auto_scheduled',
                        'error_message' => 'Email karyawan belum terdaftar/kosong di sistem.',
                    ]
                );
                $failedCount++;
                continue;
            }

            // Check or create send log record
            $log = PayrollSendLog::where('periode', $periode)
                ->where('karyawan_id', $karyawan->id)
                ->first();

            if ($log && $log->status === 'sent' && !$isForce) {
                $skippedCount++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY-RUN] Akan memasukkan slip gaji ke antrean: {$karyawan->full_nama} ({$email})");
                $sentCount++;
                continue;
            }

            // Dispatch background queue job
            \App\Jobs\SendPayrollSlipJob::dispatch($karyawan->id, $periode, $isForce ? 'instant_batch' : 'auto_scheduled');
            $sentCount++;
            $this->line("  🚀 Dispatched Queue Job: {$karyawan->full_nama} ({$email})");
        }

        $this->info("✅ Berhasil memasukkan {$sentCount} slip gaji ke dalam antrean background queue (Gagal: {$failedCount}, Dilewati: {$skippedCount}).");

        // Save last run summary in sdm_payroll_settings
        $lastRunSummary = json_encode([
            'executed_at' => now()->toIso8601String(),
            'periode' => $periode,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'skipped' => $skippedCount,
            'status' => 'sukses',
        ]);

        DB::table('sdm_payroll_settings')->updateOrInsert(
            ['key' => 'auto_send_email_last_run'],
            ['value' => $lastRunSummary, 'updated_at' => now()]
        );

        $this->info("✨ Selesai. Terkirim: {$sentCount}, Gagal: {$failedCount}, Dilewati (Sudah Terkirim): {$skippedCount}");

        return self::SUCCESS;
    }
}
