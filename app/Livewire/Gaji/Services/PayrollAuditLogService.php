<?php

namespace App\Livewire\Gaji\Services;

use Illuminate\Support\Facades\DB;

class PayrollAuditLogService
{
    public function logEdit(int $slipId, int $karyawanId, string $periode, array $changedFields, ?int $userId = null): void
    {
        if (empty($changedFields)) {
            return;
        }

        DB::table('sdm_payroll_edit_logs')->insert([
            'payroll_slip_id' => $slipId,
            'karyawan_id' => $karyawanId,
            'periode' => $periode,
            'diubah_oleh' => $userId ?: auth()->id(),
            'perubahan' => json_encode($changedFields),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getSlipLogs(int $slipId): array
    {
        return DB::table('sdm_payroll_edit_logs')
            ->join('users', 'sdm_payroll_edit_logs.diubah_oleh', '=', 'users.id')
            ->leftJoin('sdm_karyawan', 'users.karyawan_id', '=', 'sdm_karyawan.id')
            ->where('sdm_payroll_edit_logs.payroll_slip_id', $slipId)
            ->select('sdm_payroll_edit_logs.*', DB::raw("COALESCE(sdm_karyawan.nama, users.email, 'Admin') as editor_name"))
            ->orderBy('sdm_payroll_edit_logs.created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $log->changed_fields = is_string($log->perubahan) ? json_decode($log->perubahan, true) : (array) $log->perubahan;
                return $log;
            })
            ->toArray();
    }

    public function getPeriodEditLogs(string $periode, string $search = ''): array
    {
        $query = DB::table('sdm_payroll_edit_logs')
            ->join('users', 'sdm_payroll_edit_logs.diubah_oleh', '=', 'users.id')
            ->leftJoin('sdm_karyawan as editor', 'users.karyawan_id', '=', 'editor.id')
            ->join('sdm_karyawan as target', 'sdm_payroll_edit_logs.karyawan_id', '=', 'target.id')
            ->leftJoin('sdm_kary_jabatan as target_kj', function ($join) {
                $join->on('target.id', '=', 'target_kj.karyawan_id')
                    ->whereRaw('target_kj.id = (select id from sdm_kary_jabatan where karyawan_id = target.id order by created_at desc limit 1)');
            })
            ->leftJoin('sdm_jabatan as target_j', 'target_kj.jabatan_id', '=', 'target_j.id')
            ->leftJoin('bagian as target_b', 'target_j.bagian_id', '=', 'target_b.id')
            ->where('sdm_payroll_edit_logs.periode', $periode);

        if (!empty($search)) {
            $s = '%' . $search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('editor.nama', 'like', $s)
                  ->orWhere('users.email', 'like', $s)
                  ->orWhere('target.nama', 'like', $s)
                  ->orWhere('target.nip', 'like', $s)
                  ->orWhere('target_b.nama', 'like', $s)
                  ->orWhere('sdm_payroll_edit_logs.perubahan', 'like', $s);
            });
        }

        return $query->select(
                'sdm_payroll_edit_logs.*',
                DB::raw("COALESCE(editor.nama, users.email, 'Admin') as editor_name"),
                'target.nama as employee_name',
                'target.nip as employee_nip',
                'target_b.nama as employee_bagian'
            )
            ->orderBy('sdm_payroll_edit_logs.created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $log->changed_fields = is_string($log->perubahan) ? json_decode($log->perubahan, true) : (array) $log->perubahan;
                return (array) $log;
            })
            ->toArray();
    }
}
