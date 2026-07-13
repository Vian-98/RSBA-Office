<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriRuangan = [
            'Gawat Darurat' => [
                'IGD (Instalasi Gawat Darurat)',
                'UGD (Unit Gawat Darurat)',
                'Ruang PONEK',
                'Triase'
            ],
            'Perawatan Intensif' => [
                'ICU (Intensive Care Unit)',
                'ICCU (Intensive Cardiovascular Care Unit)',
                'NICU (Neonatal Intensive Care Unit)',
                'PICU (Pediatric Intensive Care Unit)',
                'HCU (High Care Unit)'
            ],
            'Ruang Tindakan & Operasi' => [
                'Kamar Operasi (OK)',
                'Ruang Bersalin (VK)',
                'Ruang Pemulihan (Recovery Room)',
                'Ruang Endoskopi',
                'Ruang Hemodialisa'
            ],
            'Rawat Jalan (Poliklinik)' => [
                'Poli Umum',
                'Poli Gigi',
                'Poli Anak',
                'Poli Penyakit Dalam',
                'Poli Kandungan (Obgyn)',
                'Poli Bedah',
                'Poli Saraf',
                'Poli Mata',
                'Poli THT',
                'Poli Jantung',
                'Poli Paru',
                'Poli Ortopedi',
                'Poli Kulit & Kelamin',
                'Poli Rehabilitasi Medik'
            ],
            'Rawat Inap' => [
                'Ruang Perawatan VIP',
                'Ruang Perawatan VVIP',
                'Ruang Perawatan Kelas 1',
                'Ruang Perawatan Kelas 2',
                'Ruang Perawatan Kelas 3',
                'Ruang Isolasi',
                'Ruang Perawatan Anak',
                'Ruang Perawatan Bedah',
                'Ruang Perawatan Penyakit Dalam'
            ],
            'Penunjang Medis' => [
                'Laboratorium',
                'Radiologi (X-Ray, CT Scan, MRI)',
                'Farmasi (Apotek Rawat Jalan)',
                'Farmasi (Apotek Rawat Inap)',
                'Instalasi Gizi / Dapur',
                'Rehabilitasi Medik / Fisioterapi',
                'Bank Darah',
                'Pemulasaraan Jenazah (Kamar Jenazah)'
            ],
            'Manajemen & Non-Medis' => [
                'Rekam Medis',
                'Pendaftaran & Informasi (Customer Service)',
                'Kasir',
                'Ruang Direksi',
                'Kantor Manajemen (SDM & Keuangan)',
                'Instalasi IT',
                'Instalasi Pemeliharaan Sarana (IPSRS)',
                'Gudang Logistik & Farmasi',
                'CSSD (Sterilisasi Sentral)',
                'Laundry / Binatu',
                'Sanitasi & Pengolahan Limbah'
            ]
        ];

        foreach ($kategoriRuangan as $kategori => $ruanganList) {
            foreach ($ruanganList as $namaRuangan) {
                // Gunakan firstOrCreate agar tidak duplikat jika di-run berulang
                Ruangan::firstOrCreate(
                    ['nama' => $namaRuangan],
                    ['is_active' => true]
                );
            }
        }
    }
}
