<?php

namespace App\Exports;

use App\Models\Master\Barang;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;

class StokGudangExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return Barang::with(['kategori', 'satuan', 'stoks', 'stoks.penerimaanDet.penerimaan'])
            ->withSum('stoks', 'stok');
    }

    public function headings(): array
    {
        return [
            'Barang',
            'Kategori',
            'Stok Tersedia',
            'Satuan',
            'Terakhir Masuk',
        ];
    }

    public function map($barang): array
    {
        $terakhirMasuk = $barang->stoks->max('penerimaanDet.penerimaan.created_at')
            ? Carbon::parse($barang->stoks->max('penerimaanDet.penerimaan.created_at'))
                ->timezone('Asia/Jakarta')
                ->format('H:i d/m/Y')
            : 'Belum pernah dibeli';

        return [
            $barang->nama,
            $barang->kategori?->nama ?? '-',
            $barang->stoks_sum_stok ?? 0,
            $barang->satuan?->nama ?? '-',
            $terakhirMasuk,
        ];
    }
}
