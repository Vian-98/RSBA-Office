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

        $this->command->info("Sedang membaca file Excel...");
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $employees = [];
        // Extract unique employees
        foreach ($rows as $index => $row) {
            if ($index < 6) continue; // Skip header
            if (empty(array_filter($row))) continue; // Skip empty rows
            
            $employeeId = trim((string)($row[0] ?? ''));
            $name = trim((string)($row[1] ?? ''));
            
            if (!empty($employeeId) && !isset($employees[$employeeId])) {
                $employees[$employeeId] = $name;
            }
        }

        $this->command->info("Ditemukan " . count($employees) . " karyawan unik. Memulai proses insert...");

        $insertedCount = 0;
        $skippedCount = 0;

        foreach ($employees as $nip => $nama) {
            // Check if already exists
            if (Karyawan::where('nip', $nip)->exists()) {
                $skippedCount++;
                continue;
            }

            // Create Karyawan
            Karyawan::create([
                'nip' => $nip,
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
            ]);
            $insertedCount++;
        }

        $this->command->info("Selesai! {$insertedCount} data baru berhasil ditambahkan, {$skippedCount} data dilewati (sudah ada).");
    }
}
