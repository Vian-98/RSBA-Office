<?php

namespace App\Services;

use GuzzleHttp\Client;
use Throwable;
use Illuminate\Support\Facades\Log;

class BpjsService
{
    /**
     * Get grouped ward capacities from BPJS API.
     */
    public function getWards()
    {
        $client = new Client();

        $kode_fk = config('bpjs.kode_faskes');
        $CID = config('bpjs.cons_id');
        $secretKey = config('bpjs.secret_key');

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $CID . "&" . $tStamp, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        $header = [
            'User-Agent' => 'testing/1.0',
            'Accept' => 'application/json',
            'X-cons-id' => $CID,
            'X-timestamp' => $tStamp,
            'X-signature' => $encodedSignature,
        ];

        try {
            $res = $client->request(
                'GET',
                'https://new-api.bpjs-kesehatan.go.id/aplicaresws/rest/bed/read/' . $kode_fk . '/1/20',
                [
                    'headers' => $header,
                    'timeout' => 10,
                ]
            );

            $body = $res->getBody();
            $responseBody = json_decode($body, true);

            $groupByKelas = collect($responseBody['response']['list'])->sortBy('kodekelas')->groupBy('kodekelas');

            $kapasitas = [];
            foreach ($groupByKelas as $kodekelas => $items) {
                $totalKapasitas = 0;
                $totalTersedia = 0;

                foreach ($items as $item) {
                    $totalKapasitas += $item['kapasitas'];
                    $totalTersedia += $item['tersedia'];
                }
                
                // Prevent division by zero
                $prosentase_terisi = $totalKapasitas > 0 ? round((($totalKapasitas - $totalTersedia) / $totalKapasitas) * 100) : 0;

                $color = 'green';
                if ($prosentase_terisi >= 85) {
                    $color = 'red';
                } else if ($prosentase_terisi >= 50) {
                    $color = 'orange';
                }

                $kapasitas[] = [
                    'kelas' => $items[0]['namakelas'],
                    'kodekelas' => $kodekelas,
                    'kapasitas' => $totalKapasitas,
                    'tersedia' => $totalTersedia,
                    'terisi' => $totalKapasitas - $totalTersedia,
                    'prosentase' => $prosentase_terisi,
                    'prosentase_color' => $color
                ];
            }

            return $kapasitas;
        } catch (Throwable $e) {
            Log::error('BPJS Service getWards error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get operating rooms schedules.
     */
    public function getOperatingRooms()
    {
        // For Tahap 1, return basic mock data similar to the old Fake adapter 
        // since office doesn't have an existing implementation for OR yet.
        return [
            [
                'room_code' => 'OR-1',
                'room_name' => 'Kamar Operasi 1',
                'schedules' => [
                    [
                        'id' => 'SCH-001',
                        'patient_name' => 'Budi Santoso',
                        'start_time' => now()->addHours(1)->toIso8601String(),
                        'status' => 'menunggu'
                    ]
                ]
            ],
            [
                'room_code' => 'OR-2',
                'room_name' => 'Kamar Operasi 2',
                'schedules' => []
            ]
        ];
    }
}
