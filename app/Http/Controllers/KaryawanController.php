<?php

namespace App\Http\Controllers;

use App\Models\Sdm\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class KaryawanController extends Controller
{
    // get all
    public function list(Request $request)
    {
        $input = $request->input('search');
        $karyawan = Karyawan::select('id', 'nama')
            ->when($input, function ($query, $input) {
                $query->where('nama', 'like', "%$input%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        return response()->json($karyawan);
    }


    // get all with jabatan — filter by jabatan_id = $atasan (parent_id of karyawan's jabatan)
    public function listWithJabatan(Request $request, $atasan = null)
    {
        $input = $request->input('search');

        $karyawan = Karyawan::with('jabatan')
            ->select('id', 'nama')
            ->when($input, function ($query, $input) {
                $query->where('nama', 'like', "%$input%");
            })
            ->when(
                $atasan,
                // Filter: karyawan whose current jabatan id == $atasan (the parent_id)
                fn($query) => $query->whereHas('jabatan', fn($q) => $q->where('sdm_jabatan.id', $atasan))
            )
            ->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(
                fn($data) => [
                    'id' => $data->id,
                    'nama' => $data->nama,
                    'description' => $data->jabatan?->first()?->nama ?? 'Belum ada jabatan'
                ]
            )->toArray();

        return response()->json($karyawan);
    }

    // Get list atasan approver terstruktur & ter-filter hierarki untuk pengajuan surat
    public function atasanApprover(Request $request, $jabatanId = null)
    {
        $search = $request->input('search');

        // Cari pemohon dari parameter request, user logged-in, atau jabatanId
        $karyawanId = $request->input('karyawan_id');
        $pemohonKaryawan = $karyawanId ? Karyawan::find($karyawanId) : auth()->user()?->karyawan;

        $jabatanPemohon = $jabatanId 
            ? \App\Models\Sdm\Jabatan::with(['tingkat', 'bagian'])->find($jabatanId) 
            : $pemohonKaryawan?->jabatan()?->first();

        $pemohonTingkat = (int) ($jabatanPemohon?->tingkat?->urutan ?? 5);
        $pemohonBagian  = $pemohonKaryawan?->active_bagian_id ?? $jabatanPemohon?->bagian_id;
        $pemohonRuangan = $pemohonKaryawan?->ruangan_id;

        // Batas tingkat yang boleh tampil (lebih senior / sama untuk tingkat <= 3)
        // Tingkat 5 → 4,3,2,1 | Tingkat 4 → 3,2,1 | Tingkat 3 → 3,2,1 | Tingkat 2 → 2,1 | Tingkat 1 → 1
        $maxTingkat = $pemohonTingkat < 3 ? $pemohonTingkat : ($pemohonTingkat === 3 ? 3 : $pemohonTingkat - 1);

        $query = Karyawan::with(['jabatan.tingkat', 'jabatan.bagian', 'ruangans'])
            ->select('sdm_karyawan.id', 'sdm_karyawan.nama', 'sdm_karyawan.gelar_depan', 'sdm_karyawan.gelar_belakang', 'sdm_karyawan.ruangan_id')
            ->whereHas('user') // hanya karyawan yang memiliki akun User (dapat diautentikasi untuk ACC)
            ->whereHas('jabatan', function ($q) use ($maxTingkat) {
                $q->whereNull('sdm_kary_jabatan.tgl_berakhir')
                  ->whereHas('tingkat', fn($tq) => $tq->where('urutan', '<=', $maxTingkat));
            })
            ->when($search, fn($q) => $q->where('sdm_karyawan.nama', 'like', "%{$search}%"))
            ->orderBy('sdm_karyawan.nama');

        $all = $query->get()->map(function ($k) use ($pemohonBagian, $pemohonRuangan) {
            $jab = $k->jabatan->first();
            $bagianId = $k->active_bagian_id;
            $tingkat  = (int) ($jab?->tingkat?->urutan ?? 99);
            $namaBagian = $jab?->bagian?->nama;

            // Pengecekan bagian sama: berdasarkan active_bagian_id ATAU ruangan_id
            $isSameBagian = ($pemohonBagian && $bagianId == $pemohonBagian)
                || ($pemohonRuangan && $k->ruangan_id == $pemohonRuangan);

            return [
                'id'          => $k->id,
                'label'       => $k->full_nama,
                'description' => ($jab?->nama ?? '-') . ($namaBagian ? ' · ' . $namaBagian : ''),
                'tingkat'     => $tingkat,
                'same_bagian' => $isSameBagian,
            ];
        })->sortBy('tingkat')->values();

        $sameBagian = $all->where('same_bagian', true)->values();
        $lainnya    = $all->where('same_bagian', false)->values();

        // Format grouped data untuk TallStackUI select.styled
        // TallStackUI styled.blade.php merender children via `option.value` (hardcoded)
        // sehingga key 'value' WAJIB digunakan untuk nested items
        $result = [];
        if ($sameBagian->isNotEmpty()) {
            $result[] = [
                'label' => '★ Bagian Sama',
                'value' => $sameBagian->toArray(),
            ];
        }
        if ($lainnya->isNotEmpty()) {
            $result[] = [
                'label' => 'Lainnya',
                'value' => $lainnya->toArray(),
            ];
        }

        return response()->json($result);
    }

    // Cari Karyawan Untuk Registrasi
    public function register(Request $request)
    {
        // Parameter: search
        $inputSearch = $request->input('search');

        // get data
        $karyawans = Karyawan::select('id', 'nama')
            ->when($inputSearch, function ($query, $inputSearch) {
                $query->where('nama', 'like', "%$inputSearch%");
            })
            ->whereNotExists(function (Builder $query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.karyawan_id', 'sdm_karyawan.id');
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        // return data
        return response()->json($karyawans);
    }


    // To Register Dokter
    public function registerDokter(Request $request)
    {
        $input = $request->input('search');

        $data = Karyawan::select('id', 'nama')
            ->when($input, function ($query, $input) {
                $query->where('nama', 'like', "%$input%");
            })
            ->whereNotExists(function (Builder $query) {
                $query->select(DB::raw(1))
                    ->from('dokter')
                    ->whereColumn('dokter.karyawan_id', 'sdm_karyawan.id');
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    /**
     * Approver picker untuk Kuitansi:
     * Menampilkan daftar pegawai yang diprioritaskan: Atasan & Keuangan di atas, sisanya di bawah.
     */
    public function kuitansiApprover(Request $request)
    {
        $search = $request->input('search');

        $query = Karyawan::with(['jabatan.tingkat', 'jabatan.bagian', 'user.roles'])
            ->select('sdm_karyawan.id', 'sdm_karyawan.nama', 'sdm_karyawan.gelar_depan', 'sdm_karyawan.gelar_belakang')
            ->whereHas('user')
            ->when($search, fn($q) => $q->where('sdm_karyawan.nama', 'like', "%{$search}%"))
            ->orderBy('sdm_karyawan.nama');

        $all = $query->get()->map(function ($k) {
            $jab = $k->jabatan->first();
            $tingkat = (int) ($jab?->tingkat?->urutan ?? 99);
            $namaBagian = $jab?->bagian?->nama ?? '';
            $groupBagian = strtolower($jab?->bagian?->group ?? '');
            $userRoles = $k->user?->roles->pluck('name')->map('strtolower')->toArray() ?? [];

            // Prioritas: Atasan / Pejabat (tingkat <= 3), Bagian Keuangan, atau Role Keuangan / Wadir-Keuangan
            $isKeuanganOrAtasan = $tingkat <= 3
                || str_contains(strtolower($namaBagian), 'keuangan')
                || str_contains(strtolower($jab?->nama ?? ''), 'keuangan')
                || in_array('keuangan', $userRoles)
                || in_array('wadir-keuangan', $userRoles)
                || in_array('super-admin', $userRoles);

            return [
                'id'                 => $k->id,
                'label'              => $k->full_nama,
                'description'        => ($jab?->nama ?? '-') . ($namaBagian ? ' · ' . $namaBagian : ''),
                'tingkat'            => $tingkat,
                'is_keu_or_atasan'   => $isKeuanganOrAtasan,
            ];
        })->sortBy('tingkat')->values();

        $keuDanAtasan = $all->where('is_keu_or_atasan', true)->values();
        $lainnya      = $all->where('is_keu_or_atasan', false)->values();

        $result = [];
        if ($keuDanAtasan->isNotEmpty()) {
            $result[] = [
                'label' => '★ Atasan & Keuangan',
                'value' => $keuDanAtasan->toArray(),
            ];
        }
        if ($lainnya->isNotEmpty()) {
            $result[] = [
                'label' => 'Lainnya',
                'value' => $lainnya->toArray(),
            ];
        }

        return response()->json($result);
    }

    /**
     * List Verifikator Keuangan untuk form SP3:
     * Karyawan dengan role Keuangan/Wadir-Keuangan atau bagian Keuangan.
     */
    public function verifikatorKeuangan(Request $request)
    {
        $search = $request->input('search');

        $query = Karyawan::with(['jabatan.bagian', 'user.roles'])
            ->select('sdm_karyawan.id', 'sdm_karyawan.nama', 'sdm_karyawan.gelar_depan', 'sdm_karyawan.gelar_belakang')
            ->whereHas('user', function ($uq) {
                $uq->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['Keuangan', 'Wadir-Keuangan', 'Super-Admin']);
                });
            })
            ->orWhereHas('jabatan', function ($jq) {
                $jq->whereHas('bagian', fn($bq) => $bq->where('nama', 'like', '%keuangan%')->orWhere('group', 'manajemen'))
                   ->orWhere('sdm_jabatan.nama', 'like', '%keuangan%');
            })
            ->when($search, fn($q) => $q->where('sdm_karyawan.nama', 'like', "%{$search}%"))
            ->orderBy('sdm_karyawan.nama');

        $list = $query->get()->unique('id')->map(function ($k) {
            $jab = $k->jabatan->first();
            $namaBagian = $jab?->bagian?->nama ?? '';
            return [
                'id'          => $k->id,
                'label'       => $k->full_nama,
                'description' => ($jab?->nama ?? 'Staf Keuangan') . ($namaBagian ? ' · ' . $namaBagian : ''),
            ];
        })->values();

        return response()->json($list);
    }
}

