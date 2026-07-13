<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sdm\Karyawan;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class KaryawanExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputFileName = 'E:/rsba/Absen 08 s.d 09 Jun.xlsx';

        if (!file_exists($inputFileName)) {
            $this->command->error("File Excel tidak ditemukan: {$inputFileName}");
            return;
        }

        // Ambil semua ruangan
        $ruangans = \App\Models\Ruangan::all();
        if ($ruangans->isEmpty()) {
            $this->command->warn("Belum ada data ruangan di database. Harap jalankan RuanganDummySeeder terlebih dahulu.");
        }

        $this->command->info("Membuat Shift unik per Ruangan...");

        foreach ($ruangans as $ruangan) {
            $nama = strtolower($ruangan->nama);
            
            // Ekstrak nama pendek untuk label shift (buang info dalam kurung agar tidak kepanjangan)
            $namaPendek = trim(preg_replace('/\s*\([^)]*\)/', '', $ruangan->nama));
            
            $shiftsToCreate = [];

            if (str_contains($nama, 'igd') || str_contains($nama, 'ugd') || str_contains($nama, 'ponek') || str_contains($nama, 'icu') || str_contains($nama, 'rawat inap') || str_contains($nama, 'kamar operasi') || str_contains($nama, 'bersalin')) {
                // Layanan 24 Jam
                $shiftsToCreate = [
                    ['kode' => 'P0730A_R' . $ruangan->id, 'nama' => 'Pagi ' . $namaPendek, 'jam_masuk' => '07:30:00', 'jam_keluar' => '14:00:00', 'lintas_hari' => false],
                    ['kode' => 'S14_R' . $ruangan->id, 'nama' => 'Siang ' . $namaPendek, 'jam_masuk' => '14:00:00', 'jam_keluar' => '21:00:00', 'lintas_hari' => false],
                    ['kode' => 'M21_R' . $ruangan->id, 'nama' => 'Malam ' . $namaPendek, 'jam_masuk' => '21:00:00', 'jam_keluar' => '07:30:00', 'lintas_hari' => true],
                ];
            } elseif (str_contains($nama, 'poli') || str_contains($nama, 'rehabilitasi')) {
                // Poliklinik
                $shiftsToCreate = [
                    ['kode' => 'P0730B_R' . $ruangan->id, 'nama' => 'Pagi ' . $namaPendek, 'jam_masuk' => '07:30:00', 'jam_keluar' => '16:30:00', 'lintas_hari' => false],
                    ['kode' => 'S14_R' . $ruangan->id, 'nama' => 'Siang ' . $namaPendek, 'jam_masuk' => '14:00:00', 'jam_keluar' => '21:00:00', 'lintas_hari' => false], // Poli sore
                ];
            } elseif (str_contains($nama, 'manajemen') || str_contains($nama, 'sdm') || str_contains($nama, 'keuangan') || str_contains($nama, 'direksi') || str_contains($nama, 'rekam medis') || str_contains($nama, 'it')) {
                // Kantor / Manajemen
                $shiftsToCreate = [
                    ['kode' => 'P08_R' . $ruangan->id, 'nama' => 'Pagi ' . $namaPendek . ' (08.00-16.00)', 'jam_masuk' => '08:00:00', 'jam_keluar' => '16:00:00', 'lintas_hari' => false],
                    ['kode' => 'P08B_R' . $ruangan->id, 'nama' => 'Pagi ' . $namaPendek . ' (08.00-17.00)', 'jam_masuk' => '08:00:00', 'jam_keluar' => '17:00:00', 'lintas_hari' => false],
                ];
            } else {
                // Support (Gizi, Laundry, CSSD, Radiologi, Laboratorium)
                $shiftsToCreate = [
                    ['kode' => 'P06_R' . $ruangan->id, 'nama' => 'Pagi Awal ' . $namaPendek, 'jam_masuk' => '06:00:00', 'jam_keluar' => '14:00:00', 'lintas_hari' => false],
                    ['kode' => 'P07_R' . $ruangan->id, 'nama' => 'Pagi ' . $namaPendek, 'jam_masuk' => '07:00:00', 'jam_keluar' => '14:00:00', 'lintas_hari' => false],
                    ['kode' => 'S12_R' . $ruangan->id, 'nama' => 'Siang ' . $namaPendek, 'jam_masuk' => '12:00:00', 'jam_keluar' => '19:00:00', 'lintas_hari' => false],
                ];
            }

            foreach ($shiftsToCreate as $sd) {
                // Batasi nama shift maksimal 30 karakter sesuai migration
                $namaShift = substr($sd['nama'], 0, 30);
                
                $shift = \App\Models\Sdm\JadwalShift::firstOrCreate(
                    ['kode' => $sd['kode']],
                    array_merge($sd, ['nama' => $namaShift, 'aktif' => true])
                );

                \App\Models\Sdm\RuanganShift::firstOrCreate([
                    'ruangan_id' => $ruangan->id,
                    'shift_id' => $shift->id
                ]);
            }
        }

        $ruangan24Jam = [];
        $ruanganPoli = [];
        $ruanganOffice = [];
        $ruanganSupport = [];

        foreach ($ruangans as $ruangan) {
            $nama = strtolower($ruangan->nama);
            if (str_contains($nama, 'igd') || str_contains($nama, 'ugd') || str_contains($nama, 'ponek') || str_contains($nama, 'icu') || str_contains($nama, 'rawat inap') || str_contains($nama, 'kamar operasi') || str_contains($nama, 'bersalin')) {
                $ruangan24Jam[] = $ruangan->id;
            } elseif (str_contains($nama, 'poli') || str_contains($nama, 'rehabilitasi')) {
                $ruanganPoli[] = $ruangan->id;
            } elseif (str_contains($nama, 'manajemen') || str_contains($nama, 'sdm') || str_contains($nama, 'keuangan') || str_contains($nama, 'direksi') || str_contains($nama, 'rekam medis') || str_contains($nama, 'it')) {
                $ruanganOffice[] = $ruangan->id;
            } else {
                $ruanganSupport[] = $ruangan->id;
            }
        }

        $this->command->info("Sedang membaca file Excel...");
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $employeesData = [];
        $employeeShifts = [];

        // Extract unique employees and their shift patterns
        foreach ($rows as $index => $row) {
            if ($index < 6) continue; // Skip header
            if (empty(array_filter($row))) continue; // Skip empty rows
            
            $employeeId = trim((string)($row[0] ?? ''));
            $name = trim((string)($row[1] ?? ''));
            $checkIn = trim((string)($row[3] ?? ''));
            
            if (!empty($employeeId)) {
                if (!isset($employeesData[$employeeId])) {
                    $employeesData[$employeeId] = $name;
                    $employeeShifts[$employeeId] = [];
                }
                
                if (!empty($checkIn)) {
                    if (!in_array($checkIn, $employeeShifts[$employeeId])) {
                        $employeeShifts[$employeeId][] = $checkIn;
                    }
                }
            }
        }

        $this->command->info("Ditemukan " . count($employeesData) . " karyawan unik. Memulai proses insert...");

        $insertedCount = 0;
        $skippedCount = 0;

        foreach ($employeesData as $pinAbsen => $nama) {
            // Cek apakah karyawan sudah ada berdasarkan pin_absen atau nama
            $existing = Karyawan::where('pin_absen', $pinAbsen)
                ->orWhere('nama', $nama)
                ->first();

            // Tentukan kategori pegawai dari jam absen
            $times = $employeeShifts[$pinAbsen];
            $kat = 'support';
            
            if (in_array('21:00', $times) || in_array('14:00', $times) || in_array('07:00', $times)) {
                $kat = '24jam';
            } elseif (in_array('08:00', $times)) {
                $kat = 'office';
            } elseif (in_array('07:30', $times)) {
                $kat = 'poli';
            }

            // Assign Ruangan
            $ruanganId = null;
            if ($kat === '24jam' && !empty($ruangan24Jam)) {
                $ruanganId = $ruangan24Jam[array_rand($ruangan24Jam)];
            } elseif ($kat === 'poli' && !empty($ruanganPoli)) {
                $ruanganId = $ruanganPoli[array_rand($ruanganPoli)];
            } elseif ($kat === 'office' && !empty($ruanganOffice)) {
                $ruanganId = $ruanganOffice[array_rand($ruanganOffice)];
            } elseif (!empty($ruanganSupport)) {
                $ruanganId = $ruanganSupport[array_rand($ruanganSupport)];
            } else {
                $ruanganIds = \App\Models\Ruangan::pluck('id')->toArray();
                $ruanganId = !empty($ruanganIds) ? $ruanganIds[array_rand($ruanganIds)] : null;
            }

            // Pemetaan khusus Koordinator untuk kebutuhan testing
            $isCoordinator = false;
            $emailKoor = '';
            $pinAbsenStr = (string)$pinAbsen;
            
            if ($pinAbsenStr === '21220175') { // Afrizal -> UGD
                $ruanganTarget = \App\Models\Ruangan::where('nama', 'like', '%UGD%')->first();
                $ruanganId = $ruanganTarget ? $ruanganTarget->id : $ruanganId;
                $isCoordinator = true;
                $emailKoor = 'koor_ugd@rsba.com';
            } elseif ($pinAbsenStr === '22230383') { // Fika Erisandy -> IGD
                $ruanganTarget = \App\Models\Ruangan::where('nama', 'like', '%IGD%')->first();
                $ruanganId = $ruanganTarget ? $ruanganTarget->id : $ruanganId;
                $isCoordinator = true;
                $emailKoor = 'koor_igd@rsba.com';
            } elseif ($pinAbsenStr === '22200233') { // Rosiana -> Poli Anak
                $ruanganTarget = \App\Models\Ruangan::where('nama', 'like', '%Poli Anak%')->first();
                $ruanganId = $ruanganTarget ? $ruanganTarget->id : $ruanganId;
                $isCoordinator = true;
                $emailKoor = 'koor_poli_anak@rsba.com';
            } elseif ($pinAbsenStr === '21220077') { // Agustina -> Ruang Perawatan VIP
                $ruanganTarget = \App\Models\Ruangan::where('nama', 'like', '%VIP%')->first();
                $ruanganId = $ruanganTarget ? $ruanganTarget->id : $ruanganId;
                $isCoordinator = true;
                $emailKoor = 'koor_vip@rsba.com';
            }

            if ($existing) {
                // Update pin_absen dan ruangan_id jika sudah ada
                $existing->update([
                    'pin_absen' => $pinAbsen,
                    'ruangan_id' => $ruanganId,
                ]);
                $karyawanRecord = $existing;
                $skippedCount++;
            } else {
                // Create Karyawan jika belum ada sama sekali
                $karyawanRecord = Karyawan::create([
                    'nip' => 'NIP-' . $pinAbsen, // Guaranteed unique NIP
                    'pin_absen' => $pinAbsen, // Excel Employee ID
                    'nik' => mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999), // Dummy NIK numeric 16 digit
                    'nama' => Str::limit($nama, 50),
                    'jk' => 'L',
                    'tgl_lahir' => '1990-01-01',
                    'hp' => '080000000000',
                    'prov' => 'Lampung',
                    'kab' => 'Bandar Lampung',
                    'kec' => 'Kemiling',
                    'desa' => 'Beringin Raya',
                    'alamat' => 'Jl. Pramuka No. 27',
                    'agama' => 'islam',
                    'status' => 'kontrak',
                    'tgl_masuk' => '2026-01-01',
                    'ruangan_id' => $ruanganId,
                    'kategori_kerja' => 'shift', // Asumsikan kerja shift karena jadwalnya variatif
                ]);
                $insertedCount++;
            }

            // Hubungkan User login jika merupakan koordinator
            if ($isCoordinator && !empty($emailKoor)) {
                $user = \App\Models\User::updateOrCreate(
                    ['email' => $emailKoor],
                    [
                        'password' => \Illuminate\Support\Facades\Hash::make('1234'),
                        'karyawan_id' => $karyawanRecord->id,
                    ]
                );
                $user->syncRoles(['Koordinator']);
            }
        }

        $this->command->info("Selesai! {$insertedCount} data baru berhasil ditambahkan, {$skippedCount} data dilewati (sudah ada).");
    }
}
