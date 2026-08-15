<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServerRoomTelemetryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $readings;
    protected array $deviceInfo;

    public function __construct(array $readings, array $deviceInfo = [])
    {
        $this->readings   = $readings;
        $this->deviceInfo = $deviceInfo;
    }

    public function collection()
    {
        return collect($this->readings);
    }

    public function headings(): array
    {
        return [
            'ID Log',
            'Waktu Wita/WIB (Recorded At)',
            'Suhu (°C)',
            'Kelembapan (%)',
            'Kualitas Sinyal RSSI (dBm)',
            'Latency Probe (ms)',
            'Status Perangkat',
            'Serial ESP32',
            'Lokasi Perangkat',
        ];
    }

    public function map($row): array
    {
        $rssi = $row['rssi'] ?? 0;
        $rssiLabel = match (true) {
            $rssi >= -60 => "{$rssi} dBm (Sangat Baik)",
            $rssi >= -70 => "{$rssi} dBm (Baik)",
            $rssi >= -80 => "{$rssi} dBm (Sedang)",
            default      => "{$rssi} dBm (Lemah)",
        };

        return [
            $row['id'] ?? '-',
            isset($row['recorded_at']) ? date('d-m-Y H:i:s', strtotime($row['recorded_at'])) : '-',
            number_format((float) ($row['temperature'] ?? 0), 2) . ' °C',
            number_format((float) ($row['humidity'] ?? 0), 2) . ' %',
            $rssiLabel,
            ($row['latency_ms'] ?? '-') . ' ms',
            $this->deviceInfo['status'] ?? 'ONLINE',
            $this->deviceInfo['serial_number'] ?? 'ESP32-SERVER-ROOM-001',
            $this->deviceInfo['location'] ?? 'Ruang Server Utama RSBA',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'], // Dark Slate
                ],
            ],
        ];
    }
}
