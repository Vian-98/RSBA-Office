<?php

namespace App\Jobs;

use App\Mail\SlipGajiMail;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\PayrollSendLog;
use App\Livewire\Gaji\Services\PayrollCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPayrollSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public int $karyawanId;
    public string $periode;
    public string $tipePengiriman;

    /**
     * Create a new job instance.
     */
    public function __construct(int $karyawanId, string $periode, string $tipePengiriman = 'instant_batch')
    {
        $this->karyawanId = $karyawanId;
        $this->periode = $periode;
        $this->tipePengiriman = $tipePengiriman;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $karyawan = Karyawan::with(['user', 'jabatan.bagian'])->find($this->karyawanId);

        if (!$karyawan) {
            return;
        }

        $email = $karyawan->email ?: optional($karyawan->user)->email;

        if (!$email) {
            PayrollSendLog::updateOrCreate(
                [
                    'periode' => $this->periode,
                    'karyawan_id' => $karyawan->id,
                ],
                [
                    'email' => '-',
                    'status' => 'failed',
                    'tipe_pengiriman' => $this->tipePengiriman,
                    'error_message' => 'Email karyawan belum terdaftar/kosong di sistem.',
                ]
            );
            return;
        }

        $log = PayrollSendLog::firstOrCreate(
            [
                'periode' => $this->periode,
                'karyawan_id' => $karyawan->id,
            ],
            [
                'email' => $email,
                'status' => 'pending',
                'tipe_pengiriman' => $this->tipePengiriman,
            ]
        );

        try {
            $slip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawan->id)
                ->where('periode', $this->periode)
                ->first();

            if ($slip) {
                $calc = [
                    'gaji_pokok' => $slip->gaji_pokok,
                    'tunjangan' => $slip->tunjangan_tetap + $slip->tunjangan_absensi + $slip->tunjangan_jabatan + $slip->tunjangan_shift + $slip->tunjangan_radiologi + $slip->tunjangan_lain + $slip->uang_lembur + $slip->tunjangan_hari_raya,
                    'bpjs_kes' => $slip->potongan_bpjs_kes,
                    'bpjs_ket' => $slip->potongan_bpjs_tk,
                    'pajak' => $slip->potongan_pph21 + $slip->potongan_bank,
                    'gaji_bersih' => $slip->gaji_bersih,
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                ];
            } else {
                $base = PayrollCalculator::calculate($karyawan);
                $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
                $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);

                $calc = [
                    'gaji_pokok' => $base['gaji_pokok'],
                    'tunjangan' => $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'],
                    'bpjs_kes' => $deductions['potongan_bpjs_kes'],
                    'bpjs_ket' => $deductions['potongan_bpjs_tk'],
                    'pajak' => $deductions['potongan_pph21'],
                    'gaji_bersih' => $totalPendapatan - ($deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk']) - $deductions['potongan_pph21'],
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                ];
            }

            $slipData = [
                'nama' => $karyawan->full_nama,
                'nip' => $karyawan->nip,
                'status' => is_object($karyawan->status) && method_exists($karyawan->status, 'nama') ? $karyawan->status->nama() : ($karyawan->status ?? '-'),
                'jabatan' => $calc['jabatan_nama'],
                'bagian' => $calc['bagian_nama'],
                'periode' => Carbon::parse($this->periode . '-01')->translatedFormat('F Y'),
                'gaji_pokok' => $calc['gaji_pokok'],
                'tunjangan' => $calc['tunjangan'],
                'bpjs_kes' => $calc['bpjs_kes'],
                'bpjs_ket' => $calc['bpjs_ket'],
                'pajak' => $calc['pajak'],
                'gaji_bersih' => $calc['gaji_bersih'],
            ];

            Mail::to($email)->send(new SlipGajiMail($slipData));

            $log->update([
                'email' => $email,
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'email' => $email,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
