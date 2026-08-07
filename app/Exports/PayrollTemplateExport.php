<?php

namespace App\Exports;

use App\Models\Sdm\Karyawan;
use App\Livewire\Gaji\Services\PayrollCalculator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PayrollTemplateExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    protected string $periode;

    public function __construct(string $periode)
    {
        $this->periode = $periode;
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        // Bind NIP (A) and No. Rekening (G) explicitly as STRING to avoid scientific notation and single quotes
        if ($column === 'A' || $column === 'G') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection()
    {
        return Karyawan::with(['jabatan.bagian'])->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama Karyawan',
            'Bagian',
            'Jabatan',
            'Status Kerja',
            'Nama Bank',
            'No. Rekening',
            'Gaji Pokok',
            'Tunjangan Tetap',
            'Tunjangan Absensi',
            'Tunjangan Jabatan',
            'Tunjangan Shift',
            'Tunjangan Radiologi',
            'Tunjangan Lain',
            'Uang Lembur',
            'Tunjangan THR',
            'Potongan Absensi',
            'Potongan Cash Bon',
            'Potongan Obat',
            'Potongan Lain',
            'Potongan Bank',
            'BPJS Kesehatan',
            'BPJS Ketenagakerjaan',
            'PPh 21',
        ];
    }

    /**
     * @param Karyawan $karyawan
     */
    public function map($karyawan): array
    {
        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawan->id)
            ->where('periode', $this->periode)
            ->first();

        $bagian = $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum';
        $jabatan = $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff';

        if ($slip) {
            $gajiPokok = (double) $slip->gaji_pokok;
            $tunjanganTetap = (double) $slip->tunjangan_tetap;
            $tunjanganAbsensi = (double) $slip->tunjangan_absensi;
            $tunjanganJabatan = (double) $slip->tunjangan_jabatan;
            $tunjanganShift = (double) $slip->tunjangan_shift;
            $tunjanganRadiologi = (double) $slip->tunjangan_radiologi;
            $tunjanganLain = (double) $slip->tunjangan_lain;
            $uangLembur = (double) $slip->uang_lembur;
            $thr = (double) $slip->tunjangan_hari_raya;
            $potAbsensi = (double) $slip->potongan_absensi;
            $potCashBon = (double) $slip->potongan_cash_bon;
            $potObat = (double) $slip->potongan_obat;
            $potLain = (double) $slip->potongan_lain;
            $potBank = (double) $slip->potongan_bank;
            $bpjsKes = (double) $slip->potongan_bpjs_kes;
            $bpjsTk = (double) $slip->potongan_bpjs_tk;
            $pph21 = (double) $slip->potongan_pph21;
        } else {
            $base = PayrollCalculator::calculate($karyawan);
            $totalPendapatan = (double) ($base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan']);
            $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);

            $gajiPokok = (double) $base['gaji_pokok'];
            $tunjanganTetap = (double) $base['tunjangan_tetap'];
            $tunjanganAbsensi = (double) $base['tunjangan_absensi'];
            $tunjanganJabatan = (double) $base['tunjangan_jabatan'];
            $tunjanganShift = 0.0;
            $tunjanganRadiologi = 0.0;
            $tunjanganLain = 0.0;
            $uangLembur = 0.0;
            $thr = 0.0;
            $potAbsensi = 0.0;
            $potCashBon = 0.0;
            $potObat = 0.0;
            $potLain = 0.0;
            $potBank = 0.0;
            $bpjsKes = (double) $deductions['potongan_bpjs_kes'];
            $bpjsTk = (double) $deductions['potongan_bpjs_tk'];
            $pph21 = (double) $deductions['potongan_pph21'];
        }

        return [
            (string) $karyawan->nip,
            $karyawan->full_nama,
            $bagian,
            $jabatan,
            $karyawan->status?->nama() ?? '-',
            $karyawan->nama_bank ?? '',
            (string) ($karyawan->no_rekening ?? ''),
            $gajiPokok,
            $tunjanganTetap,
            $tunjanganAbsensi,
            $tunjanganJabatan,
            $tunjanganShift,
            $tunjanganRadiologi,
            $tunjanganLain,
            $uangLembur,
            $thr,
            $potAbsensi,
            $potCashBon,
            $potObat,
            $potLain,
            $potBank,
            $bpjsKes,
            $bpjsTk,
            $pph21,
        ];
    }

    public function columnFormats(): array
    {
        $rupiahFormat = '"Rp "#,##0';

        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => $rupiahFormat,
            'I' => $rupiahFormat,
            'J' => $rupiahFormat,
            'K' => $rupiahFormat,
            'L' => $rupiahFormat,
            'M' => $rupiahFormat,
            'N' => $rupiahFormat,
            'O' => $rupiahFormat,
            'P' => $rupiahFormat,
            'Q' => $rupiahFormat,
            'R' => $rupiahFormat,
            'S' => $rupiahFormat,
            'T' => $rupiahFormat,
            'U' => $rupiahFormat,
            'V' => $rupiahFormat,
            'W' => $rupiahFormat,
            'X' => $rupiahFormat,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Enable Excel Dropdown AutoFilter on Header Row
        $sheet->setAutoFilter("A1:X{$highestRow}");

        // Freeze top header row so it stays visible when scrolling
        $sheet->freezePane('A2');

        // Style Header Row (Row 1)
        $sheet->getStyle('A1:X1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo header background
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Grid borders and alignment for all data rows
        if ($highestRow > 1) {
            $sheet->getStyle("A2:X{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Left align text columns A-G
            $sheet->getStyle("A2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Right align money columns H-X
            $sheet->getStyle("H2:X{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }
}
