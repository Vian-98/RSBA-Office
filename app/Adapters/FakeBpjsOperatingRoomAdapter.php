<?php

namespace App\Adapters;

use App\Contracts\BpjsOperatingRoomAdapterInterface;

class FakeBpjsOperatingRoomAdapter implements BpjsOperatingRoomAdapterInterface
{
    public function fetchOperatingRoomSchedules(): array
    {
        $rooms = [
            ['bpjs_or_code' => 'OK-01', 'name' => 'Kamar Operasi Utama 01'],
            ['bpjs_or_code' => 'OK-02', 'name' => 'Kamar Operasi Kebidanan 02'],
            ['bpjs_or_code' => 'OK-03', 'name' => 'Kamar Operasi Emergency 03'],
        ];

        $schedules = [];
        $names = ['Ahmad Basuki', 'Siti Rahma', 'Budi Santoso', 'Dewi Lestari', 'Joko Widodo', 'Megawati', 'Prabowo Subianto', 'Anies Baswedan'];

        for ($i = 1; $i <= 8; $i++) {
            $room = $rooms[array_rand($rooms)];
            $scheduledStart = now()->addHours($i - 3);
            
            $status = 'menunggu';
            $actualStart = null;
            if ($i <= 3) {
                $status = 'selesai';
                $actualStart = $scheduledStart->copy()->addMinutes(rand(-5, 10));
            } elseif ($i === 4) {
                $status = 'sedang_dilaksanakan';
                $actualStart = $scheduledStart->copy()->addMinutes(rand(-5, 5));
            }

            $schedules[] = [
                'bpjs_schedule_id' => 'SCH-BPJS-' . (1000 + $i),
                'bpjs_or_code' => $room['bpjs_or_code'],
                'or_name' => $room['name'],
                'patient_name' => $names[$i - 1],
                'scheduled_start_at' => $scheduledStart->toIso8601String(),
                'actual_start_at' => $actualStart ? $actualStart->toIso8601String() : null,
                'status' => $status,
            ];
        }

        return $schedules;
    }
}
