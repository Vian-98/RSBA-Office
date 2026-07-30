<?php

namespace App\Exports;

use App\Models\Gudang\OpnameStokDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OpnameHasil implements FromQuery, WithMapping, WithHeadings
{
    private int $opname_id;

    public function __construct($opname_id)
    {
        $this->opname_id = $opname_id;
    }

    public function query()
    {
        $data = OpnameStokDetail::query()
            ->with(['barang', 'barang.satuan', 'investigasi'])
            ->where('opname_id', $this->opname_id);
        return $data;
    }

    public function map($detail): array
    {
        return [
            $detail->stok_id ?? '-',
            $detail->barang->nama ?? '-',
            $detail->barang->satuan->nama ?? '-',
            $detail->harga_satuan ?? 0,
            $detail->stok_sistem_opname ?? 0,
            $detail->stok_fisik ?? 0,
            $detail->selisih ?? 0,
            $detail->investigasi->hasil_investigasi ?? '-',
            $detail->investigasi->catatan_investigasi ?? '-'
        ];
    }

    public function headings(): array
    {
        return [
            'Stok Id',
            'Nama Barang',
            'Satuan',
            'Harga Satuan (Rp)',
            'Stok Sistem',
            'Stok Fisik',
            'Selisih',
            'Hasil Investigasi',
            'Keterangan',
        ];
    }
}
