<?php

namespace App\Exports;

use App\Models\Sdm\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Karyawan::with(['latestJabatan.jabatan'])->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'NIK',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Agama',
            'Status Kerja',
            'Jabatan',
            'Masa Kerja',
            'Status Dinas',
            'Tanggal Masuk',
            'HP',
        ];
    }

    /**
    * @param Karyawan $karyawan
    */
    public function map($karyawan): array
    {
        $statusDinas = 'Aktif';
        if ($karyawan->resign) {
            $statusDinas = match ($karyawan->resign) {
                '1' => 'Resign / Mengundurkan Diri',
                '2' => 'Diberhentikan',
                '4' => 'Habis Kontrak',
                default => 'Resign'
            };
        }

        return [
            $karyawan->nip,
            $karyawan->nik,
            $karyawan->full_nama,
            $karyawan->jk === 'L' ? 'Laki-laki' : 'Perempuan',
            ucfirst($karyawan->agama),
            $karyawan->status?->nama() ?? '-',
            $karyawan->latestJabatan?->jabatan?->nama ?? '-',
            $karyawan->masakerja,
            $statusDinas,
            $karyawan->tgl_masuk,
            $karyawan->hp,
        ];
    }
}
