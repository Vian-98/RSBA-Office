<?php

namespace App\Exports;

use App\Models\Sdm\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanKepegawaianExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        if ($this->query) {
            return $this->query->get();
        }

        return Karyawan::with(['latestJabatan.jabatan.bagian', 'ruangan'])->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'NIK',
            'Nama Lengkap',
            'Gelar Depan',
            'Gelar Belakang',
            'Jenis Kelamin',
            'Agama',
            'Status Kepegawaian',
            'Bagian / Departemen',
            'Jabatan Saat Ini',
            'Ruangan',
            'Masa Kerja',
            'Usia',
            'Tanggal Masuk',
            'No. HP',
            'Email / Username',
            'Alamat Domisili',
        ];
    }

    /**
     * @param Karyawan $karyawan
     */
    public function map($karyawan): array
    {
        $statusStr = is_object($karyawan->status) ? $karyawan->status->nama() : ucfirst($karyawan->status ?? '-');

        return [
            $karyawan->nip,
            $karyawan->nik,
            $karyawan->full_nama,
            $karyawan->gelar_depan ?? '',
            $karyawan->gelar_belakang ?? '',
            $karyawan->jk === 'L' ? 'Laki-laki' : 'Perempuan',
            ucfirst($karyawan->agama ?? '-'),
            $statusStr,
            $karyawan->latestJabatan?->jabatan?->bagian?->nama ?? '-',
            $karyawan->latestJabatan?->jabatan?->nama ?? '-',
            $karyawan->ruangan?->nama ?? '-',
            $karyawan->masakerja ?? '-',
            $karyawan->usia ?? '-',
            $karyawan->tgl_masuk ?? '-',
            $karyawan->hp ?? '-',
            $karyawan->user?->email ?? '-',
            $karyawan->dom_alamat ?? $karyawan->alamat ?? '-',
        ];
    }
}
