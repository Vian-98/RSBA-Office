<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UmDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = [
            base_path('um_kategori.sql'),
            base_path('um_satuan.sql'),
            base_path('um_barang.sql'),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $sql = file_get_contents($file);
                DB::unprepared($sql);
                $this->command->info('Seeded from: ' . basename($file));
            } else {
                $this->command->error('File not found: ' . basename($file));
            }
        }
    }
}
