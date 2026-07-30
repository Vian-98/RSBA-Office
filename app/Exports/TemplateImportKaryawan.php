<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateImportKaryawan implements FromArray, WithHeadings
{
    protected $template;

    function __construct()
    {
        $this->template = 'karyawan';
    }

    function array(): array
    {
        $data = [
            [
                'NIP Karyawan',
                'NIK (16 digit)',
                'Nama Lengkap (tanpa gelar)',
                'Tgl Lahir (YYYY-MM-DD)',
                'HP',
                'Agama(islam,kristen,hindu,budha,khonghucu)',
                'Status Karyawan (kontrak,tetap,mitra,bantuan,magang)',
                'Tanggal Masuk (YYYY-MM-DD)',
            ]
        ];

        return $data;
    }

    public function headings(): array
    {
        return [
            'nip',
            'nik',
            'nama',
            'tgl_lahir',
            'hp',
            'agama',
            'status',
            'tgl_masuk',
        ];
    }
}
